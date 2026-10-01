<?php

namespace Pterodactyl\Services\Users;

use Pterodactyl\Models\User;
use Illuminate\Http\Request;
use Pterodactyl\Models\ActivityLog;
use Pterodactyl\Notifications\NewLoginNotification;

/**
 * E-mails the account owner when someone logs in from an IP address the account hasn't used for
 * logging in during the last 90 days, so a stolen password gets noticed.
 */
class LoginAlertService
{
    public const EVENTS = ['auth:success', 'auth:discord.login'];

    public function check(User $user, Request $request): void
    {
        try {
            $ip = (string) $request->ip();
            $previous = ActivityLog::query()
                ->whereIn('event', self::EVENTS)
                ->where('actor_id', $user->id)
                ->where('actor_type', $user->getMorphClass())
                ->where('timestamp', '>=', now()->subDays(90));

            // The very first login of an account is no surprise.
            if (!(clone $previous)->exists() || (clone $previous)->where('ip', $ip)->exists()) {
                return;
            }

            $user->notify((new NewLoginNotification(
                $ip,
                mb_substr((string) $request->userAgent(), 0, 200),
                now()->setTimezone(config('app.timezone'))->format('d.m.Y H:i T')
            ))->locale($user->language ?: config('app.locale')));
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
