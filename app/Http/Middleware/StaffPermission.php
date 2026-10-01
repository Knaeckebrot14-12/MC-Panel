<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * staff:<permission>[,<permission>...] lets team members through who hold at least one of the
 * permissions (Admin -> Settings -> Roles). The owner holds all of them.
 */
class StaffPermission
{
    public function handle(Request $request, \Closure $next, string ...$permissions): mixed
    {
        $user = $request->user();
        if (!$user || !collect($permissions)->contains(fn ($permission) => $user->hasStaffPermission($permission))) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}
