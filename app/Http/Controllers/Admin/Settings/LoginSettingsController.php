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
use Pterodactyl\Http\Controllers\Base\DiscordAuthController;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

class LoginSettingsController extends Controller
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
        return view('admin.settings.login', [
            'callbackUrl' => DiscordAuthController::callbackUrl(),
            'hasSecret' => (bool) config('mcpanel.discord.client_secret'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'verify_email' => 'required|boolean',
            'max_accounts_per_ip' => 'required|integer|min:0|max:1000',
            'discord_enabled' => 'required|boolean',
            'discord_client_id' => 'nullable|string|max:32|regex:/^[0-9]*$/',
            'discord_client_secret' => 'nullable|string|max:128',
            'discord_allow_registration' => 'required|boolean',
            'status_page' => 'required|boolean',
        ]);

        $bool = fn ($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';

        $this->settings->set('settings::mcpanel:registration:verify_email', $bool($data['verify_email']));
        $this->settings->set('settings::mcpanel:registration:max_accounts_per_ip', (string) $data['max_accounts_per_ip']);
        $this->settings->set('settings::mcpanel:discord:enabled', $bool($data['discord_enabled']));
        $this->settings->set('settings::mcpanel:discord:client_id', $data['discord_client_id'] ?: '(empty)');
        $this->settings->set('settings::mcpanel:discord:allow_registration', $bool($data['discord_allow_registration']));
        $this->settings->set('settings::mcpanel:status_page:enabled', $bool($data['status_page']));

        // Left empty, the stored secret is kept (it is never shown again once saved).
        if (!empty($data['discord_client_secret'])) {
            $this->settings->set('settings::mcpanel:discord:client_secret', $this->encrypter->encrypt($data['discord_client_secret']));
        }

        StaffAudit::record('settings.login');

        $this->kernel->call('queue:restart');
        $this->alert->success(trans('admin/settings_login.saved'))->flash();

        return redirect()->route('admin.settings.login');
    }
}
