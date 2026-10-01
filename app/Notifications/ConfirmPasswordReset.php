<?php

namespace Pterodactyl\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * First step of "forgot password": only a click on this link (sent to the account's own address)
 * generates the new password, so nobody can reset someone else's password just by knowing it.
 */
class ConfirmPasswordReset extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $url)
    {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(trans('passwords.confirm_mail.subject'))
            ->line(trans('passwords.confirm_mail.intro', ['username' => $notifiable->username]))
            ->action(trans('passwords.confirm_mail.button'), $this->url)
            ->line(trans('passwords.confirm_mail.expires'))
            ->line(trans('passwords.confirm_mail.ignore'));
    }
}
