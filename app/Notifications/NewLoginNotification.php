<?php

namespace Pterodactyl\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewLoginNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $ip, public string $agent, public string $time)
    {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(trans('auth.new_login.subject'))
            ->line(trans('auth.new_login.intro', ['username' => $notifiable->username]))
            ->line(trans('auth.new_login.details', ['time' => $this->time, 'ip' => $this->ip]))
            ->line($this->agent ?: '-')
            ->line(trans('auth.new_login.ok'))
            ->action(trans('auth.new_login.button'), url('/account'))
            ->line(trans('auth.new_login.not_you'));
    }
}
