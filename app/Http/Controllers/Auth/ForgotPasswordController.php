<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Pterodactyl\Models\User;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Notifications\SendGeneratedPassword;

class ForgotPasswordController extends Controller
{
    /**
     * Handle a request to reset a user's password. Rather than emailing a reset
     * link, we generate a new random password immediately, save it, and email
     * that password to the user. They are then required to set a password of
     * their own choosing the next time they log in.
     *
     * The response is intentionally identical whether or not the account exists,
     * so this endpoint cannot be used to enumerate valid usernames/emails.
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'login' => 'required|string',
        ]);

        $login = $request->input('login');
        $field = str_contains($login, '@') ? 'email' : 'username';

        /** @var User|null $user */
        $user = User::query()->where($field, $login)->first();

        if ($user) {
            $password = Str::password(12);

            $user->forceFill([
                'password' => Hash::make($password),
                'must_change_password' => true,
            ])->saveOrFail();

            Activity::event('auth:reset-password')
                ->withRequestMetadata()
                ->subject($user)
                ->log('a new password was generated and emailed');

            $user->notify(new SendGeneratedPassword($password));
        }

        return new JsonResponse([
            'status' => trans('passwords.generated_password_sent'),
        ]);
    }
}
