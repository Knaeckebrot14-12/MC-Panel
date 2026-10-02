<?php

namespace Pterodactyl\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Illuminate\Support\Facades\Auth;
use Pterodactyl\Services\Users\ImpersonationService;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * In the support view (a staff member signed in as a user) the user's servers can be looked at
 * and handled like the user would, but nothing that belongs to the user's account itself:
 * password, e-mail, 2FA, API and SSH keys, coins and purchases, tickets, giving servers away.
 */
class BlockWhileImpersonating
{
    /** Changes under these client API paths are not possible in the support view. */
    private const BLOCKED = [
        'api/client/account*',
        'api/client/coins*',
        'api/client/self-service*',
        'api/client/ownership-requests*',
        'api/client/servers/*/ownership',
        'api/client/tickets*',
        'api/client/push*',
    ];

    public function __construct(private ImpersonationService $impersonation)
    {
    }

    public function handle(Request $request, Closure $next): mixed
    {
        $data = $this->impersonation->impersonator($request);
        if (!$data) {
            return $next($request);
        }

        // The staff member lost the permission (or the account) in the meantime: end it right away.
        $staff = User::query()->find($data['id'] ?? 0);
        if (!$staff || !$staff->hasStaffPermission('users.impersonate') || ($request->user()?->id !== ($data['target_id'] ?? null))) {
            Auth::guard()->logout();
            $request->session()->invalidate();
            throw new HttpException(401, trans('impersonation.errors.ended'));
        }

        if (!$request->isMethodSafe() && $request->is(...self::BLOCKED)) {
            throw new HttpException(403, trans('impersonation.errors.blocked'));
        }

        return $next($request);
    }
}
