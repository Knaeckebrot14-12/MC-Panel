<?php

namespace Pterodactyl\Http\Middleware;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * While the panel is locked for maintenance, only the team can use the client API. Everybody else
 * can still load their own account (the panel needs it to show the maintenance screen) and log out.
 * Game servers and Wings are not affected.
 */
class PanelMaintenance
{
    public function handle(Request $request, \Closure $next): mixed
    {
        $user = $request->user();

        if (
            config('mcpanel.maintenance.mode') === 'lock'
            && $user && !$user->isStaff()
            && !$request->is('api/client/account', 'api/client/account/languages')
        ) {
            throw new HttpException(503, config('mcpanel.maintenance.message') ?: trans('maintenance.default_message'));
        }

        return $next($request);
    }
}
