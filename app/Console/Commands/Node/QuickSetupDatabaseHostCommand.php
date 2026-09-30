<?php

namespace Pterodactyl\Console\Commands\Node;

use Illuminate\Console\Command;
use Pterodactyl\Models\DatabaseHost;
use Illuminate\Contracts\Encryption\Encrypter;
use Pterodactyl\Services\Databases\Hosts\HostCreationService;

/**
 * Used by install.sh: registers the database server it set up next to Wings, so users can
 * create databases for their game servers right away. Running it again updates the entry.
 */
class QuickSetupDatabaseHostCommand extends Command
{
    protected $description = 'Adds (or updates) a database host for game server databases (used by the installer).';

    protected $signature = 'p:database-host:quick-setup
                            {--name=Game server databases : Name shown in the admin area.}
                            {--host= : Address game servers and the panel use to reach the database server.}
                            {--port=3306 : Port of the database server.}
                            {--username= : Database user that may create databases and users.}
                            {--password= : Password of that user.}
                            {--node= : ID of the node the host is linked to.}';

    public function __construct(private HostCreationService $creation, private Encrypter $encrypter)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $host = trim((string) $this->option('host'));
        if ($host === '' || !$this->option('username') || !$this->option('password')) {
            $this->error('--host, --username and --password are required.');

            return self::FAILURE;
        }

        $data = [
            'name' => $this->option('name'),
            'host' => $host,
            'port' => (int) $this->option('port'),
            'username' => $this->option('username'),
            'password' => $this->option('password'),
            'node_id' => $this->option('node') ? (int) $this->option('node') : null,
        ];

        $existing = DatabaseHost::query()->where('host', $host)->where('port', $data['port'])->first();
        if ($existing) {
            $existing->forceFill([
                'username' => $data['username'],
                'password' => $this->encrypter->encrypt($data['password']),
                'node_id' => $data['node_id'] ?? $existing->node_id,
            ])->save();
            $this->line((string) $existing->id);

            return self::SUCCESS;
        }

        try {
            // Also checks that the panel can log in with these credentials.
            $created = $this->creation->handle($data);
        } catch (\Throwable $exception) {
            $this->error('Could not connect to the database server: ' . $exception->getMessage());

            return self::FAILURE;
        }

        $this->line((string) $created->id);

        return self::SUCCESS;
    }
}
