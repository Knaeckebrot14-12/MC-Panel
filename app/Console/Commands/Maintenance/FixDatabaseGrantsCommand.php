<?php

namespace Pterodactyl\Console\Commands\Maintenance;

use Illuminate\Console\Command;
use Pterodactyl\Models\Database;
use Pterodactyl\Models\DatabaseHost;
use Pterodactyl\Extensions\DynamicDatabaseConnection;
use Pterodactyl\Repositories\Eloquent\DatabaseRepository;

/**
 * Re-issues the access rights of every server database so they apply to exactly that database.
 *
 * Older versions granted rights on the plain name, which MySQL reads as a pattern ("_" matches any
 * character), so a user's rights could reach further than their own database. This removes that
 * grant and writes the escaped one. It is safe to run any number of times, and it runs daily
 * so database hosts that were offline are repaired later.
 */
class FixDatabaseGrantsCommand extends Command
{
    protected $description = 'Limits the rights of every server database user to exactly its own database.';

    protected $signature = 'p:databases:fix-grants';

    public function __construct(private DynamicDatabaseConnection $dynamic, private DatabaseRepository $repository)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $failed = 0;

        foreach (DatabaseHost::query()->get() as $host) {
            $databases = Database::query()->where('database_host_id', $host->id)->get();
            if ($databases->isEmpty()) {
                continue;
            }

            try {
                $this->dynamic->set('dynamic', $host->id);

                foreach ($databases as $database) {
                    // Old pattern grant away first, then the exact one (order matters).
                    $this->repository->revokeLegacyGrant($database->database, $database->username, $database->remote);
                    $this->repository->assignUserToDatabase($database->database, $database->username, $database->remote);
                }
                $this->repository->flush();
                $this->info("Host {$host->name}: {$databases->count()} database(s) checked.");
            } catch (\Throwable $exception) {
                ++$failed;
                $this->warn("Host {$host->name} could not be reached: {$exception->getMessage()}");
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
