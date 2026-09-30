<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\CoinTransaction;
use Pterodactyl\Models\CoinServerPlan;

class CoinsController extends ClientApiController
{
    /**
     * Returns the requesting user's coin balance along with the current,
     * admin-configured earning rates and shop prices the frontend needs to
     * render the Earn/AFK/Shop pages.
     */
    public function index(): JsonResponse
    {
        $user = request()->user();

        return new JsonResponse([
            'balance' => $user->coins,
            'linkvertise' => [
                'available' => !empty(config('coins.linkvertise.user_id')),
                'reward' => config('coins.linkvertise.reward'),
            ],
            'daily' => CoinExtrasController::dailyState($user),
            'referral' => CoinExtrasController::referralState($user),
            'afk' => [
                'rewardPerMinute' => config('coins.afk.reward_per_minute'),
                'adSlotHtml' => config('coins.afk.ad_slot_html') ?: null,
            ],
            'shop' => [
                'memoryUnitMib' => config('coins.shop.memory_unit_mib'),
                'memoryPrice' => config('coins.shop.memory_price'),
                'diskUnitMib' => config('coins.shop.disk_unit_mib'),
                'diskPrice' => config('coins.shop.disk_price'),
                'cpuUnitPercent' => config('coins.shop.cpu_unit_percent'),
                'cpuPrice' => config('coins.shop.cpu_price'),
                'backupPrice' => config('coins.shop.backup_price'),
                'slotPrice' => config('coins.shop.slot_price'),
                'server' => [
                    'monthlyPrice' => config('coins.server.monthly_price'),
                    'memory' => config('coins.server.memory'),
                    'disk' => config('coins.server.disk'),
                    'cpu' => config('coins.server.cpu'),
                    'backups' => config('coins.server.backups'),
                    'plans' => CoinServerPlan::query()
                        ->where('active', true)
                        ->orderBy('sort_order')
                        ->orderBy('monthly_price')
                        ->get()
                        ->map(fn (CoinServerPlan $plan) => [
                            'id' => $plan->id,
                            'name' => $plan->name,
                            'description' => $plan->description,
                            'memory' => $plan->memory,
                            'disk' => $plan->disk,
                            'cpu' => $plan->cpu,
                            'backups' => $plan->backups,
                            'monthlyPrice' => $plan->monthly_price,
                        ])->values(),
                ],
            ],
        ]);
    }

    /**
     * Returns the requesting user's coin transaction history, newest first.
     */
    public function transactions(): JsonResponse
    {
        $transactions = CoinTransaction::query()
            ->where('user_id', request()->user()->id)
            ->orderByDesc('id')
            ->paginate(50);

        return new JsonResponse([
            'data' => collect($transactions->items())->map(fn (CoinTransaction $transaction) => [
                'amount' => $transaction->amount,
                'type' => $transaction->type,
                'description' => $transaction->description,
                'createdAt' => $transaction->created_at->toIso8601String(),
            ]),
            'meta' => [
                'currentPage' => $transactions->currentPage(),
                'lastPage' => $transactions->lastPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }
}
