<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Restricts a route to the panel owner; regular admins are turned away.
 */
class OwnerOnly
{
    public function handle(Request $request, \Closure $next): mixed
    {
        if (!$request->user() || !$request->user()->isOwner()) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}
