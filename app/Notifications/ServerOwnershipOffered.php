<?php

namespace Pterodactyl\Notifications;

use Pterodactyl\Models\User;
use Pterodactyl\Models\Server;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ServerOwnershipOffered extends Notification
{
    public function __construct(private Server $server, private User $from)
    {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $locale = $notifiable->language ?: config('app.locale');

        return (new MailMessage())
            ->subject(trans('server_ownership.mail.subject', ['server' => $this->server->name], $locale))
            ->greeting(trans('server_ownership.mail.greeting', ['name' => $notifiable->name_first ?: $notifiable->username], $locale))
            ->line(trans('server_ownership.mail.line', ['user' => $this->from->username, 'server' => $this->server->name], $locale))
            ->line(trans('server_ownership.mail.expires', ['days' => \Pterodactyl\Services\Servers\OwnershipTransferService::EXPIRES_DAYS], $locale))
            ->action(trans('server_ownership.mail.action', [], $locale), url('/'));
    }
}
