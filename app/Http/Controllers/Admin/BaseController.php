<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Admin\StatisticsService;
use Pterodactyl\Services\Helpers\SoftwareVersionService;

class BaseController extends Controller
{
    private const AREAS = [
        'users.view' => 'admin.users',
        'servers.view' => 'admin.servers',
        'tickets' => 'admin.tickets',
        'announcements' => 'admin.announcements',
        'audit' => 'admin.audit',
        'maintenance' => 'admin.maintenance',
        'databases' => 'admin.databases',
        'locations' => 'admin.locations',
        'nodes' => 'admin.nodes',
        'coins.vouchers' => 'admin.vouchers',
        'coins.plans' => 'admin.plans',
        'coins.settings' => 'admin.settings.coins',
        'mounts' => 'admin.mounts',
        'nests' => 'admin.nests',
    ];

    /**
     * BaseController constructor.
     */
    public function __construct(private SoftwareVersionService $version)
    {
    }

    /**
     * Return the admin index view.
     */
    public function index(): View|\Illuminate\Http\RedirectResponse
    {
        $user = request()->user();
        if (!$user->hasStaffPermission('overview')) {
            // The first area this team member may open, in the order of the sidebar.
            foreach (self::AREAS as $permission => $route) {
                if ($user->hasStaffPermission($permission)) {
                    return redirect()->route($route);
                }
            }

            return redirect()->route('index');
        }

        return view('admin.index', ['version' => $this->version, 'stats' => app(StatisticsService::class)->summary()]);
    }
}
