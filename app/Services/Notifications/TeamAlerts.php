<?php

namespace Pterodactyl\Services\Notifications;

use Pterodactyl\Models\User;
use Pterodactyl\Models\Ticket;

/**
 * Messages to the team's Discord channel (the webhook from Admin -> Settings -> Monitoring) about
 * new support tickets and new registrations. They go out after the response was sent, so the
 * person who just opened a ticket or registered never waits for Discord, and a failing webhook
 * can never make either of them fail.
 */
class TeamAlerts
{
    public static function newTicket(Ticket $ticket): void
    {
        if (!self::enabled('notify_tickets')) {
            return;
        }

        $url = route('admin.tickets.view', $ticket->id);
        $subject = $ticket->subject;
        $username = $ticket->user?->username ?? '-';
        $category = $ticket->category;
        $priority = $ticket->priority;
        $id = $ticket->id;

        self::later(fn () => DiscordWebhook::send(
            config('mcpanel.monitoring.discord_webhook'),
            trans('admin/monitoring.team.ticket_title', ['id' => $id]),
            $subject . "\n" . $url,
            DiscordWebhook::COLOR_BLUE,
            [
                trans('admin/monitoring.team.field_user') => $username,
                trans('admin/monitoring.team.field_category') => (string) $category,
                trans('admin/monitoring.team.field_priority') => (string) $priority,
            ]
        ));
    }

    public static function newRegistration(User $user): void
    {
        if (!self::enabled('notify_registrations')) {
            return;
        }

        $username = $user->username;
        $url = route('admin.users.view', $user->id);

        self::later(fn () => DiscordWebhook::send(
            config('mcpanel.monitoring.discord_webhook'),
            trans('admin/monitoring.team.registration_title'),
            $username . "\n" . $url,
            DiscordWebhook::COLOR_GREEN
        ));
    }

    private static function enabled(string $setting): bool
    {
        return DiscordWebhook::isValidUrl(config('mcpanel.monitoring.discord_webhook'))
            && filter_var(config('mcpanel.monitoring.' . $setting), FILTER_VALIDATE_BOOLEAN);
    }

    private static function later(\Closure $send): void
    {
        app()->terminating(function () use ($send) {
            try {
                $send();
            } catch (\Throwable $exception) {
                report($exception);
            }
        });
    }
}
