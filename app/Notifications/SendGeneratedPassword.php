<?php

namespace Pterodactyl\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SendGeneratedPassword extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public string $password)
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Your Password Has Been Reset')
            ->line("A password reset was requested for your account ({$notifiable->username}).")
            ->line('Your new temporary password is:')
            ->line($this->password)
            ->line('Log in using this password. You will be required to choose a new password before you can continue using the panel.')
            ->line('If you did not request this, please contact your administrator immediately.');
    }
}
