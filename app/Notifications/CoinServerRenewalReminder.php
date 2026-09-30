<?php

namespace Pterodactyl\Notifications;

use Pterodactyl\Models\Server;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Warns a user that a coin-funded server is about to renew but their balance won't cover it.
 */
class CoinServerRenewalReminder extends Notification
{
    public function __construct(private Server $server, private int $price)
    {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $params = [
            'server' => $this->server->name,
            'price' => $this->price,
            'balance' => $notifiable->coins,
            'date' => $this->server->paid_with_coins_until->format('Y-m-d H:i'),
        ];

        return (new MailMessage())
            ->subject(trans('coins.mail.reminder_subject', $params))
            ->greeting(trans('tickets.mail.greeting', ['name' => $notifiable->name_first ?: $notifiable->username]))
            ->line(trans('coins.mail.reminder_line', $params))
            ->line(trans('coins.mail.reminder_hint'))
            ->action(trans('coins.mail.reminder_action'), url('/coins/earn'));
    }
}
