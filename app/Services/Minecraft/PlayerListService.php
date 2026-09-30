<?php

namespace Pterodactyl\Services\Minecraft;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Str;
use Pterodactyl\Models\Server;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;
use Pterodactyl\Repositories\Wings\DaemonCommandRepository;

/**
 * Whitelist, operators and bans of a Minecraft server. While the server runs, changes go
 * through console commands (so the server applies them instantly); while it is offline the
 * JSON files are edited directly, exactly as the server itself would write them.
 */
class PlayerListService
{
    public const FILES = [
        'whitelist' => 'whitelist.json',
        'ops' => 'ops.json',
        'banned_players' => 'banned-players.json',
        'banned_ips' => 'banned-ips.json',
    ];

    public const ACTIONS = [
        'whitelist_add', 'whitelist_remove', 'op', 'deop', 'ban', 'pardon', 'ban_ip', 'pardon_ip', 'kick',
        'whitelist_on', 'whitelist_off',
    ];

    public function __construct(
        private DaemonFileRepository $files,
        private DaemonServerRepository $servers,
        private DaemonCommandRepository $commands,
        private ServerListPing $ping,
    ) {
    }

    /**
     * Everything the players tab shows.
     */
    public function overview(Server $server): array
    {
        $lists = [];
        foreach (self::FILES as $key => $file) {
            $lists[$key] = $this->readList($server, $file);
        }

        $properties = $this->readProperties($server);

        return [
            'running' => $this->isRunning($server),
            'online' => $this->ping->forServer($server),
            'whitelist_enabled' => ($properties['white-list'] ?? 'false') === 'true',
            'online_mode' => ($properties['online-mode'] ?? 'true') === 'true',
            'lists' => $lists,
        ];
    }

    /**
     * Applies one change. Returns "command" when it went through the console, "file" otherwise.
     *
     * @throws DisplayException
     */
    public function apply(Server $server, string $action, ?string $target, ?string $reason): string
    {
        $target = trim((string) $target);
        $reason = trim(preg_replace('/[\r\n]+/', ' ', (string) $reason));
        $reason = mb_substr($reason, 0, 100);

        if (in_array($action, ['ban_ip', 'pardon_ip'], true)) {
            if (!filter_var($target, FILTER_VALIDATE_IP)) {
                throw new DisplayException(trans('server_players.errors.invalid_ip'));
            }
        } elseif (!in_array($action, ['whitelist_on', 'whitelist_off'], true)) {
            // Java names are 3-16 characters; Bedrock players via Geyser/Floodgate carry a "." or "*" prefix.
            if (!preg_match('/^[.*]?[A-Za-z0-9_]{1,16}$/', $target)) {
                throw new DisplayException(trans('server_players.errors.invalid_name'));
            }
        }

        if ($this->isRunning($server)) {
            $this->commands->setServer($server)->send($this->command($action, $target, $reason));

            return 'command';
        }

        if ($action === 'kick') {
            throw new DisplayException(trans('server_players.errors.kick_offline'));
        }

        $this->applyToFiles($server, $action, $target, $reason);

        return 'file';
    }

    public function isRunning(Server $server): bool
    {
        try {
            $details = $this->servers->setServer($server)->getDetails();

            return ($details['state'] ?? $details['utilization']['state'] ?? 'offline') === 'running';
        } catch (\Throwable) {
            return false;
        }
    }

    private function command(string $action, string $target, string $reason): string
    {
        return match ($action) {
            'whitelist_add' => "whitelist add $target",
            'whitelist_remove' => "whitelist remove $target",
            'op' => "op $target",
            'deop' => "deop $target",
            'ban' => trim("ban $target $reason"),
            'pardon' => "pardon $target",
            'ban_ip' => trim("ban-ip $target $reason"),
            'pardon_ip' => "pardon-ip $target",
            'kick' => trim("kick $target $reason"),
            'whitelist_on' => 'whitelist on',
            'whitelist_off' => 'whitelist off',
        };
    }

    /**
     * @throws DisplayException
     */
    private function applyToFiles(Server $server, string $action, string $target, string $reason): void
    {
        if (in_array($action, ['whitelist_on', 'whitelist_off'], true)) {
            $this->writeProperty($server, 'white-list', $action === 'whitelist_on' ? 'true' : 'false');

            return;
        }

        $now = now()->format('Y-m-d H:i:s O');
        [$file, $matchKey] = match ($action) {
            'whitelist_add', 'whitelist_remove' => [self::FILES['whitelist'], 'name'],
            'op', 'deop' => [self::FILES['ops'], 'name'],
            'ban', 'pardon' => [self::FILES['banned_players'], 'name'],
            'ban_ip', 'pardon_ip' => [self::FILES['banned_ips'], 'ip'],
        };

        $list = array_values(array_filter(
            $this->readList($server, $file),
            fn ($entry) => strcasecmp((string) ($entry[$matchKey] ?? ''), $target) !== 0
        ));

        if (in_array($action, ['whitelist_add', 'op', 'ban'], true)) {
            $profile = $this->profile($server, $target);
            $entry = match ($action) {
                'whitelist_add' => $profile,
                'op' => $profile + ['level' => 4, 'bypassesPlayerLimit' => false],
                'ban' => $profile + ['created' => $now, 'source' => 'Panel', 'expires' => 'forever', 'reason' => $reason ?: 'Banned by an operator.'],
            };
            $list[] = $entry;
        } elseif ($action === 'ban_ip') {
            $list[] = ['ip' => $target, 'created' => $now, 'source' => 'Panel', 'expires' => 'forever', 'reason' => $reason ?: 'Banned by an operator.'];
        }

        $this->files->setServer($server)->putContent($file, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * UUID and exact spelling of a player, as the server would store it.
     *
     * @throws DisplayException
     */
    private function profile(Server $server, string $name): array
    {
        $onlineMode = ($this->readProperties($server)['online-mode'] ?? 'true') === 'true';

        if ($onlineMode && !Str::startsWith($name, ['.', '*'])) {
            try {
                $response = Http::timeout(5)->get('https://api.mojang.com/users/profiles/minecraft/' . rawurlencode($name));
                if ($response->successful() && $response->json('id')) {
                    $id = $response->json('id');

                    return [
                        'uuid' => substr($id, 0, 8) . '-' . substr($id, 8, 4) . '-' . substr($id, 12, 4) . '-' . substr($id, 16, 4) . '-' . substr($id, 20),
                        'name' => $response->json('name'),
                    ];
                }
            } catch (\Throwable) {
                throw new DisplayException(trans('server_players.errors.lookup_failed'));
            }

            throw new DisplayException(trans('server_players.errors.unknown_player', ['name' => $name]));
        }

        // Offline-mode servers derive the UUID from the name, the same way the server does.
        $hash = md5('OfflinePlayer:' . $name, true);
        $hash[6] = chr(ord($hash[6]) & 0x0F | 0x30);
        $hash[8] = chr(ord($hash[8]) & 0x3F | 0x80);

        return ['uuid' => Uuid::fromBytes($hash)->toString(), 'name' => $name];
    }

    private function readList(Server $server, string $file): array
    {
        try {
            $data = json_decode($this->files->setServer($server)->getContent($file, 1048576), true);

            return is_array($data) ? array_values($data) : [];
        } catch (\Throwable) {
            return [];
        }
    }

    private function readProperties(Server $server): array
    {
        try {
            $content = $this->files->setServer($server)->getContent('server.properties', 262144);
        } catch (\Throwable) {
            return [];
        }

        $properties = [];
        foreach (preg_split('/\r?\n/', $content) as $line) {
            if ($line === '' || str_starts_with(ltrim($line), '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $properties[trim($key)] = trim($value);
        }

        return $properties;
    }

    private function writeProperty(Server $server, string $key, string $value): void
    {
        try {
            $content = $this->files->setServer($server)->getContent('server.properties', 262144);
        } catch (\Throwable) {
            $content = '';
        }

        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
        $content = preg_match($pattern, $content)
            ? preg_replace($pattern, "$key=$value", $content)
            : rtrim($content, "\n") . "\n$key=$value\n";

        $this->files->setServer($server)->putContent('server.properties', $content);
    }
}
