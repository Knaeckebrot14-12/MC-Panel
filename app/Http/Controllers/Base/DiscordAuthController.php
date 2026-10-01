<?php

namespace Pterodactyl\Http\Controllers\Base;

use Illuminate\Support\Str;
use Pterodactyl\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Facades\Activity;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Services\Users\RegistrationGuard;
use Pterodactyl\Services\Users\UserCreationService;

/**
 * "Log in with Discord": signs in users whose Discord account is linked, links it by a matching
 * verified e-mail address, or creates a new account when that is allowed. Logged-in users use
 * the same flow to link Discord from their account page.
 */
class DiscordAuthController extends Controller
{
    private const API = 'https://discord.com/api';

    public function __construct(private UserCreationService $creation, private RegistrationGuard $guard)
    {
    }

    public static function enabled(): bool
    {
        return filter_var(config('mcpanel.discord.enabled'), FILTER_VALIDATE_BOOLEAN)
            && config('mcpanel.discord.client_id')
            && config('mcpanel.discord.client_secret');
    }

    public static function callbackUrl(): string
    {
        return rtrim(config('app.url'), '/') . '/auth/discord/callback';
    }

    public function redirect(Request $request): RedirectResponse
    {
        if (!self::enabled()) {
            return redirect('/auth/login');
        }

        $state = Str::random(40);
        $request->session()->put('discord_oauth', [
            'state' => $state,
            'link_user' => $request->user() ? $request->user()->id : null,
        ]);

        return redirect('https://discord.com/oauth2/authorize?' . http_build_query([
            'client_id' => config('mcpanel.discord.client_id'),
            'redirect_uri' => self::callbackUrl(),
            'response_type' => 'code',
            'scope' => 'identify email',
            'state' => $state,
            'prompt' => 'none',
        ]));
    }

    public function callback(Request $request): RedirectResponse
    {
        $session = $request->session()->pull('discord_oauth');
        $linking = $session['link_user'] ?? null;
        $fail = fn (string $code) => redirect(($linking ? '/account' : '/auth/login') . '?discord_error=' . $code);

        if (!self::enabled() || !$session || !hash_equals($session['state'], (string) $request->query('state'))) {
            return $fail('state');
        }
        if (!$request->query('code')) {
            return $fail('cancelled');
        }

        $profile = $this->fetchProfile((string) $request->query('code'));
        if (!$profile) {
            return $fail('discord');
        }

        if ($linking) {
            return $this->link($request, (int) $linking, $profile);
        }

        $user = User::query()->where('discord_id', $profile['id'])->first();

        // An existing account with the same, Discord-verified address gets linked automatically.
        if (!$user && $profile['verified'] && $profile['email']) {
            $user = User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($profile['email'])])->first();
            $user?->forceFill(['discord_id' => $profile['id'], 'discord_username' => $profile['username']])->save();
        }

        if (!$user) {
            if (!filter_var(config('mcpanel.discord.allow_registration'), FILTER_VALIDATE_BOOLEAN)) {
                return $fail('no_account');
            }
            if (!$profile['verified'] || !$profile['email']) {
                return $fail('unverified');
            }

            try {
                $this->guard->assertCanRegister($request->ip());
                $user = $this->register($profile);
                $this->guard->afterRegistration($user, $request->ip(), true);
                \Pterodactyl\Services\Notifications\TeamAlerts::newRegistration($user);
            } catch (DisplayException) {
                return $fail('ip_limit');
            }
        }

        // Accounts protected by two-factor authentication keep using password + code.
        if ($user->use_totp) {
            return $fail('two_factor');
        }

        $user->forceFill(['discord_username' => $profile['username']])->save();

        app(\Pterodactyl\Services\Users\LoginAlertService::class)->check($user, $request);
        Auth::login($user, true);
        $request->session()->regenerate();

        Activity::event('auth:discord.login')->subject($user)->withRequestMetadata()->log();

        return redirect('/');
    }

    private function link(Request $request, int $userId, array $profile): RedirectResponse
    {
        $user = $request->user();
        if (!$user || $user->id !== $userId) {
            return redirect('/auth/login');
        }

        $taken = User::query()->where('discord_id', $profile['id'])->where('id', '!=', $user->id)->exists();
        if ($taken) {
            return redirect('/account?discord_error=taken');
        }

        $user->forceFill(['discord_id' => $profile['id'], 'discord_username' => $profile['username']])->save();
        Activity::event('user:account.discord-linked')->subject($user)->log();

        return redirect('/account?discord=linked');
    }

    /**
     * @return array{id: string, username: string, name: string, email: string|null, verified: bool}|null
     */
    private function fetchProfile(string $code): ?array
    {
        try {
            $token = Http::asForm()->timeout(10)->post(self::API . '/oauth2/token', [
                'client_id' => config('mcpanel.discord.client_id'),
                'client_secret' => config('mcpanel.discord.client_secret'),
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => self::callbackUrl(),
            ]);
            if (!$token->successful() || !$token->json('access_token')) {
                return null;
            }

            $me = Http::withToken($token->json('access_token'))->timeout(10)->get(self::API . '/users/@me');
            if (!$me->successful() || !$me->json('id')) {
                return null;
            }

            return [
                'id' => (string) $me->json('id'),
                'username' => (string) $me->json('username'),
                'name' => (string) ($me->json('global_name') ?: $me->json('username')),
                'email' => $me->json('email'),
                'verified' => (bool) $me->json('verified'),
            ];
        } catch (\Throwable $exception) {
            report($exception);

            return null;
        }
    }

    private function register(array $profile): User
    {
        // Panel usernames: lowercase letters, digits, "_", "." and "-", starting/ending alphanumeric.
        $base = trim(preg_replace('/[^a-z0-9_.-]/', '', mb_strtolower($profile['username'])), '._-');
        $base = strlen($base) >= 3 ? substr($base, 0, 24) : 'discord' . substr($profile['id'], -6);
        $username = $base;
        for ($i = 2; User::query()->where('username', $username)->exists(); ++$i) {
            $username = $base . $i;
        }

        return $this->creation->handle([
            'email' => $profile['email'],
            'username' => $username,
            'name_first' => mb_substr($profile['name'], 0, 191) ?: $username,
            'name_last' => '(Discord)',
            'password' => Str::password(32),
            'discord_id' => $profile['id'],
            'discord_username' => $profile['username'],
        ]);
    }
}
