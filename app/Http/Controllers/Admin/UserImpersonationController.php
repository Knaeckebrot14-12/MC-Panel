<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Pterodactyl\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Users\ImpersonationService;

class UserImpersonationController extends Controller
{
    public function __construct(private AlertsMessageBag $alert, private ImpersonationService $impersonation)
    {
    }

    /**
     * Opens the panel as this user (support view).
     */
    public function start(Request $request, User $user): RedirectResponse
    {
        try {
            $this->impersonation->start($request, $request->user(), $user);
        } catch (DisplayException $exception) {
            $this->alert->danger($exception->getMessage())->flash();

            return redirect()->route('admin.users.view', $user->id);
        }

        return redirect('/');
    }
}
