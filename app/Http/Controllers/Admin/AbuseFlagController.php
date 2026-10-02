<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Models\AbuseFlag;
use Pterodactyl\Services\StaffAudit;
use Pterodactyl\Http\Controllers\Controller;

class AbuseFlagController extends Controller
{
    public function __construct(private AlertsMessageBag $alert)
    {
    }

    /**
     * Open flags from the abuse scan; ?show=resolved lists the recently resolved ones instead.
     */
    public function index(Request $request): View
    {
        $resolved = $request->query('show') === 'resolved';

        $query = AbuseFlag::query()->with(['server.user', 'server.node', 'resolver']);
        $resolved
            ? $query->whereNotNull('resolved_at')->orderByDesc('resolved_at')
            : $query->open()->orderByDesc('last_seen_at');

        return view('admin.abuse.index', [
            'flags' => $query->paginate(50)->appends($request->query()),
            'resolved' => $resolved,
            'openCount' => AbuseFlag::openCount(),
        ]);
    }

    /**
     * Mark a flag as resolved or ignored. The server and its owner are not touched.
     */
    public function resolve(Request $request, AbuseFlag $flag): RedirectResponse
    {
        if ($flag->resolved_at === null) {
            $flag->forceFill(['resolved_at' => now(), 'resolved_by' => $request->user()->id])->save();

            $server = $flag->server;
            StaffAudit::record('abuse.resolved', $server?->name ?? '#' . $flag->server_id, ['type' => $flag->type], $server?->user, $server);
        }

        $this->alert->success(trans('admin/abuse.flags.resolved'))->flash();

        return redirect()->route('admin.abuse');
    }
}
