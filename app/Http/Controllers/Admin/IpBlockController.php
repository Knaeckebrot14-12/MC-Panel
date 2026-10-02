<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\IpBlock;
use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\LoginFailure;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Services\StaffAudit;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Security\IpLockoutService;

class IpBlockController extends Controller
{
    /** Duration choices for a manual block, in minutes. */
    public const DURATIONS = [60, 360, 1440, 10080, 43200];

    public function __construct(private AlertsMessageBag $alert, private IpLockoutService $lockout)
    {
    }

    /**
     * Blocked IPs, the recent failed attempts and the form for a manual block.
     */
    public function index(): View
    {
        $active = IpBlock::query()->active()->orderByDesc('blocked_until')->get();

        $blockCounts = IpBlock::query()
            ->whereIn('ip', $active->pluck('ip'))
            ->where('reason', IpBlock::REASON_AUTO)
            ->where('created_at', '>=', now()->subDays(IpLockoutService::ESCALATION_DAYS))
            ->selectRaw('ip, COUNT(*) as total')
            ->groupBy('ip')
            ->pluck('total', 'ip');

        $failures = LoginFailure::query()->orderByDesc('id')->limit(100)->get();

        $targeted = LoginFailure::query()
            ->whereNotNull('username')
            ->where('created_at', '>=', now()->subDay())
            ->selectRaw('username, COUNT(*) as attempts, COUNT(DISTINCT ip) as ips')
            ->groupBy('username')
            ->orderByDesc('attempts')
            ->limit(10)
            ->get();

        $history = IpBlock::query()
            ->where(function ($query) {
                $query->where('blocked_until', '<=', now())->orWhereNotNull('unblocked_at');
            })
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return view('admin.security.ipblocks', [
            'active' => $active,
            'blockCounts' => $blockCounts,
            'failures' => $failures,
            'targeted' => $targeted,
            'history' => $history,
            'lockout' => $this->lockout,
            'durations' => self::DURATIONS,
        ]);
    }

    /**
     * Blocks an IP address by hand.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ip' => 'required|string|ip',
            'minutes' => 'required|integer|in:' . implode(',', self::DURATIONS),
        ]);

        try {
            $block = $this->lockout->blockManually($data['ip'], (int) $data['minutes'], $request->user());
        } catch (\InvalidArgumentException) {
            $this->alert->danger(trans('admin/ipblock.cannot_block'))->flash();

            return redirect()->route('admin.security.ipblocks')->withInput();
        }

        StaffAudit::record('ipblock.blocked', $block->ip, ['duration' => IpLockoutService::durationLabel((int) $data['minutes'])]);
        $this->alert->success(trans('admin/ipblock.blocked', ['ip' => $block->ip]))->flash();

        return redirect()->route('admin.security.ipblocks');
    }

    /**
     * Lifts a block.
     */
    public function destroy(IpBlock $block): RedirectResponse
    {
        $this->lockout->unblock($block->ip, request()->user());

        StaffAudit::record('ipblock.unblocked', $block->ip);
        $this->alert->success(trans('admin/ipblock.unblocked', ['ip' => $block->ip]))->flash();

        return redirect()->route('admin.security.ipblocks');
    }
}
