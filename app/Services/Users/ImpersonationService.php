<?php

namespace Pterodactyl\Services\Users;

use Pterodactyl\Models\User;
use Illuminate\Http\Request;
use Pterodactyl\Services\StaffAudit;
use Illuminate\Support\Facades\Auth;
use Pterodactyl\Exceptions\DisplayException;

/**
 * Support view: a staff member with the "users.impersonate" permission sees the panel exactly as
 * a normal user does. The staff member's id stays in the session, a banner shows the support view
 * the whole time, account settings and coins can't be changed (see BlockWhileImpersonating), and
 * start and end are written to the staff audit log.
 */
class ImpersonationService
{
    public const SESSION_KEY = 'mcpanel_impersonator';

    /**
     * @throws DisplayException
     */
    public function start(Request $request, User $staff, User $target): void
    {
        if (!$staff->hasStaffPermission('users.impersonate')) {
            throw new DisplayException(trans('impersonation.errors.forbidden'));
        }
        // Only normal users: viewing a team member's account would hand over their admin rights.
        if ($target->id === $staff->id || $target->isStaff()) {
            throw new DisplayException(trans('impersonation.errors.staff'));
        }
        if ($target->isSuspended()) {
            throw new DisplayException(trans('impersonation.errors.suspended'));
        }
        if ($this->impersonator($request)) {
            throw new DisplayException(trans('impersonation.errors.already'));
        }

        // Logged while the staff member is still the signed-in user (the audit log takes it from there).
        StaffAudit::record('user.impersonated', $target->username, [], $target);

        Auth::guard()->login($target);
        $request->session()->put(self::SESSION_KEY, [
            'id' => $staff->id,
            'username' => $staff->username,
            'target_id' => $target->id,
            'started_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Back to the staff member's own account. Returns the staff member, or null when there was no
     * support view (or the staff account is gone; then the session simply ends).
     */
    public function stop(Request $request): ?User
    {
        $data = $request->session()->pull(self::SESSION_KEY);
        if (!is_array($data)) {
            return null;
        }

        $target = $request->user();
        $staff = User::query()->find($data['id'] ?? 0);
        if (!$staff || !$staff->hasStaffPermission('users.impersonate')) {
            Auth::guard()->logout();
            $request->session()->invalidate();

            return null;
        }

        Auth::guard()->login($staff);
        StaffAudit::record('user.impersonation_ended', $target?->username, [], $target);

        return $staff;
    }

    /**
     * The staff member behind the current session, if this is a support view.
     */
    public function impersonator(Request $request): ?array
    {
        $data = $request->hasSession() ? $request->session()->get(self::SESSION_KEY) : null;

        return is_array($data) ? $data : null;
    }
}
