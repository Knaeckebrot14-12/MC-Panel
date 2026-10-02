<?php

namespace Pterodactyl\Services\Abuse;

use Pterodactyl\Models\Node;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\AbuseFlag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Pterodactyl\Services\Notifications\PushService;
use Pterodactyl\Services\Notifications\DiscordWebhook;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;

/**
 * Looks for servers that behave like they are abused (crypto mining, DDoS tools, ...) and flags them
 * for the team. It is deliberately conservative and never acts against a server or its owner itself.
 *
 *  - cpu:     CPU stays at/above a share of the server's CPU limit for a long window (history from
 *             the server_stats table that p:stats:collect fills every 5 minutes)
 *  - miner:   known miner keywords in the console or the root folder of servers that are busy
 *  - network: outbound traffic far above a threshold, from the byte counters Wings reports
 *             (skipped silently when Wings doesn't report them)
 */
class AbuseScanService
{
    /** Resolved flags of the same server and type don't come back for this long (hours). */
    public const COOLDOWN_HOURS = 24;

    /** At most this many busy servers per scan get their console and files checked for miners. */
    private const MAX_MINER_CHECKS = 40;

    /** A server must use at least this share of its CPU cap right now to be searched for miners. */
    private const MINER_CPU_SHARE = 0.5;

    /** Unlimited servers are measured against this many threads of the node at most (default when unknown: 2). */
    private const MAX_UNLIMITED_THREADS = 4;

    public function __construct(
        private DaemonServerRepository $daemon,
        private DaemonFileRepository $files,
        private PushService $push,
    ) {
    }

    public static function enabled(): bool
    {
        return filter_var(config('mcpanel.abuse.enabled'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Runs one scan over every reachable node.
     *
     * @return array{checked: int, new: int, refreshed: int, flags: array<int, string>}
     */
    public function scan(): array
    {
        $summary = ['checked' => 0, 'new' => 0, 'refreshed' => 0, 'flags' => []];
        if (!self::enabled()) {
            return $summary;
        }

        $cfg = $this->settings();
        $cpuHistory = $cfg['cpu_percent'] > 0 ? $this->cpuHistory($cfg['cpu_minutes']) : collect();
        $minerCandidates = [];

        foreach (Node::query()->where('maintenance_mode', false)->get() as $node) {
            $live = $this->liveUsage($node);
            if ($live === null) {
                continue;
            }

            $servers = Server::query()->where('node_id', $node->id)->whereNull('status')->with('user')->get();
            foreach ($servers as $server) {
                $entry = $live[$server->uuid] ?? null;
                if (!$entry || ($entry['state'] ?? 'offline') !== 'running') {
                    continue;
                }
                ++$summary['checked'];

                $usage = $entry['utilization'] ?? [];
                $cap = $this->cpuCap($server, $node);

                if ($cfg['cpu_percent'] > 0 && ($row = $cpuHistory->get($server->id))) {
                    $details = $this->cpuSignal($server, $cap, $row, $cfg);
                    if ($details) {
                        $this->tally($summary, $this->raise($server, AbuseFlag::TYPE_CPU, $details));
                    }
                }

                if ($cfg['network_enabled'] && $cfg['network_mib_per_min'] > 0 && isset($usage['network']['tx_bytes'])) {
                    $details = $this->networkSignal($server, (int) $usage['network']['tx_bytes'], $cfg);
                    if ($details) {
                        $this->tally($summary, $this->raise($server, AbuseFlag::TYPE_NETWORK, $details));
                    }
                }

                // Miners burn CPU: only busy servers are worth a console and file check.
                $cpuNow = (float) ($usage['cpu_absolute'] ?? 0);
                if ($cfg['miner_enabled'] && $cfg['keywords'] && $cap > 0 && $cpuNow >= $cap * self::MINER_CPU_SHARE) {
                    $minerCandidates[] = ['cpu' => $cpuNow / $cap, 'server' => $server];
                }
            }
        }

        usort($minerCandidates, fn ($a, $b) => $b['cpu'] <=> $a['cpu']);
        foreach (array_slice($minerCandidates, 0, self::MAX_MINER_CHECKS) as $candidate) {
            $matches = $this->minerEvidence($candidate['server'], $cfg['keywords']);
            if ($matches) {
                $this->tally($summary, $this->raise($candidate['server'], AbuseFlag::TYPE_MINER, ['matches' => $matches]));
            }
        }

        return $summary;
    }

    /**
     * What the keyword search finds in the last console lines and the root folder of a server.
     * Public so the matching can be tested without Wings.
     *
     * @param array<int, string> $keywords
     * @param array<int, string> $consoleLines
     * @param array<int, string> $fileNames
     *
     * @return array<int, array{source: string, keyword: string, text: string}>
     */
    public static function matchKeywords(array $keywords, array $consoleLines, array $fileNames): array
    {
        if (!$keywords) {
            return [];
        }

        // Whole words only, so "randomx" doesn't match "RandomXP" and the like.
        $quoted = array_map(fn ($keyword) => preg_quote($keyword, '/'), $keywords);
        $pattern = '/(?<![a-z0-9])(' . implode('|', $quoted) . ')(?![a-z0-9])/i';

        $found = [];
        foreach ($consoleLines as $line) {
            $line = trim(preg_replace('/\e\[[0-9;]*m/', '', $line));
            // What players type into the chat or as commands says nothing about the server itself.
            if ($line === '' || preg_match('/\]:\s*(\[[^\]]+\]\s*)?<[^>\s]+>\s/', $line) || stripos($line, 'issued server command') !== false) {
                continue;
            }
            if (preg_match($pattern, $line, $m)) {
                $found[] = ['source' => 'log', 'keyword' => strtolower($m[1]), 'text' => mb_substr($line, 0, 200)];
            }
        }
        foreach ($fileNames as $name) {
            if (preg_match($pattern, $name, $m)) {
                $found[] = ['source' => 'file', 'keyword' => strtolower($m[1]), 'text' => mb_substr($name, 0, 200)];
            }
        }

        return array_slice($found, 0, 5);
    }

    /**
     * The scan settings with sane limits applied.
     *
     * @return array<string, mixed>
     */
    public static function settings(): array
    {
        $bool = fn (string $key) => filter_var(config('mcpanel.abuse.' . $key), FILTER_VALIDATE_BOOLEAN);

        return [
            'enabled' => self::enabled(),
            'cpu_percent' => max(0, min(100, (int) config('mcpanel.abuse.cpu_percent'))),
            'cpu_minutes' => max(10, (int) config('mcpanel.abuse.cpu_minutes')),
            'miner_enabled' => $bool('miner_enabled'),
            'keywords' => self::parseKeywords((string) config('mcpanel.abuse.miner_keywords')),
            'network_enabled' => $bool('network_enabled'),
            'network_mib_per_min' => max(0, (int) config('mcpanel.abuse.network_mib_per_min')),
            'network_minutes' => max(5, (int) config('mcpanel.abuse.network_minutes')),
            'notify_discord' => $bool('notify_discord'),
            'notify_push' => $bool('notify_push'),
        ];
    }

    /**
     * One keyword per line or comma separated, lowercase, no duplicates.
     *
     * @return array<int, string>
     */
    public static function parseKeywords(string $text): array
    {
        $words = array_map(fn ($word) => mb_strtolower(trim($word)), preg_split('/[\r\n,]+/', $text) ?: []);

        return array_slice(array_values(array_unique(array_filter($words, fn ($word) => $word !== '' && mb_strlen($word) <= 64))), 0, 100);
    }

    /**
     * Live usage of every server on a node (one Wings request), keyed by server UUID; null when unreachable.
     *
     * @return array<string, array<string, mixed>>|null
     */
    private function liveUsage(Node $node): ?array
    {
        try {
            $response = $this->daemon->setNode($node)->getHttpClient()->get('/api/servers', ['timeout' => 15]);
            $list = json_decode((string) $response->getBody(), true);
        } catch (\Throwable) {
            return null;
        }

        $result = [];
        foreach (is_array($list) ? $list : [] as $entry) {
            $uuid = $entry['configuration']['uuid'] ?? $entry['uuid'] ?? null;
            if ($uuid) {
                $result[$uuid] = $entry;
            }
        }

        return $result;
    }

    /**
     * The CPU the server may use, in percent of one thread (100 = one thread). Without a limit it is
     * the node's thread count, but at most four threads (two when unknown), so a normal busy
     * server on a big machine is never measured against an unreachable cap.
     */
    private function cpuCap(Server $server, Node $node): int
    {
        if ((int) $server->cpu > 0) {
            return (int) $server->cpu;
        }

        $threads = (int) data_get($node->monitor_state, 'last.threads', 0);

        return 100 * max(1, min($threads > 0 ? $threads : 2, self::MAX_UNLIMITED_THREADS));
    }

    /**
     * Per server: how many samples the last minutes hold and the lowest/average CPU among them.
     */
    private function cpuHistory(int $minutes): \Illuminate\Support\Collection
    {
        // Two minutes of slack: the collector's timestamps drift a little.
        return DB::table('server_stats')
            ->where('created_at', '>=', now()->subMinutes($minutes + 2))
            ->selectRaw('server_id, COUNT(*) as samples, MIN(cpu) as min_cpu, AVG(cpu) as avg_cpu')
            ->groupBy('server_id')
            ->get()
            ->keyBy('server_id');
    }

    /**
     * @param array<string, mixed> $cfg
     *
     * @return array<string, mixed>|null
     */
    private function cpuSignal(Server $server, int $cap, object $row, array $cfg): ?array
    {
        $needed = (int) ceil($cfg['cpu_minutes'] / 5);
        $threshold = $cap * $cfg['cpu_percent'] / 100;
        if ((int) $row->samples < $needed || (float) $row->min_cpu < $threshold) {
            return null;
        }

        return [
            'minutes' => $cfg['cpu_minutes'],
            'threshold_percent' => $cfg['cpu_percent'],
            'cap' => $cap,
            'unlimited' => (int) $server->cpu <= 0,
            'min' => round((float) $row->min_cpu, 1),
            'avg' => round((float) $row->avg_cpu, 1),
        ];
    }

    /**
     * Keeps the byte counter samples of the server and returns details when the average outbound
     * rate over the window is above the threshold. Counters that went backwards (container restart)
     * start a new window.
     *
     * @param array<string, mixed> $cfg
     *
     * @return array<string, mixed>|null
     */
    private function networkSignal(Server $server, int $txBytes, array $cfg): ?array
    {
        $key = 'abuse:net:' . $server->id;
        $now = time();
        $window = $cfg['network_minutes'] * 60;

        $samples = Cache::get($key, []);
        $samples = is_array($samples) ? $samples : [];
        $last = end($samples);
        if ($last && $txBytes < $last[1]) {
            $samples = [];
        }
        $samples[] = [$now, $txBytes];
        // Keep the window plus one sample interval.
        $samples = array_values(array_filter($samples, fn ($sample) => $sample[0] >= $now - $window - 330));
        Cache::put($key, $samples, now()->addHours(3));

        $first = $samples[0];
        $span = $now - $first[0];
        if ($span < $window * 0.8) {
            return null;
        }

        $rate = ($txBytes - $first[1]) / 1048576 / ($span / 60);
        if ($rate < $cfg['network_mib_per_min']) {
            return null;
        }

        return [
            'minutes' => (int) round($span / 60),
            'mib_per_min' => round($rate, 1),
            'threshold' => $cfg['network_mib_per_min'],
        ];
    }

    /**
     * @param array<int, string> $keywords
     *
     * @return array<int, array{source: string, keyword: string, text: string}>
     */
    private function minerEvidence(Server $server, array $keywords): array
    {
        $lines = [];
        try {
            $response = $this->daemon->setServer($server)->getHttpClient()->get(
                sprintf('/api/servers/%s/logs', $server->uuid),
                ['query' => ['size' => 100], 'timeout' => 10]
            );
            $data = json_decode((string) $response->getBody(), true)['data'] ?? [];
            $lines = is_array($data) ? array_map('strval', $data) : [];
        } catch (\Throwable) {
        }

        $names = [];
        try {
            foreach ($this->files->setServer($server)->getDirectory('/') as $file) {
                if (isset($file['name'])) {
                    $names[] = (string) $file['name'];
                }
            }
        } catch (\Throwable) {
        }

        return self::matchKeywords($keywords, $lines, $names);
    }

    /**
     * Creates the flag, or refreshes the open one of the same server and type. Returns [flag, isNew]
     * (only a new flag notifies the team), or null when the cooldown after a resolved flag suppresses it.
     *
     * @param array<string, mixed> $details
     *
     * @return array{0: AbuseFlag, 1: bool}|null
     */
    private function raise(Server $server, string $type, array $details): ?array
    {
        $open = AbuseFlag::query()->open()->where('server_id', $server->id)->where('type', $type)->first();
        if ($open) {
            $open->forceFill(['last_seen_at' => now(), 'details' => $details])->save();

            return [$open, false];
        }

        // Someone just ruled on this: don't ask again right away.
        $recentlyResolved = AbuseFlag::query()->where('server_id', $server->id)->where('type', $type)
            ->where('resolved_at', '>=', now()->subHours(self::COOLDOWN_HOURS))->exists();
        if ($recentlyResolved) {
            return null;
        }

        $flag = AbuseFlag::query()->create([
            'server_id' => $server->id,
            'type' => $type,
            'details' => $details,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);
        $this->notify($flag, $server);

        return [$flag, true];
    }

    /**
     * @param array{checked: int, new: int, refreshed: int, flags: array<int, string>} $summary
     * @param array{0: AbuseFlag, 1: bool}|null $result
     */
    private function tally(array &$summary, ?array $result): void
    {
        if (!$result) {
            return;
        }
        [$flag, $isNew] = $result;
        if (!$isNew) {
            ++$summary['refreshed'];

            return;
        }
        ++$summary['new'];
        $summary['flags'][] = $flag->type . ':' . $flag->server_id;
    }

    /**
     * Discord webhook and push to the team: once per new flag.
     */
    private function notify(AbuseFlag $flag, Server $server): void
    {
        $cfg = $this->settings();
        $title = trans('admin/abuse.alerts.title', ['server' => $server->name]);
        $body = trans('admin/abuse.types.' . $flag->type) . ': ' . $flag->describe();
        $url = route('admin.abuse');

        if ($cfg['notify_discord']) {
            DiscordWebhook::send(config('mcpanel.monitoring.discord_webhook'), $title, $body . "\n" . $url, DiscordWebhook::COLOR_ORANGE, [
                trans('admin/abuse.alerts.field_server') => $server->name,
                trans('admin/abuse.alerts.field_owner') => $server->user?->username ?? '-',
                trans('admin/abuse.alerts.field_type') => trans('admin/abuse.types.' . $flag->type),
            ]);
        }

        if ($cfg['notify_push']) {
            $this->push->sendToTeam($title, $body, $url);
        }
    }
}
