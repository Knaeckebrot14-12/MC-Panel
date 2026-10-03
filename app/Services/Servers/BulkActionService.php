<?php

namespace Pterodactyl\Services\Servers;

use Illuminate\Support\Str;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\Server;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Cache\LockTimeoutException;

/**
 * Bulk power actions and console messages for many servers at once (Admin -> Servers -> Bulk actions).
 * The service picks the servers that may be touched and keeps the progress of a run in the cache, so the
 * page can poll it while the queued jobs (one per server) work through the nodes.
 */
class BulkActionService
{
    public const POWER_ACTIONS = ['start', 'stop', 'restart', 'kill'];

    public const MESSAGE_MAX = 200;

    /** Progress is kept for a few hours; the audit log is the permanent record. */
    private const TTL = 21600;

    private const ACTIVE_KEY = 'mcpanel:bulk:active';

    /** How many failed servers are listed on the page. */
    private const MAX_FAILURES = 25;

    /**
     * Servers that can run right now: not suspended, not installing (or failed to install), not restoring a
     * backup and not being transferred. Optionally limited to one node and/or to Minecraft servers.
     *
     * @return Builder<Server>
     */
    public function eligible(?int $nodeId = null, bool $minecraftOnly = false): Builder
    {
        $query = Server::query()
            ->whereNull('servers.status')
            ->whereNotExists(fn ($sub) => $sub->selectRaw('1')->from('server_transfers')->whereColumn('server_transfers.server_id', 'servers.id')->whereNull('server_transfers.successful'));

        if ($nodeId !== null) {
            $query->where('servers.node_id', $nodeId);
        }

        if ($minecraftOnly) {
            $this->onlyMinecraft($query);
        }

        return $query;
    }

    /**
     * Mirrors SubdomainService::isMinecraft() (the client area's isMinecraftServer plus the stock eggs'
     * markers) as a query, so no per-server lookups are needed.
     *
     * @param Builder<Server> $query
     */
    private function onlyMinecraft(Builder $query): void
    {
        $query->where(function (Builder $q) {
            $q->whereExists(fn ($sub) => $sub->selectRaw('1')->from('egg_variables')
                ->whereColumn('egg_variables.egg_id', 'servers.egg_id')
                ->where('egg_variables.env_variable', 'MINECRAFT_VERSION'))
                ->orWhere('servers.startup', 'like', '%{{SERVER_JARFILE}}%')
                ->orWhereExists(fn ($sub) => $sub->selectRaw('1')->from('eggs')
                    ->whereColumn('eggs.id', 'servers.egg_id')
                    ->whereRaw('LOWER(eggs.name) LIKE ?', ['%minecraft%']))
                ->orWhereExists(fn ($sub) => $sub->selectRaw('1')->from('nests')
                    ->whereColumn('nests.id', 'servers.nest_id')
                    ->whereRaw('LOWER(nests.name) LIKE ?', ['%minecraft%']));
        });
    }

    /**
     * How many servers each node (and all together) would be affected, for the preview on the page.
     *
     * @return array{nodes: array<int, array{id: int, name: string, power: int, message: int}>, power: int, message: int}
     */
    public function counts(): array
    {
        $power = $this->eligible()->select('servers.node_id', DB::raw('COUNT(*) as aggregate'))->groupBy('servers.node_id')->pluck('aggregate', 'node_id');
        $message = $this->eligible(null, true)->select('servers.node_id', DB::raw('COUNT(*) as aggregate'))->groupBy('servers.node_id')->pluck('aggregate', 'node_id');

        $nodes = Node::query()->orderBy('name')->get(['id', 'name'])->map(fn (Node $node) => [
            'id' => $node->id,
            'name' => $node->name,
            'power' => (int) ($power[$node->id] ?? 0),
            'message' => (int) ($message[$node->id] ?? 0),
        ])->all();

        return ['nodes' => $nodes, 'power' => (int) $power->sum(), 'message' => (int) $message->sum()];
    }

    /**
     * Cleans a text for the in-game "say" command: control characters and line breaks (which would end the
     * command and start another one) become spaces and the text can never start with "/".
     */
    public function sanitizeMessage(string $message): string
    {
        $message = (string) preg_replace('/[\p{Cc}\p{Cf}\p{Zl}\p{Zp}]+/u', ' ', $message);
        $message = trim((string) preg_replace('/\s+/u', ' ', $message));
        $message = ltrim($message, "/ \t");

        return trim(mb_substr($message, 0, self::MESSAGE_MAX));
    }

    /**
     * @param array<string, scalar|null> $meta
     */
    public function createRun(string $type, array $meta, int $total): string
    {
        $id = (string) Str::uuid();
        Cache::put($this->key($id), array_merge($meta, [
            'type' => $type,
            'total' => $total,
            'started_at' => now()->toIso8601String(),
        ]), self::TTL);

        foreach (['ok', 'failed', 'skipped'] as $counter) {
            Cache::put($this->key($id, $counter), 0, self::TTL);
        }

        return $id;
    }

    /**
     * Only one run at a time: a second click (or a second admin) while servers are still being worked on
     * would restart/kill them twice. Returns false when another run is still unfinished (for at most 15 minutes,
     * so a run whose jobs were lost never blocks the page for good).
     */
    public function claim(string $runId): bool
    {
        if (Cache::add(self::ACTIVE_KEY, $runId, 900)) {
            return true;
        }

        $current = Cache::get(self::ACTIVE_KEY);
        $state = is_string($current) ? $this->run($current) : null;
        if ($state === null || $state['finished']) {
            Cache::put(self::ACTIVE_KEY, $runId, 900);

            return true;
        }

        return false;
    }

    /**
     * Drops a run that was created but never queued.
     */
    public function discard(string $runId): void
    {
        foreach ([null, 'ok', 'failed', 'skipped', 'failures', 'lock'] as $part) {
            Cache::forget($this->key($runId, $part ?? 'meta'));
        }
    }

    /**
     * Called by the jobs. $result is "ok", "failed" or "skipped".
     */
    public function record(string $runId, string $result, ?string $server = null, ?string $error = null): void
    {
        if (!in_array($result, ['ok', 'failed', 'skipped'], true) || !Cache::has($this->key($runId))) {
            return;
        }

        Cache::increment($this->key($runId, $result));

        if ($result === 'failed') {
            try {
                Cache::lock($this->key($runId, 'lock'), 5)->block(3, function () use ($runId, $server, $error) {
                    $failures = Cache::get($this->key($runId, 'failures'), []);
                    if (count($failures) < self::MAX_FAILURES) {
                        $failures[] = ['server' => $server, 'error' => mb_substr((string) $error, 0, 200)];
                        Cache::put($this->key($runId, 'failures'), $failures, self::TTL);
                    }
                });
            } catch (LockTimeoutException) {
                // The counter is what matters; the list of names is best effort.
            }
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function run(string $runId): ?array
    {
        $meta = Cache::get($this->key($runId));
        if (!is_array($meta)) {
            return null;
        }

        $ok = (int) Cache::get($this->key($runId, 'ok'), 0);
        $failed = (int) Cache::get($this->key($runId, 'failed'), 0);
        $skipped = (int) Cache::get($this->key($runId, 'skipped'), 0);
        $total = (int) $meta['total'];
        $done = $ok + $failed + $skipped;

        return $meta + [
            'ok' => $ok,
            'failed' => $failed,
            'skipped' => $skipped,
            'queued' => max(0, $total - $done),
            'finished' => $done >= $total,
            'failures' => Cache::get($this->key($runId, 'failures'), []),
        ];
    }

    private function key(string $runId, string $part = 'meta'): string
    {
        return $part === 'meta' ? "mcpanel:bulk:$runId" : "mcpanel:bulk:$runId:$part";
    }
}
