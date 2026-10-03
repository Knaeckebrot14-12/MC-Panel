<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Services\StaffAudit;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Abuse\AbuseScanService;
use Pterodactyl\Services\Notifications\DiscordWebhook;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class AbuseSettingsController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private SettingsRepositoryInterface $settings,
    ) {
    }

    public function index(): View
    {
        return view('admin.settings.abuse', [
            'cfg' => AbuseScanService::settings(),
            'keywordsText' => implode("\n", AbuseScanService::parseKeywords((string) config('mcpanel.abuse.miner_keywords'))),
            'hasWebhook' => DiscordWebhook::isValidUrl(config('mcpanel.monitoring.discord_webhook')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => 'required|boolean',
            'cpu_percent' => 'required|integer|min:50|max:100',
            'cpu_minutes' => 'required|integer|min:10|max:1440',
            'miner_enabled' => 'required|boolean',
            'miner_keywords' => 'nullable|string|max:4000',
            'network_enabled' => 'required|boolean',
            'network_mib_per_min' => 'required|integer|min:1|max:1000000',
            'network_minutes' => 'required|integer|min:5|max:240',
            'notify_discord' => 'required|boolean',
            'notify_push' => 'required|boolean',
        ]);

        foreach (['enabled', 'miner_enabled', 'network_enabled', 'notify_discord', 'notify_push'] as $flag) {
            $this->settings->set('settings::mcpanel:abuse:' . $flag, $request->boolean($flag) ? 'true' : 'false');
        }
        foreach (['cpu_percent', 'cpu_minutes', 'network_mib_per_min', 'network_minutes'] as $number) {
            $this->settings->set('settings::mcpanel:abuse:' . $number, (string) $data[$number]);
        }

        $keywords = AbuseScanService::parseKeywords((string) ($data['miner_keywords'] ?? ''));
        // The trailing line break keeps a list that is just "true", "null" or "empty" from being turned into
        // a boolean/null by SettingsServiceProvider; parseKeywords() ignores it.
        $this->settings->set('settings::mcpanel:abuse:miner_keywords', $keywords ? implode("\n", $keywords) . "\n" : '(empty)');

        StaffAudit::record('settings.abuse');
        $this->alert->success(trans('admin/abuse.settings.saved'))->flash();

        return redirect()->route('admin.settings.abuse');
    }
}
