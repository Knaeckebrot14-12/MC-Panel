<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\StaffAuditLog;
use Pterodactyl\Http\Controllers\Controller;

class AuditLogController extends Controller
{
    /**
     * Read-only log of what team members did in the admin area.
     */
    public function index(Request $request): View
    {
        $query = StaffAuditLog::query()->with(['user', 'targetUser', 'targetServer'])->orderByDesc('id');

        $group = (string) $request->query('group', '');
        if ($group !== '' && preg_match('/^[a-z]+$/', $group)) {
            $query->where('action', 'like', $group . '.%');
        }

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$search}%"));
            });
        }

        return view('admin.audit.index', [
            'logs' => $query->paginate(50)->appends($request->query()),
            'filters' => ['group' => $group, 'search' => $search],
            'groups' => ['user', 'server', 'ticket', 'voucher', 'plan', 'coins'],
        ]);
    }
}
