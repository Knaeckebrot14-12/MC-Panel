<?php

namespace Pterodactyl\Console\Commands\Coins;

use Illuminate\Console\Command;
use Pterodactyl\Models\Server;
use Pterodactyl\Services\Coins\CoinService;
use Pterodactyl\Services\Servers\SuspensionService;
use Pterodactyl\Services\Servers\ServerDeletionService;

class ChargeCoinFundedServersCommand extends Command
{
    protected $description = 'Charges the monthly coin renewal for servers bought through the coin shop, suspending any whose owner cannot afford it and deleting ones that have sat unpaid past the grace period.';

    protected $signature = 'p:coins:charge-servers';

    public function __construct(
        private CoinService $coins,
        private SuspensionService $suspension,
        private ServerDeletionService $deletion,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $graceDays = (int) config('coins.server.suspension_grace_days');

        $due = Server::query()
            ->whereNotNull('paid_with_coins_until')
            ->where('paid_with_coins_until', '<=', now())
            ->with('user')
            ->get();

        foreach ($due as $server) {
            $user = $server->user;

            // An admin-level suspension takes precedence — don't charge for,
            // or touch the suspension state of, a server the owner's account
            // is already locked out of for unrelated reasons.
            if (!$user || $user->isSuspended()) {
                continue;
            }

            // Servers keep the price they were bought at; older ones fall back to the configured tier.
            $price = (int) ($server->coin_monthly_price ?? config('coins.server.monthly_price'));

            $charged = $this->coins->tryDebit(
                $user,
                $price,
                'shop:server:renewal',
                "Monthly renewal for server #{$server->id}"
            );

            if (!$charged) {
                if ($server->coin_suspended_at && $server->coin_suspended_at->addDays($graceDays)->isPast()) {
                    $this->deletion->handle($server);
                    $this->warn("Deleted server #{$server->id} ({$server->name}) — unpaid for over {$graceDays} days.");

                    continue;
                }

                if (!$server->isSuspended()) {
                    $this->suspension->toggle($server, SuspensionService::ACTION_SUSPEND);
                }

                if (!$server->coin_suspended_at) {
                    $server->update(['coin_suspended_at' => now()]);
                }

                $this->warn("Suspended server #{$server->id} ({$server->name}) — owner could not afford the {$price} coin renewal.");

                continue;
            }

            $server->update([
                'paid_with_coins_until' => now()->addMonth(),
                'coin_suspended_at' => null,
            ]);

            if ($server->isSuspended()) {
                $this->suspension->toggle($server, SuspensionService::ACTION_UNSUSPEND);
                $this->info("Renewed and unsuspended server #{$server->id} ({$server->name}).");
            } else {
                $this->info("Renewed server #{$server->id} ({$server->name}).");
            }
        }
    }
}
