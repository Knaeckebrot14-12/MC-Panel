<?php

namespace Pterodactyl\Console\Commands\Coins;

use Illuminate\Console\Command;
use Pterodactyl\Models\Server;
use Pterodactyl\Notifications\CoinServerRenewalReminder;

class RemindCoinFundedServersCommand extends Command
{
    protected $description = 'Emails owners of coin-funded servers that renew soon when their coin balance will not cover the renewal.';

    protected $signature = 'p:coins:remind-servers {--days=3 : How many days ahead of the renewal to warn}';

    public function handle(): void
    {
        $days = max(1, (int) $this->option('days'));

        $servers = Server::query()
            ->whereNotNull('paid_with_coins_until')
            ->whereBetween('paid_with_coins_until', [now(), now()->addDays($days)])
            ->with('user')
            ->get();

        foreach ($servers as $server) {
            $user = $server->user;
            if (!$user || $user->isSuspended()) {
                continue;
            }

            // One reminder per billing period.
            if ($server->coin_reminder_for && $server->coin_reminder_for->equalTo($server->paid_with_coins_until)) {
                continue;
            }

            $price = (int) ($server->coin_monthly_price ?? config('coins.server.monthly_price'));
            if ($user->coins >= $price) {
                continue;
            }

            try {
                $user->notify(
                    (new CoinServerRenewalReminder($server, $price))->locale($user->language ?: config('app.locale'))
                );
            } catch (\Throwable $exception) {
                report($exception);
                continue;
            }

            $server->forceFill(['coin_reminder_for' => $server->paid_with_coins_until])->save();
            $this->info("Reminded {$user->username} about server #{$server->id}.");
        }
    }
}
