<?php

namespace Pterodactyl\Notifications;

use Pterodactyl\Models\Ticket;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Tells the support team that a ticket was opened or that the customer answered.
 */
class TicketNeedsAttention extends Notification
{
    public function __construct(private Ticket $ticket, private bool $isNew)
    {
    }

    public function via(): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $params = [
            'id' => $this->ticket->id,
            'subject' => $this->ticket->subject,
            'user' => optional($this->ticket->user)->username ?? '—',
        ];
        $type = $this->isNew ? 'new' : 'reply';

        return (new MailMessage())
            ->subject(trans("tickets.staff_mail.{$type}_subject", $params))
            ->greeting(trans('tickets.mail.greeting', ['name' => $notifiable->name_first ?: $notifiable->username]))
            ->line(trans("tickets.staff_mail.{$type}_line", $params))
            ->action(trans('tickets.staff_mail.action'), url('/admin/tickets/view/' . $this->ticket->id));
    }
}
