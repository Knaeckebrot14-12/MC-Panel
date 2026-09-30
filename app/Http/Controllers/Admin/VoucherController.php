<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Pterodactyl\Models\CoinVoucher;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Services\StaffAudit;
use Pterodactyl\Http\Controllers\Controller;

class VoucherController extends Controller
{
    public function __construct(private AlertsMessageBag $alert)
    {
    }

    public function index(): View
    {
        return view('admin.vouchers.index', [
            'vouchers' => CoinVoucher::query()->orderByDesc('id')->paginate(50),
        ]);
    }

    /**
     * Shows who redeemed a voucher and when.
     */
    public function view(CoinVoucher $voucher): View
    {
        return view('admin.vouchers.view', [
            'voucher' => $voucher,
            'redemptions' => $voucher->redemptions()->with('user')->orderByDesc('id')->paginate(50),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'nullable|string|min:3|max:64|regex:/^[A-Za-z0-9_-]+$/|unique:coin_vouchers,code',
            'quantity' => 'nullable|integer|min:1|max:100',
            'coins' => 'required|integer|min:1|max:10000000',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
            'note' => 'nullable|string|max:191',
        ]);

        $quantity = (int) ($data['quantity'] ?? 1);
        $custom = strtoupper($data['code'] ?? '');

        // A fixed code can only ever be one voucher; bulk creation always generates random codes.
        if ($custom !== '' && $quantity > 1) {
            return redirect()->route('admin.vouchers')->withInput()->withErrors(['quantity' => trans('admin/vouchers.notices.bulk_needs_random')]);
        }

        $codes = [];
        for ($i = 0; $i < $quantity; ++$i) {
            do {
                $code = $custom !== '' ? $custom : strtoupper(Str::random(10));
            } while (CoinVoucher::query()->where('code', $code)->exists());

            CoinVoucher::query()->create([
                'code' => $code,
                'coins' => $data['coins'],
                'max_uses' => $data['max_uses'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
                'note' => $data['note'] ?? null,
                'active' => true,
            ]);
            $codes[] = $code;
        }

        StaffAudit::record('voucher.created', $codes[0], ['count' => count($codes), 'coins' => $data['coins']]);

        $this->alert->success(
            $quantity === 1
                ? trans('admin/vouchers.notices.created', ['code' => $codes[0]])
                : trans('admin/vouchers.notices.created_many', ['count' => $quantity, 'codes' => implode(', ', $codes)])
        )->flash();

        return redirect()->route('admin.vouchers');
    }

    public function toggle(CoinVoucher $voucher): RedirectResponse
    {
        $voucher->update(['active' => !$voucher->active]);
        StaffAudit::record('voucher.toggled', $voucher->code);

        $this->alert->success(trans('admin/vouchers.notices.updated'))->flash();

        return redirect()->route('admin.vouchers');
    }

    public function delete(CoinVoucher $voucher): RedirectResponse
    {
        StaffAudit::record('voucher.deleted', $voucher->code);
        $voucher->delete();

        $this->alert->success(trans('admin/vouchers.notices.deleted'))->flash();

        return redirect()->route('admin.vouchers');
    }
}
