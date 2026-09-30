<?php

namespace Pterodactyl\Console\Commands\Node;

use Pterodactyl\Models\Node;
use Pterodactyl\Models\Location;
use Illuminate\Console\Command;
use Pterodactyl\Services\Nodes\NodeCreationService;
use Pterodactyl\Services\Allocations\AssignmentService;

/**
 * Used by install.sh when the panel and Wings go on the same machine: creates (or reuses) a
 * location and a node for this machine plus a range of game ports, and prints the node ID.
 */
class QuickSetupNodeCommand extends Command
{
    protected $description = 'Creates a location, a node and game port allocations in one step (used by the installer).';

    protected $signature = 'p:node:quick-setup
                            {--fqdn= : Domain (or IP when not using SSL) Wings is reached at.}
                            {--scheme=http : https when Wings has a certificate, otherwise http.}
                            {--name=Node 1 : Name of the node.}
                            {--memory=4096 : Memory in MiB servers on this node may use.}
                            {--disk=20480 : Disk space in MiB servers on this node may use.}
                            {--ip= : IP address game servers listen on.}
                            {--ports=25565-25575 : Game port range to create.}';

    public function __construct(private NodeCreationService $nodes, private AssignmentService $allocations)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $fqdn = strtolower(trim((string) $this->option('fqdn')));
        $scheme = $this->option('scheme') === 'https' ? 'https' : 'http';
        if ($fqdn === '') {
            $this->error('--fqdn is required.');

            return self::FAILURE;
        }

        $node = Node::query()->where('fqdn', $fqdn)->first();
        if ($node && $node->scheme !== $scheme) {
            // Running the installer again (e.g. after switching the panel to HTTPS) repairs the node.
            $node->forceFill(['scheme' => $scheme])->save();
        }
        if (!$node) {
            $location = Location::query()->first() ?? Location::query()->create([
                'short' => 'main',
                'long' => 'Main location',
            ]);

            $node = $this->nodes->handle([
                'name' => $this->option('name'),
                'description' => 'Created by the installer',
                'location_id' => $location->id,
                'fqdn' => $fqdn,
                'scheme' => $scheme,
                'public' => true,
                'behind_proxy' => false,
                'maintenance_mode' => false,
                'memory' => max(1024, (int) $this->option('memory')),
                'memory_overallocate' => 0,
                'disk' => max(1024, (int) $this->option('disk')),
                'disk_overallocate' => 0,
                'upload_size' => 100,
                'daemonListen' => 8080,
                'daemonSFTP' => 2022,
                'daemonBase' => '/var/lib/pterodactyl/volumes',
            ]);
        }

        if ($ip = $this->option('ip')) {
            $ports = (string) $this->option('ports');
            try {
                $this->allocations->handle($node, ['allocation_ip' => $ip, 'allocation_ports' => [$ports]]);
            } catch (\Throwable $exception) {
                // Already existing ports are fine on a re-run.
                $this->warn('Allocations: ' . $exception->getMessage());
            }
        }

        $this->line((string) $node->id);

        return self::SUCCESS;
    }
}
