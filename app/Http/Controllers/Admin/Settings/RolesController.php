<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Services\StaffAudit;
use Illuminate\Contracts\Console\Kernel;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Users\RolePermissions;

class RolesController extends Controller
{
    public function __construct(private AlertsMessageBag $alert, private Kernel $kernel)
    {
    }

    public function index(): View
    {
        $counts = User::query()->whereIn('role', RolePermissions::ROLES)->selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');

        return view('admin.settings.roles', [
            'matrix' => RolePermissions::matrix(),
            'groups' => RolePermissions::GROUPS,
            'roles' => RolePermissions::ROLES,
            'counts' => $counts,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'array',
            'permissions.*.*' => 'string|in:' . implode(',', RolePermissions::all()),
        ]);

        if ($request->boolean('reset')) {
            RolePermissions::save(RolePermissions::DEFAULTS);
        } else {
            RolePermissions::save((array) $request->input('permissions', []));
        }

        StaffAudit::record('settings.roles');
        $this->kernel->call('queue:restart');
        $this->alert->success(trans('admin/roles.saved'))->flash();

        return redirect()->route('admin.settings.roles');
    }
}
