<?php

namespace Pterodactyl\Services\Admin;

use Carbon\CarbonImmutable;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Numbers for the admin overview. Cached for a minute so the page stays fast on big panels.
 */
class StatisticsService
{
    public function summary(): array
    {
        return Cache::remember('admin:statistics', 60, function () {
            $since30 = now()->subDays(30);
            $since7 = now()->subDays(7);

            $coinsEarned = (int) DB::table('coin_transactions')->where('created_at', '>=', $since30)->where('amount', '>', 0)->sum('amount');
            $coinsSpent = (int) -DB::table('coin_transactions')->where('created_at', '>=', $since30)->where('amount', '<', 0)->sum('amount');

            $nodes = Node::query()->withSum('servers', 'memory')->withSum('servers', 'disk')->withCount('servers')->orderBy('name')->get()
                ->map(fn (Node $node) => [
                    'id' => $node->id,
                    'name' => $node->name,
                    'servers' => $node->servers_count,
                    'memory_percent' => $node->memory > 0 ? (int) round(($node->servers_sum_memory ?? 0) / $node->memory * 100) : 0,
                    'disk_percent' => $node->disk > 0 ? (int) round(($node->servers_sum_disk ?? 0) / $node->disk * 100) : 0,
                    'maintenance' => (bool) $node->maintenance_mode,
                ]);

            return [
                'users' => User::query()->count(),
                'users_new_7d' => User::query()->where('created_at', '>=', $since7)->count(),
                'users_unverified' => User::query()->whereNull('email_verified_at')->count(),
                'servers' => Server::query()->count(),
                'servers_suspended' => Server::query()->where('status', Server::STATUS_SUSPENDED)->count(),
                'servers_coin_funded' => Server::query()->whereNotNull('paid_with_coins_until')->count(),
                'tickets_open' => Ticket::query()->where('status', '!=', 'closed')->count(),
                'coins_circulating' => (int) User::query()->sum('coins'),
                'coins_earned_30d' => $coinsEarned,
                'coins_spent_30d' => $coinsSpent,
                'nodes' => $nodes,
                'registrations' => $this->perDay(DB::table('users'), $since30, 'COUNT(*)'),
                'coins_earned' => $this->perDay(DB::table('coin_transactions')->where('amount', '>', 0), $since30, 'SUM(amount)'),
                'coins_spent' => $this->perDay(DB::table('coin_transactions')->where('amount', '<', 0), $since30, '-SUM(amount)'),
            ];
        });
    }

    /**
     * One value per day for the last 30 days, days without rows included as 0.
     *
     * @return array{labels: string[], values: int[]}
     */
    private function perDay($query, $since, string $aggregate): array
    {
        $rows = $query->where('created_at', '>=', $since)
            ->selectRaw("DATE(created_at) as day, $aggregate as value")
            ->groupBy('day')
            ->pluck('value', 'day');

        $labels = [];
        $values = [];
        for ($day = CarbonImmutable::parse($since)->startOfDay(); $day->lte(now()); $day = $day->addDay()) {
            $labels[] = $day->format('d.m.');
            $values[] = (int) ($rows[$day->toDateString()] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }
}
