<?php

namespace Pterodactyl\Services\Notifications;

use Illuminate\Support\Facades\Http;

/**
 * Posts a message (one embed) to a Discord webhook. Only discord.com webhook URLs are accepted,
 * so a stored URL can never be used to make the panel call arbitrary hosts.
 */
class DiscordWebhook
{
    public const COLOR_RED = 0xDD4B39;
    public const COLOR_GREEN = 0x00A65A;
    public const COLOR_ORANGE = 0xF39C12;
    public const COLOR_BLUE = 0x3C8DBC;

    public static function isValidUrl(?string $url): bool
    {
        return is_string($url)
            && preg_match('#^https://(?:ptb\.|canary\.)?(?:discord\.com|discordapp\.com)/api/webhooks/\d+/[\w-]+$#', $url) === 1;
    }

    /**
     * @param array<string, string> $fields
     */
    public static function send(?string $url, string $title, string $description, int $color = self::COLOR_BLUE, array $fields = []): bool
    {
        if (!self::isValidUrl($url)) {
            return false;
        }

        $embed = [
            'title' => mb_substr($title, 0, 256),
            'description' => mb_substr($description, 0, 4000),
            'color' => $color,
            'timestamp' => now()->toIso8601String(),
            'footer' => ['text' => config('app.name', 'Recoded Ptero')],
        ];
        foreach (array_slice($fields, 0, 10, true) as $name => $value) {
            $embed['fields'][] = ['name' => mb_substr($name, 0, 256), 'value' => mb_substr($value, 0, 1024), 'inline' => true];
        }

        try {
            return Http::timeout(10)->withoutRedirecting()->post($url, [
                'username' => mb_substr(config('app.name', 'Recoded Ptero'), 0, 80),
                'embeds' => [$embed],
                'allowed_mentions' => ['parse' => []],
            ])->successful();
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
