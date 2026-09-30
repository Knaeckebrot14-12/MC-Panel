<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Services\Coins\CoinService;

class AfkController extends ClientApiController
{
    // The client is expected to send a tick roughly once a minute; this is
    // the minimum server-side spacing enforced between credited ticks, a
    // little under a minute to tolerate client-side timer drift.
    private const MIN_SECONDS_BETWEEN_TICKS = 55;

    public function __construct(private CoinService $coins)
    {
        parent::__construct();
    }

    /**
     * Credits a user for a minute spent on the AFK page, provided enough time
     * has actually passed since their last tick and they don't appear to be
     * running an ad blocker.
     */
    public function tick(Request $request): JsonResponse
    {
        $user = $request->user();
        $reward = (int) config('coins.afk.reward_per_minute');

        $secondsSinceLast = $user->last_afk_tick_at
            ? now()->diffInSeconds($user->last_afk_tick_at, true)
            : self::MIN_SECONDS_BETWEEN_TICKS;

        if ($secondsSinceLast < self::MIN_SECONDS_BETWEEN_TICKS) {
            return new JsonResponse([
                'credited' => false,
                'reason' => 'too_soon',
                'balance' => $user->coins,
                'secondsUntilNextTick' => self::MIN_SECONDS_BETWEEN_TICKS - (int) $secondsSinceLast,
            ]);
        }

        $user->update(['last_afk_tick_at' => now()]);

        if ($request->boolean('adblockDetected')) {
            return new JsonResponse([
                'credited' => false,
                'reason' => 'adblock_detected',
                'balance' => $user->coins,
                'secondsUntilNextTick' => self::MIN_SECONDS_BETWEEN_TICKS,
            ]);
        }

        $user = $this->coins->credit($user, $reward, 'afk', 'AFK page — 1 minute');

        return new JsonResponse([
            'credited' => true,
            'reward' => $reward,
            'balance' => $user->coins,
            'secondsUntilNextTick' => self::MIN_SECONDS_BETWEEN_TICKS,
        ]);
    }
}
