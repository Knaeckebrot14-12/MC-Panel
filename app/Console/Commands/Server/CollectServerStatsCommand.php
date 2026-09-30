<?php

namespace Pterodactyl\Console\Commands\Server;

use Pterodactyl\Models\Node;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\EggVariable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Services\Minecraft\ServerListPing;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;

class CollectServerStatsCommand extends Command
{
    protected $description = 'Stores CPU, memory and player counts of every server for the history graphs.';

    protected $signature = 'p:stats:collect';

    /** How long history is kept. */
    private const KEEP_DAYS = 8;

    public function __construct(private DaemonServerRepository $repository, private ServerListPing $ping)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = now();
        $rows = [];
        // Eggs that expose a Minecraft version are the ones worth pinging for players.
        $minecraftEggs = EggVariable::query()->where('env_variable', 'MINECRAFT_VERSION')->pluck('egg_id')->flip();

        foreach (Node::query()->where('maintenance_mode', false)->get() as $node) {
            try {
                // One request per node returns the live usage of all its servers.
                $response = $this->repository->setNode($node)->getHttpClient()->get('/api/servers');
                $list = json_decode($response->getBody()->__toString(), true) ?: [];
            } catch (\Throwable $exception) {
                $this->warn("Node {$node->name} unreachable: {$exception->getMessage()}");
                continue;
            }

            $servers = Server::query()->where('node_id', $node->id)->with(['allocation', 'node'])->get()->keyBy('uuid');

            foreach ($list as $entry) {
                $uuid = $entry['configuration']['uuid'] ?? $entry['uuid'] ?? null;
                $server = $uuid ? $servers->get($uuid) : null;
                if (!$server) {
                    continue;
                }

                $usage = $entry['utilization'] ?? [];
                $state = $entry['state'] ?? $usage['state'] ?? 'offline';
                $running = $state === 'running';

                $players = null;
                if ($running && $minecraftEggs->has($server->egg_id)) {
                    $players = optional($this->ping->forServer($server, 1.0))['online'];
                }

                $rows[] = [
                    'server_id' => $server->id,
                    'cpu' => $running ? round((float) ($usage['cpu_absolute'] ?? 0), 2) : 0,
                    'memory' => $running ? (int) ($usage['memory_bytes'] ?? 0) : 0,
                    'players' => $players,
                    'created_at' => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('server_stats')->insert($chunk);
        }

        DB::table('server_stats')->where('created_at', '<', now()->subDays(self::KEEP_DAYS))->delete();

        $this->info('Stored ' . count($rows) . ' data points.');

        return self::SUCCESS;
    }
}
