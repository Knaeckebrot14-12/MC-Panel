<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\RateLimiter;
use Pterodactyl\Services\Users\RegistrationGuard;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class EmailVerificationController extends ClientApiController
{
    public function __construct(private RegistrationGuard $guard)
    {
        parent::__construct();
    }

    /**
     * Sends the confirmation mail again (at most three times per 15 minutes).
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->email_verified_at) {
            return new JsonResponse(['verified' => true]);
        }

        $key = 'verify-email:' . $user->id;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw new TooManyRequestsHttpException(RateLimiter::availableIn($key), trans('verification.too_many'));
        }
        RateLimiter::hit($key, 900);

        $this->guard->sendVerification($user);

        return new JsonResponse(['verified' => false, 'sent' => true]);
    }
}
