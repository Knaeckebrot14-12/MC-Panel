<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Services\Users\PasskeyService;
use Pterodactyl\Exceptions\DisplayException;

/**
 * Sign in with a passkey, without typing a username (discoverable credentials).
 *
 * A passkey sign-in is only accepted with user verification (PIN, fingerprint, face, ...), so the
 * passkey is "something you have" plus "something you are/know" in one step. That is why it
 * replaces the password AND the TOTP code: no two-factor checkpoint follows, even on accounts
 * with use_totp enabled. Signing in with the password still asks for the TOTP code, and the
 * panel-wide "2FA required" rule (RequireTwoFactorAuthentication) keeps applying to the account
 * unchanged, because it is about the password path, which stays possible.
 *
 * Everything after the verification goes through the same code as the password login
 * (sendLoginResponse): session regeneration, "remember me", the DirectLogin event (which writes
 * the "Logged in" activity and sends the new-login e-mail alert), and the failed-login throttle.
 */
class PasskeyLoginController extends AbstractLoginController
{
    public function __construct(private PasskeyService $passkeys)
    {
        parent::__construct();
    }

    /**
     * Returns the challenge for navigator.credentials.get().
     */
    public function options(Request $request): JsonResponse
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }

        return new JsonResponse(['data' => $this->passkeys->loginOptions($request->session())]);
    }

    /**
     * Verifies the browser's answer and signs the user in.
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            $this->sendLockoutResponse($request);
        }

        $credential = $request->input('credential');
        if (!is_array($credential)) {
            $this->sendFailedLoginResponse($request);
        }

        try {
            $passkey = $this->passkeys->authenticate($request->session(), $credential);
        } catch (DisplayException) {
            // Unknown credential, bad signature, replayed or expired challenge, wrong origin,
            // counter rollback: all look the same from the outside.
            $this->sendFailedLoginResponse($request);
        }

        $user = $passkey->user;

        // Suspended accounts are refused like any other failed sign-in.
        if ($user->isSuspended()) {
            $this->sendFailedLoginResponse($request, $user);
        }

        $response = $this->sendLoginResponse($user, $request);

        Activity::event('auth:passkey.login')
            ->subject($user)
            ->withRequestMetadata()
            ->property('name', $passkey->name)
            ->log();

        return $response;
    }
}
