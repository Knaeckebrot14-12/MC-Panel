<?php

namespace Pterodactyl\Console\Commands\Node;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Models\DatabaseHost;
use Pterodactyl\Extensions\DynamicDatabaseConnection;

/**
 * Used by install.sh after an upgrade from Pterodactyl: logs in to every database host the way the
 * panel does when it creates a database, and prints one line per host:
 *   ok|<id>|<host>:<port>|<name>
 *   fail|<id>|<host>:<port>|<name>|<error>
 */
class CheckDatabaseHostsCommand extends Command
{
    protected $description = 'Checks that the panel can log in to every database host (used by the installer).';

    protected $signature = 'p:database-hosts:check';

    public function handle(DynamicDatabaseConnection $dynamic): int
    {
        $failed = 0;
        foreach (DatabaseHost::query()->orderBy('id')->get() as $host) {
            $label = sprintf('%d|%s:%d|%s', $host->id, $host->host, $host->port, str_replace(["\n", '|'], ' ', $host->name));
            try {
                $dynamic->set('hostcheck', $host);
                DB::connection('hostcheck')->getPdo();
                $this->line('ok|' . $label);
            } catch (\Throwable $exception) {
                ++$failed;
                $this->line('fail|' . $label . '|' . str_replace(["\n", '|'], ' ', mb_substr($exception->getMessage(), 0, 200)));
            } finally {
                DB::purge('hostcheck');
            }
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
