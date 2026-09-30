<?php

namespace Pterodactyl\Services\Users;

use Pterodactyl\Models\User;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Notifications\VerifyEmailAddress;

/**
 * Anti-alt-account rules shared by the normal sign-up and the Discord sign-up.
 */
class RegistrationGuard
{
    /**
     * Only public addresses say something about who registers. Private/loopback addresses show
     * up when the real client IP is hidden behind Docker or a proxy; limiting those would
     * lock out everyone at once, so they are not counted.
     */
    public static function countableIp(?string $ip): ?string
    {
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $ip;
        }

        return null;
    }

    /**
     * @throws DisplayException when this address already has the allowed number of accounts
     */
    public function assertCanRegister(?string $ip): void
    {
        $limit = (int) config('mcpanel.registration.max_accounts_per_ip');
        $ip = self::countableIp($ip);
        if ($limit <= 0 || !$ip) {
            return;
        }

        if (User::query()->where('registration_ip', $ip)->count() >= $limit) {
            throw new DisplayException(trans('verification.ip_limit'));
        }
    }

    /**
     * Stores where the account was made and, when required, asks for e-mail confirmation.
     *
     * @param bool $emailTrusted true when the address was already verified elsewhere (Discord)
     */
    public function afterRegistration(User $user, ?string $ip, bool $emailTrusted = false): void
    {
        $user->forceFill(['registration_ip' => $ip]);

        if (!$emailTrusted && filter_var(config('mcpanel.registration.verify_email'), FILTER_VALIDATE_BOOLEAN)) {
            $user->forceFill(['email_verified_at' => null])->save();
            $this->sendVerification($user);

            return;
        }

        $user->save();
    }

    public function sendVerification(User $user): void
    {
        try {
            $user->notify(new VerifyEmailAddress());
        } catch (\Throwable $exception) {
            // A broken mail setup must not break the sign-up; the user can resend from the panel.
            report($exception);
        }
    }
}
