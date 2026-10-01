<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Services\StaffAudit;
use Illuminate\Contracts\Console\Kernel;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Branding\BrandingService;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class DesignController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private Kernel $kernel,
        private BrandingService $branding,
        private SettingsRepositoryInterface $settings,
    ) {
    }

    public function index(): View
    {
        return view('admin.settings.design', [
            'logo' => BrandingService::logoUrl(),
            'favicon' => BrandingService::faviconUrl(),
            'background' => BrandingService::backgroundUrl(),
            'accent' => BrandingService::accent(),
            'theme' => BrandingService::defaultTheme(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'default_theme' => 'required|in:dark,light',
            'logo' => 'nullable|file|max:2048',
            'favicon' => 'nullable|file|max:2048',
            'background' => 'nullable|file|max:2048',
            'remove' => 'nullable|array',
            'remove.*' => 'in:logo,favicon,background',
        ]);

        foreach (BrandingService::TYPES as $type) {
            if (in_array($type, $request->input('remove', []), true)) {
                $this->settings->set("settings::mcpanel:branding:$type", '(empty)');
                $this->branding->cleanup($type);
            } elseif ($request->hasFile($type)) {
                $this->settings->set("settings::mcpanel:branding:$type", $this->branding->store($type, $request->file($type)));
            }
        }

        $this->settings->set('settings::mcpanel:branding:accent', strtolower($request->input('accent')));
        $this->settings->set('settings::mcpanel:branding:default_theme', $request->input('default_theme'));

        StaffAudit::record('settings.design');
        $this->kernel->call('queue:restart');
        $this->alert->success(trans('admin/design.saved'))->flash();

        return redirect()->route('admin.settings.design');
    }
}
