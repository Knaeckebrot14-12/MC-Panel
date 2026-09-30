<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\User;
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

        // Claim this minute in one query, so requests sent in parallel can't all pass the check above.
        $claimed = User::query()
            ->whereKey($user->id)
            ->where(fn ($query) => $query->whereNull('last_afk_tick_at')
                ->orWhere('last_afk_tick_at', '<=', now()->subSeconds(self::MIN_SECONDS_BETWEEN_TICKS)))
            ->update(['last_afk_tick_at' => now()]);

        if (!$claimed) {
            return new JsonResponse([
                'credited' => false,
                'reason' => 'too_soon',
                'balance' => $user->refresh()->coins,
                'secondsUntilNextTick' => self::MIN_SECONDS_BETWEEN_TICKS,
            ]);
        }

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
