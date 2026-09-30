<?php

namespace Pterodactyl\Notifications;

use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailAddress extends Notification
{
    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $notifiable->language ?: config('app.locale');

        return (new MailMessage())
            ->subject(trans('verification.mail.subject', ['app' => config('app.name')], $locale))
            ->greeting(trans('verification.mail.greeting', ['name' => $notifiable->name_first ?: $notifiable->username], $locale))
            ->line(trans('verification.mail.line', ['app' => config('app.name')], $locale))
            ->action(trans('verification.mail.action', [], $locale), self::url($notifiable))
            ->line(trans('verification.mail.ignore', [], $locale));
    }

    /**
     * Signed link valid for three days. The hash ties it to the address it was sent to,
     * so a link stops working once the address is changed.
     */
    public static function url(object $user): string
    {
        return URL::temporarySignedRoute('auth.verify-email', now()->addDays(3), [
            'id' => $user->id,
            'hash' => sha1(mb_strtolower($user->email)),
        ]);
    }
}
