<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\User;
use Pterodactyl\Models\CoinVoucher;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Services\Coins\CoinService;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Models\CoinVoucherRedemption;

class CoinExtrasController extends ClientApiController
{
    public function __construct(private CoinService $coins)
    {
        parent::__construct();
    }

    /**
     * Redeems a voucher code, at most once per user.
     *
     * @throws DisplayException
     */
    public function redeemVoucher(Request $request): JsonResponse
    {
        $data = $request->validate(['code' => 'required|string|max:64']);
        $code = trim($data['code']);
        $user = $request->user();

        $coins = DB::transaction(function () use ($code, $user) {
            /** @var CoinVoucher|null $voucher */
            $voucher = CoinVoucher::query()->whereRaw('LOWER(code) = ?', [strtolower($code)])->lockForUpdate()->first();

            if (!$voucher || !$voucher->active) {
                throw new DisplayException(trans('coins.voucher.invalid'));
            }
            if ($voucher->isExpired()) {
                throw new DisplayException(trans('coins.voucher.expired'));
            }
            if ($voucher->isExhausted()) {
                throw new DisplayException(trans('coins.voucher.exhausted'));
            }
            if (CoinVoucherRedemption::query()->where('voucher_id', $voucher->id)->where('user_id', $user->id)->exists()) {
                throw new DisplayException(trans('coins.voucher.already_redeemed'));
            }

            CoinVoucherRedemption::query()->create([
                'voucher_id' => $voucher->id,
                'user_id' => $user->id,
                'coins' => $voucher->coins,
            ]);
            $voucher->increment('uses');

            return $voucher->coins;
        });

        $user = $this->coins->credit($user, $coins, 'voucher', 'Redeemed voucher ' . strtoupper($code));

        return new JsonResponse(['coins' => $coins, 'balance' => $user->coins]);
    }

    /**
     * Claims the daily login reward, growing with the user's streak of consecutive days.
     *
     * @throws DisplayException
     */
    public function claimDaily(Request $request): JsonResponse
    {
        $user = $request->user();
        $state = self::dailyState($user);

        if (!$state['enabled']) {
            throw new DisplayException(trans('coins.daily.disabled'));
        }

        $claimed = DB::transaction(function () use ($user) {
            /** @var User $locked */
            $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $state = self::dailyState($locked);

            if (!$state['available']) {
                throw new DisplayException(trans('coins.daily.already_claimed'));
            }

            $locked->forceFill([
                'last_daily_claim_at' => now(),
                'daily_streak' => $state['nextStreak'],
            ])->save();

            return $state;
        });

        $user = $this->coins->credit(
            $user,
            $claimed['reward'],
            'daily',
            "Daily login reward — day {$claimed['nextStreak']} of your streak"
        );

        return new JsonResponse([
            'reward' => $claimed['reward'],
            'streak' => $claimed['nextStreak'],
            'balance' => $user->coins,
        ]);
    }

    /**
     * Computes the current state of a user's daily reward. Days are calendar days in the app's timezone.
     *
     * @return array{enabled: bool, available: bool, reward: int, streak: int, nextStreak: int}
     */
    public static function dailyState(User $user): array
    {
        $base = (int) config('coins.daily.reward');
        $bonus = (int) config('coins.daily.streak_bonus');
        $max = max(1, (int) config('coins.daily.streak_max'));

        $last = $user->last_daily_claim_at;
        $today = now()->startOfDay();

        $available = !$last || $last->copy()->startOfDay()->lt($today);
        $continues = $last && $last->copy()->startOfDay()->equalTo($today->copy()->subDay());
        $streak = ($last && ($continues || !$available)) ? (int) $user->daily_streak : 0;
        $nextStreak = $available ? ($continues ? (int) $user->daily_streak + 1 : 1) : $streak;

        return [
            'enabled' => $base > 0,
            'available' => $base > 0 && $available,
            'reward' => $base + $bonus * (min($nextStreak, $max) - 1),
            'streak' => $streak,
            'nextStreak' => $nextStreak,
        ];
    }

    /**
     * Summary of the user's referral standing for the Earn page.
     */
    public static function referralState(User $user): array
    {
        $referred = User::query()->where('referred_by', $user->id);

        return [
            'enabled' => (int) config('coins.referral.referrer_reward') > 0,
            'code' => $user->referral_code,
            'referrerReward' => (int) config('coins.referral.referrer_reward'),
            'referredBonus' => (int) config('coins.referral.referred_bonus'),
            'invited' => (clone $referred)->count(),
            'active' => (clone $referred)->whereNotNull('referral_rewarded_at')->count(),
        ];
    }
}
