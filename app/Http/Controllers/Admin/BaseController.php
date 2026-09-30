<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Admin\StatisticsService;
use Pterodactyl\Services\Helpers\SoftwareVersionService;

class BaseController extends Controller
{
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
        if (!$user->root_admin) {
            return redirect()->route($user->hasStaffPermission('users.view') ? 'admin.users' : 'admin.tickets');
        }

        return view('admin.index', ['version' => $this->version, 'stats' => app(StatisticsService::class)->summary()]);
    }
}
