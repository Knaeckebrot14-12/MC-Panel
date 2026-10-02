<?php

namespace Pterodactyl\Http\Controllers\Base;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Services\Users\ImpersonationService;

class ImpersonationController extends Controller
{
    public function __construct(private ImpersonationService $impersonation)
    {
    }

    /**
     * Ends the support view and returns the staff member to the user's page in the admin area.
     */
    public function leave(Request $request): JsonResponse
    {
        $target = $request->user();
        $staff = $this->impersonation->stop($request);

        return new JsonResponse([
            'redirect' => $staff && $target ? route('admin.users.view', $target->id) : '/auth/login',
        ]);
    }
}
