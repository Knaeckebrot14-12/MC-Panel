<?php

namespace Pterodactyl\Console\Commands\Server;

use Pterodactyl\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Pterodactyl\Services\Backups\InitiateBackupService;

class AutoBackupCommand extends Command
{
    protected $description = 'Creates the automatic backups servers are due for, replacing the oldest unlocked backup when the limit is reached.';

    protected $signature = 'p:backups:auto';

    public function __construct(private InitiateBackupService $backups)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $servers = Server::query()
            ->where('auto_backup_hours', '>', 0)
            ->where('backup_limit', '>', 0)
            ->whereNull('status')
            ->get();

        foreach ($servers as $server) {
            $due = !$server->auto_backup_last_at
                || $server->auto_backup_last_at->copy()->addHours($server->auto_backup_hours)->isPast();
            // After a failure (node offline...) wait half an hour before the next try.
            if (!$due || Cache::has("auto-backup:retry:{$server->id}")) {
                continue;
            }

            try {
                $this->backups->setIsLocked(false)->handle($server, 'Auto-Backup ' . now()->format('Y-m-d H:i'), true);
                $server->forceFill(['auto_backup_last_at' => now()])->save();
                $this->info("Backup started for {$server->uuidShort}");
            } catch (\Throwable $exception) {
                Cache::put("auto-backup:retry:{$server->id}", true, now()->addMinutes(30));
                $this->warn("Backup for {$server->uuidShort} failed: {$exception->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
