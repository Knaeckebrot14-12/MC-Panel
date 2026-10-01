<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Pterodactyl\Models\User;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Notifications\ConfirmPasswordReset;
use Pterodactyl\Notifications\SendGeneratedPassword;

class ForgotPasswordController extends Controller
{
    /**
     * Step one of "forgot password": e-mails a confirmation link to the account. Only that link
     * generates a new password (step two), so knowing someone's e-mail or username isn't enough
     * to reset their password.
     *
     * The response is intentionally identical whether or not the account exists,
     * so this endpoint cannot be used to enumerate valid usernames/emails.
     */
    public function sendResetLinkEmail(Request $request): JsonResponse
    {
        $request->validate([
            'login' => 'required|string|max:191',
        ]);

        $login = $request->input('login');
        $field = str_contains($login, '@') ? 'email' : 'username';

        /** @var User|null $user */
        $user = User::query()->where($field, $login)->first();

        if ($user) {
            $url = URL::temporarySignedRoute('auth.password.confirm', now()->addHour(), [
                'user' => $user->id,
                // Tied to the current password, so the link stops working once it was used.
                'hash' => self::passwordFingerprint($user),
            ]);

            Activity::event('auth:reset-password-requested')
                ->withRequestMetadata()
                ->subject($user)
                ->log();

            $user->notify((new ConfirmPasswordReset($url))->locale($user->language ?: config('app.locale')));
        }

        return new JsonResponse([
            'status' => trans('passwords.generated_password_sent'),
        ]);
    }

    /**
     * Step two: the owner of the mailbox clicked the link. A new random password is set and
     * e-mailed; it has to be changed after logging in.
     */
    public function confirm(Request $request, int $user, string $hash): View
    {
        /** @var User|null $model */
        $model = User::query()->find($user);
        $valid = $model && hash_equals(self::passwordFingerprint($model), $hash);

        if ($valid) {
            $password = Str::password(14);

            $model->forceFill([
                'password' => Hash::make($password),
                'must_change_password' => true,
                'remember_token' => Str::random(60),
            ])->saveOrFail();

            Activity::event('auth:reset-password')
                ->withRequestMetadata()
                ->subject($model)
                ->log('a new password was generated and emailed');

            $model->notify(new SendGeneratedPassword($password));
        }

        return view('auth.password-reset-result', ['valid' => $valid]);
    }

    private static function passwordFingerprint(User $user): string
    {
        return substr(hash_hmac('sha256', $user->id . '|' . $user->password, config('app.key')), 0, 32);
    }
}
