<?php

namespace Pterodactyl\Console\Commands\Update;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Pterodactyl\Services\Update\UpdateService;

class CheckForUpdatesCommand extends Command
{
    protected $description = 'Checks GitHub for a newer panel version and, when automatic updates are enabled, starts the update.';

    protected $signature = 'p:update:check';

    /** How often the same version may be tried automatically before giving up on it. */
    private const MAX_AUTO_ATTEMPTS = 2;

    /** Minutes to wait between two automatic attempts for the same version. */
    private const RETRY_DELAY_MINUTES = 30;

    public function __construct(private UpdateService $updates)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $latest = $this->updates->latest(true);
        if (!$latest) {
            $this->warn('Could not reach GitHub.');

            return self::SUCCESS;
        }

        if (!$this->updates->hasUpdate($latest)) {
            $this->info('The panel is up to date.');

            return self::SUCCESS;
        }

        $this->info('Update available: ' . $latest['short'] . ' - ' . $latest['title']);

        if (!$this->updates->autoUpdateEnabled()) {
            return self::SUCCESS;
        }
        if (!$this->updates->updaterOnline() || $this->updates->isBusy()) {
            return self::SUCCESS;
        }

        // Don't hammer a version that keeps failing: two tries, half an hour apart.
        $key = 'mcpanel:update:auto:' . $latest['commit'];
        $attempts = (int) Cache::get($key . ':count', 0);
        if ($attempts >= self::MAX_AUTO_ATTEMPTS || Cache::has($key . ':wait')) {
            return self::SUCCESS;
        }

        $this->updates->requestUpdate(null, true);
        Cache::put($key . ':count', $attempts + 1, now()->addDay());
        Cache::put($key . ':wait', true, now()->addMinutes(self::RETRY_DELAY_MINUTES));
        $this->info('Automatic update started.');

        return self::SUCCESS;
    }
}
