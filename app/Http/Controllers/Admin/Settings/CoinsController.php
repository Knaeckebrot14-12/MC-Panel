<?php

namespace Pterodactyl\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Illuminate\Http\Response;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\StaffAudit;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;
use Pterodactyl\Http\Requests\Admin\Settings\CoinsSettingsFormRequest;

class CoinsController extends Controller
{
    public function __construct(private SettingsRepositoryInterface $settings)
    {
    }

    /**
     * Render UI for editing the coin economy settings: earning via
     * Linkvertise and the AFK page, and the shop's coin prices.
     */
    public function index(): View
    {
        return view('admin.settings.coins');
    }

    /**
     * Handle request to update coin system settings.
     *
     * @throws \Pterodactyl\Exceptions\Model\DataValidationException
     * @throws \Pterodactyl\Exceptions\Repository\RecordNotFoundException
     */
    public function update(CoinsSettingsFormRequest $request): Response
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }

        StaffAudit::record('coins.settings');

        return response('', 204);
    }
}
