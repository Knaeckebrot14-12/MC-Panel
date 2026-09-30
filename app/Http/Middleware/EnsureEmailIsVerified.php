<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Keeps accounts that haven't confirmed their e-mail address away from coins and free servers,
 * which is what throwaway accounts would be created for.
 */
class EnsureEmailIsVerified
{
    public function handle(Request $request, \Closure $next): mixed
    {
        if ($request->user() && $request->user()->needsEmailVerification()) {
            throw new AccessDeniedHttpException(trans('verification.required'));
        }

        return $next($request);
    }
}
