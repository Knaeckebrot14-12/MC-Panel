<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Lets any team member (supporter, moderator, admin, owner) into the admin area.
 * Individual routes are then narrowed down with StaffPermission / AdminAuthenticate.
 */
class StaffAuthenticate
{
    public function handle(Request $request, \Closure $next): mixed
    {
        if (!$request->user() || !$request->user()->isStaff()) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}
