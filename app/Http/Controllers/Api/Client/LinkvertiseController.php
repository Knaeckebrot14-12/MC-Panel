<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\LinkvertiseClaim;
use Pterodactyl\Exceptions\DisplayException;

class LinkvertiseController extends ClientApiController
{
    /**
     * Creates a fresh, single-use claim and returns a Linkvertise link wrapping
     * the callback that redeems it. Linkvertise doesn't offer a link-creation
     * API, so the link is built directly from the admin's publisher ID using
     * their documented dynamic-link URL format — there's no cap on how many of
     * these can be generated.
     *
     * @throws DisplayException
     */
    public function store(): JsonResponse
    {
        $userId = config('coins.linkvertise.user_id');
        if (empty($userId)) {
            throw new DisplayException(trans('coins.errors.linkvertise_unconfigured'));
        }

        $user = request()->user();
        $reward = (int) config('coins.linkvertise.reward');
        $dailyLimit = (int) config('coins.linkvertise.daily_limit');

        $createdToday = LinkvertiseClaim::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDay())
            ->count();

        if ($createdToday >= $dailyLimit) {
            throw new DisplayException(trans('coins.errors.linkvertise_limit', ['limit' => $dailyLimit]));
        }

        $claim = LinkvertiseClaim::query()->create([
            'user_id' => $user->id,
            'token' => Str::random(40),
            'coins' => $reward,
            'expires_at' => now()->addHours(2),
        ]);

        $target = route('coins.linkvertise.claim', ['token' => $claim->token]);
        $random = round(microtime(true) * 1000) . '.' . random_int(1000000, 9999999);
        $url = sprintf(
            'https://link-to.net/%s/%s/dynamic/?r=%s',
            $userId,
            $random,
            base64_encode($target)
        );

        return new JsonResponse([
            'url' => $url,
            'reward' => $reward,
        ]);
    }
}
