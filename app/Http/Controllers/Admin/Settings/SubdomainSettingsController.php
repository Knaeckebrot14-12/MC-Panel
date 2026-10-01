<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Services\StaffAudit;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Encryption\Encrypter;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Subdomains\SubdomainService;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class SubdomainSettingsController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private Kernel $kernel,
        private Encrypter $encrypter,
        private SettingsRepositoryInterface $settings,
        private SubdomainService $subdomains,
    ) {
    }

    public function index(): View
    {
        return view('admin.settings.subdomains', [
            'hasToken' => !empty(config('mcpanel.subdomains.cloudflare_token')),
            'check' => !empty(config('mcpanel.subdomains.cloudflare_token')) ? $this->subdomains->verify() : [],
            'count' => DB::table('server_subdomains')->count(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => 'required|boolean',
            'cloudflare_token' => 'nullable|string|max:255|regex:/^[A-Za-z0-9_-]+$/',
            'domains' => 'nullable|string|max:1000',
        ]);

        $domains = collect(preg_split('/[\s,]+/', strtolower((string) ($data['domains'] ?? ''))))->filter()->unique();
        $invalid = $domains->reject(fn ($d) => SubdomainService::validHostname($d));
        if ($invalid->isNotEmpty()) {
            return redirect()->route('admin.settings.subdomains')->withInput()
                ->withErrors(['domains' => trans('admin/subdomains.invalid_domains', ['domains' => $invalid->implode(', ')])]);
        }

        $this->settings->set('settings::mcpanel:subdomains:enabled', $request->boolean('enabled') ? 'true' : 'false');
        $this->settings->set('settings::mcpanel:subdomains:domains', $domains->isEmpty() ? '(empty)' : $domains->implode(','));
        if (!empty($data['cloudflare_token'])) {
            $this->settings->set('settings::mcpanel:subdomains:cloudflare_token', $this->encrypter->encrypt($data['cloudflare_token']));
        }

        StaffAudit::record('settings.subdomains');
        $this->kernel->call('queue:restart');
        $this->alert->success(trans('admin/subdomains.saved'))->flash();

        return redirect()->route('admin.settings.subdomains');
    }
}
