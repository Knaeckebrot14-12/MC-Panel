<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Services\StaffAudit;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class MaintenanceController extends Controller
{
    public function __construct(private AlertsMessageBag $alert, private SettingsRepositoryInterface $settings)
    {
    }

    public function index(): View
    {
        return view('admin.maintenance');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['off', 'banner', 'lock'])],
            'message' => 'nullable|string|max:1000',
        ]);

        $this->settings->set('settings::mcpanel:maintenance:mode', $data['mode']);
        $this->settings->set('settings::mcpanel:maintenance:message', trim((string) ($data['message'] ?? '')) ?: '(empty)');

        StaffAudit::record('maintenance.updated', trans('admin/maintenance.modes.' . $data['mode']));

        $this->alert->success(trans('admin/maintenance.saved'))->flash();

        return redirect()->route('admin.maintenance');
    }
}
