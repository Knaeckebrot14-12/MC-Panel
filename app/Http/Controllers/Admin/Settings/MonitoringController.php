<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Services\StaffAudit;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Encryption\Encrypter;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Notifications\DiscordWebhook;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class MonitoringController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private Kernel $kernel,
        private Encrypter $encrypter,
        private SettingsRepositoryInterface $settings,
    ) {
    }

    public function index(): View
    {
        return view('admin.settings.monitoring', [
            'hasWebhook' => DiscordWebhook::isValidUrl(config('mcpanel.monitoring.discord_webhook')),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'discord_webhook' => ['nullable', 'string', 'max:255', function ($attribute, $value, $fail) {
                if ($value !== null && $value !== '' && !DiscordWebhook::isValidUrl($value)) {
                    $fail(trans('admin/monitoring.settings.webhook_invalid'));
                }
            }],
            'remove_webhook' => 'sometimes|boolean',
            'notify_offline' => 'required|boolean',
            'disk_percent' => 'required|integer|min:0|max:100',
            'memory_percent' => 'required|integer|min:0|max:100',
        ]);

        if ($request->boolean('remove_webhook')) {
            $this->settings->set('settings::mcpanel:monitoring:discord_webhook', '(empty)');
        } elseif (!empty($data['discord_webhook'])) {
            // Stored encrypted and never shown again: whoever has the URL can post into the channel.
            $this->settings->set('settings::mcpanel:monitoring:discord_webhook', $this->encrypter->encrypt($data['discord_webhook']));
        }

        $this->settings->set('settings::mcpanel:monitoring:notify_offline', $request->boolean('notify_offline') ? 'true' : 'false');
        $this->settings->set('settings::mcpanel:monitoring:disk_percent', (string) $data['disk_percent']);
        $this->settings->set('settings::mcpanel:monitoring:memory_percent', (string) $data['memory_percent']);

        StaffAudit::record('settings.monitoring');
        $this->kernel->call('queue:restart');
        $this->alert->success(trans('admin/monitoring.settings.saved'))->flash();

        return redirect()->route('admin.settings.monitoring');
    }

    public function test(): RedirectResponse
    {
        $ok = DiscordWebhook::send(
            config('mcpanel.monitoring.discord_webhook'),
            trans('admin/monitoring.settings.test_title'),
            trans('admin/monitoring.settings.test_body'),
            DiscordWebhook::COLOR_BLUE
        );

        $ok
            ? $this->alert->success(trans('admin/monitoring.settings.test_sent'))->flash()
            : $this->alert->danger(trans('admin/monitoring.settings.test_failed'))->flash();

        return redirect()->route('admin.settings.monitoring');
    }
}
