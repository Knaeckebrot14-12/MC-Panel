<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class StaffPermission
{
    public function handle(Request $request, \Closure $next, string $permission): mixed
    {
        if (!$request->user() || !$request->user()->hasStaffPermission($permission)) {
            throw new AccessDeniedHttpException();
        }

        return $next($request);
    }
}
