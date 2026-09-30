<?php

namespace Pterodactyl\Notifications;

use Pterodactyl\Models\Ticket;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketStaffReplied extends Notification
{
    public function __construct(private Ticket $ticket)
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
            ->subject(trans('tickets.mail.subject', ['id' => $this->ticket->id, 'subject' => $this->ticket->subject], $locale))
            ->greeting(trans('tickets.mail.greeting', ['name' => $notifiable->name_first ?: $notifiable->username], $locale))
            ->line(trans('tickets.mail.line', ['id' => $this->ticket->id, 'subject' => $this->ticket->subject], $locale))
            ->action(trans('tickets.mail.action', [], $locale), url('/tickets/' . $this->ticket->id));
    }
}
