<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Pterodactyl\Services\StaffAudit;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\Contracts\Console\Kernel;
use Pterodactyl\Http\Controllers\Controller;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Pterodactyl\Services\Databases\PhpMyAdminService;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Pterodactyl\Http\Requests\Admin\Settings\AdvancedSettingsFormRequest;

class AdvancedController extends Controller
{
    /**
     * AdvancedController constructor.
     */
    public function __construct(
        private AlertsMessageBag $alert,
        private ConfigRepository $config,
        private Kernel $kernel,
        private SettingsRepositoryInterface $settings,
    ) {
    }

    /**
     * Render advanced Panel settings UI.
     */
    public function index(): View
    {
        $showRecaptchaWarning = false;
        if (
            $this->config->get('recaptcha._shipped_secret_key') === $this->config->get('recaptcha.secret_key')
            || $this->config->get('recaptcha._shipped_website_key') === $this->config->get('recaptcha.website_key')
        ) {
            $showRecaptchaWarning = true;
        }

        return view('admin.settings.advanced', [
            'showRecaptchaWarning' => $showRecaptchaWarning,
        ]);
    }

    /**
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function update(AdvancedSettingsFormRequest $request, PhpMyAdminService $phpMyAdmin): RedirectResponse
    {
        $phpMyAdminWasEnabled = PhpMyAdminService::enabled();

        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }

        $phpMyAdminEnabled = $request->input('mcpanel:phpmyadmin:enabled') === 'true';
        if ($phpMyAdminEnabled !== $phpMyAdminWasEnabled) {
            // Turning it off also signs everybody out of phpMyAdmin right away.
            if (!$phpMyAdminEnabled) {
                $phpMyAdmin->endAllSessions();
            }
            StaffAudit::record($phpMyAdminEnabled ? 'settings.phpmyadmin_on' : 'settings.phpmyadmin_off');
        }

        $this->kernel->call('queue:restart');
        $this->alert->success('Advanced settings have been updated successfully and the queue worker was restarted to apply these changes.')->flash();

        return redirect()->route('admin.settings.advanced');
    }
}
