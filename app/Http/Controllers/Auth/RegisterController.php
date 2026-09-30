<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Pterodactyl\Models\User;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Services\Coins\CoinService;
use Pterodactyl\Http\Requests\Auth\RegisterRequest;
use Pterodactyl\Services\Users\RegistrationGuard;
use Pterodactyl\Services\Users\UserCreationService;

class RegisterController extends AbstractLoginController
{
    public function __construct(
        private UserCreationService $creationService,
        private CoinService $coins,
        private RegistrationGuard $guard,
    ) {
        parent::__construct();
    }

    /**
     * Handle a self-registration request, creating a new (non-admin) user
     * account and logging them in immediately.
     *
     * @throws \Throwable
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $this->guard->assertCanRegister($request->ip());

        $data = $request->validated();
        $referralCode = trim((string) ($data['referral_code'] ?? ''));
        unset($data['referral_code']);

        $user = $this->creationService->handle($data);
        $this->guard->afterRegistration($user, $request->ip());

        $this->applyReferral($user, $referralCode);

        return $this->sendLoginResponse($user, $request);
    }

    /**
     * Links a freshly registered user to whoever invited them and hands out the welcome bonus.
     * An unknown code is silently ignored so a typo never blocks a registration.
     */
    private function applyReferral(User $user, string $code): void
    {
        if ($code === '' || (int) config('coins.referral.referrer_reward') <= 0) {
            return;
        }

        $referrer = User::query()->where('referral_code', strtoupper($code))->first();
        if (!$referrer || $referrer->id === $user->id) {
            return;
        }

        // Inviting yourself with a second account from the same connection earns nothing.
        $ip = RegistrationGuard::countableIp($user->registration_ip);
        if ($ip && $referrer->registration_ip === $ip) {
            return;
        }

        $user->forceFill(['referred_by' => $referrer->id])->save();

        $bonus = (int) config('coins.referral.referred_bonus');
        if ($bonus > 0) {
            $this->coins->credit($user, $bonus, 'referral:bonus', "Welcome bonus — invited by {$referrer->username}");
        }
    }
}
