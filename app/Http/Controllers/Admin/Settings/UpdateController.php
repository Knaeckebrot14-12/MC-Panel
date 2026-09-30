<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Services\StaffAudit;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Update\UpdateService;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class UpdateController extends Controller
{
    public function __construct(private UpdateService $updates, private SettingsRepositoryInterface $settings)
    {
    }

    /**
     * Render the update page: installed vs. newest version, the update button and the auto-update switch.
     */
    public function index(): View
    {
        // Make sure the page never shows an empty "latest" box on a fresh install.
        $this->updates->latest();

        return view('admin.settings.updates', $this->payload());
    }

    /**
     * Live state for the page to poll while an update is running.
     */
    public function status(): JsonResponse
    {
        return response()->json($this->payload());
    }

    /**
     * Ask GitHub right now instead of waiting for the scheduled check.
     */
    public function check(): JsonResponse
    {
        $this->updates->latest(true);

        return response()->json($this->payload());
    }

    /**
     * Hand the update over to the updater service.
     */
    public function run(Request $request): JsonResponse
    {
        $this->updates->latest(true);

        if (!$this->updates->hasUpdate()) {
            return response()->json(['error' => trans('admin/update.errors.nothing_to_update')], 422);
        }

        try {
            $this->updates->requestUpdate($request->user());
        } catch (DisplayException $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        StaffAudit::record('update.started', $this->updates->cachedLatest()['short'] ?? null);

        return response()->json($this->payload());
    }

    /**
     * Switch automatic updates on or off.
     */
    public function auto(Request $request): JsonResponse
    {
        $enabled = $request->boolean('enabled');
        $this->settings->set('settings::mcpanel:auto_update', $enabled ? 'true' : 'false');
        config(['mcpanel.auto_update' => $enabled]);

        StaffAudit::record($enabled ? 'update.auto_on' : 'update.auto_off');

        return response()->json($this->payload());
    }

    private function payload(): array
    {
        return $this->updates->summary() + [
            'status' => $this->updates->status(),
            'changes' => $this->updates->changes(),
            'repository' => config('mcpanel.repository'),
            'branch' => config('mcpanel.branch'),
        ];
    }
}
