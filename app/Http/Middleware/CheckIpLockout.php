<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Pterodactyl\Models\IpBlock;
use Pterodactyl\Services\Security\IpLockoutService;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Refuses the sign-in endpoints (login, 2FA checkpoint, passkey login, register, forgot/reset
 * password) with a 429 while the client's IP is blocked for too many failed logins, see
 * IpLockoutService. Only requests that send data are refused: the pages themselves stay
 * reachable, as does everything for people who are already logged in, because blocking the
 * whole panel for everybody behind a shared IP would be too harsh.
 *
 * The message says nothing about accounts.
 */
class CheckIpLockout
{
    public function __construct(private IpLockoutService $lockout)
    {
    }

    public function handle(Request $request, \Closure $next): mixed
    {
        if ($request->isMethodSafe() || $request->user() !== null) {
            return $next($request);
        }

        $block = $this->lockout->blockFor($request->ip());
        if ($block === null) {
            return $next($request);
        }

        throw $this->refusal($block);
    }

    private function refusal(IpBlock $block): HttpException
    {
        $seconds = max(1, (int) now()->diffInSeconds($block->blocked_until, false));
        $minutes = max(1, (int) ceil($seconds / 60));

        return new HttpException(
            429,
            trans('auth.ip_blocked', ['minutes' => $minutes]),
            null,
            ['Retry-After' => (string) $seconds]
        );
    }
}
