<?php

namespace Pterodactyl\Http\ViewComposers;

use Illuminate\View\View;
use Pterodactyl\Services\Helpers\AssetHashService;

class AssetComposer
{
    /**
     * AssetComposer constructor.
     */
    public function __construct(private AssetHashService $assetHashService)
    {
    }

    /**
     * Provide access to the asset service in the views.
     */
    public function compose(View $view): void
    {
        $view->with('asset', $this->assetHashService);
        $view->with('siteConfiguration', [
            'name' => config('app.name') ?? 'Pterodactyl',
            'locale' => config('app.locale') ?? 'en',
            'recaptcha' => [
                'enabled' => config('recaptcha.enabled', false),
                'siteKey' => config('recaptcha.website_key') ?? '',
            ],
            'maintenance' => [
                'mode' => in_array(config('mcpanel.maintenance.mode'), ['banner', 'lock'], true) ? config('mcpanel.maintenance.mode') : 'off',
                'message' => (string) config('mcpanel.maintenance.message'),
            ],
            'discord' => [
                'enabled' => (bool) \Pterodactyl\Http\Controllers\Base\DiscordAuthController::enabled(),
            ],
            'statusPage' => filter_var(config('mcpanel.status_page.enabled'), FILTER_VALIDATE_BOOLEAN),
            'verifyEmail' => filter_var(config('mcpanel.registration.verify_email'), FILTER_VALIDATE_BOOLEAN),
            'branding' => [
                'logo' => \Pterodactyl\Services\Branding\BrandingService::logoUrl(),
                'background' => \Pterodactyl\Services\Branding\BrandingService::backgroundUrl(),
                'defaultTheme' => \Pterodactyl\Services\Branding\BrandingService::defaultTheme(),
            ],
            'subdomains' => app(\Pterodactyl\Services\Subdomains\SubdomainService::class)->enabled(),
        ]);
    }
}
