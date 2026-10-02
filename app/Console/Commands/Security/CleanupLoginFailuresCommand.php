<?php

namespace Pterodactyl\Console\Commands\Security;

use Illuminate\Console\Command;
use Pterodactyl\Services\Security\IpLockoutService;

class CleanupLoginFailuresCommand extends Command
{
    protected $description = 'Delete old failed login attempts and finished IP blocks.';

    protected $signature = 'p:security:cleanup-logins';

    public function handle(IpLockoutService $lockout): int
    {
        $deleted = $lockout->cleanup();

        $this->info(sprintf(
            'Deleted %d failed login attempt(s) and %d finished IP block(s).',
            $deleted['failures'],
            $deleted['blocks']
        ));

        return self::SUCCESS;
    }
}
