<?php

namespace Pterodactyl\Console\Commands\Update;

use Illuminate\Console\Command;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class AutoUpdateCommand extends Command
{
    protected $description = 'Turns automatic panel updates on or off (same switch as Settings -> Updates).';

    protected $signature = 'p:update:auto {state : on or off}';

    public function __construct(private SettingsRepositoryInterface $settings)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $state = strtolower((string) $this->argument('state'));
        if (!in_array($state, ['on', 'off'], true)) {
            $this->error('State must be "on" or "off".');

            return self::FAILURE;
        }

        $this->settings->set('settings::mcpanel:auto_update', $state === 'on' ? 'true' : 'false');
        $this->info('Automatic updates are now ' . $state . '.');

        return self::SUCCESS;
    }
}
