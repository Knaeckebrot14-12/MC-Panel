<?php

namespace Pterodactyl\Listeners;

use Pterodactyl\Facades\Activity;
use Pterodactyl\Services\Users\LoginAlertService;
use Illuminate\Auth\Events\Failed;
use Pterodactyl\Events\Auth\DirectLogin;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Events\Dispatcher;
use Pterodactyl\Extensions\Illuminate\Events\Contracts\SubscribesToEvents;

class AuthenticationListener implements SubscribesToEvents
{
    /**
     * Handles an authentication event by logging the user and information about
     * the request.
     */
    public function login(Failed|DirectLogin $event): void
    {
        $activity = Activity::withRequestMetadata();
        if ($event->user) {
            $activity = $activity->subject($event->user);
        }

        if ($event instanceof Failed) {
            foreach ($event->credentials as $key => $value) {
                $activity = $activity->property($key, $value);
            }
        }

        // Before this login is logged, so it isn't compared with itself.
        if ($event instanceof DirectLogin && $event->user) {
            app(LoginAlertService::class)->check($event->user, request());
        }

        $activity->event($event instanceof Failed ? 'auth:fail' : 'auth:success')->log();
    }

    public function reset(PasswordReset $event): void
    {
        Activity::event('event:password-reset')->withRequestMetadata()->subject($event->user)->log();
    }

    public function subscribe(Dispatcher $events): void
    {
        $events->listen(Failed::class, [self::class, 'login']);
        $events->listen(DirectLogin::class, [self::class, 'login']);
        $events->listen(PasswordReset::class, [self::class, 'reset']);
    }
}
