<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Pterodactyl\Models\CoinServerPlan;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\StaffAudit;

class PlanController extends Controller
{
    public function __construct(private AlertsMessageBag $alert)
    {
    }

    public function index(): View
    {
        return view('admin.plans.index', [
            'plans' => CoinServerPlan::query()->orderBy('sort_order')->orderBy('monthly_price')->get(),
        ]);
    }

    public function edit(CoinServerPlan $plan): View
    {
        return view('admin.plans.edit', ['plan' => $plan]);
    }

    public function store(Request $request): RedirectResponse
    {
        $plan = CoinServerPlan::query()->create($this->validated($request));
        StaffAudit::record('plan.created', $plan->name);

        $this->alert->success(trans('admin/plans.notices.created'))->flash();

        return redirect()->route('admin.plans');
    }

    public function update(Request $request, CoinServerPlan $plan): RedirectResponse
    {
        $plan->update($this->validated($request));
        StaffAudit::record('plan.updated', $plan->name);

        $this->alert->success(trans('admin/plans.notices.updated'))->flash();

        return redirect()->route('admin.plans');
    }

    public function delete(CoinServerPlan $plan): RedirectResponse
    {
        StaffAudit::record('plan.deleted', $plan->name);
        $plan->delete();

        $this->alert->success(trans('admin/plans.notices.deleted'))->flash();

        return redirect()->route('admin.plans');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|min:1|max:191',
            'description' => 'nullable|string|max:191',
            'memory' => 'required|integer|min:128',
            'disk' => 'required|integer|min:128',
            'cpu' => 'required|integer|min:0',
            'backups' => 'required|integer|min:0',
            'monthly_price' => 'required|integer|min:0',
            'sort_order' => 'nullable|integer',
        ]);

        $data['active'] = $request->boolean('active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
