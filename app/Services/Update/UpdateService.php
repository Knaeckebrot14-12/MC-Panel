<?php

namespace Pterodactyl\Services\Update;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Pterodactyl\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\PendingRequest;
use Pterodactyl\Exceptions\DisplayException;

/**
 * Knows which build of the panel is installed, which one is the newest on GitHub, and hands
 * update requests to the updater service that runs next to the panel (installer/updater).
 *
 * The panel never touches Docker itself: it only writes request.json into a shared directory
 * and the updater picks it up, builds the new image and swaps the container.
 */
class UpdateService
{
    public const CACHE_KEY = 'mcpanel:update:latest';

    public const ETAG_KEY = 'mcpanel:update:etag';

    /** Seconds the updater's heartbeat may be old before it counts as offline. */
    private const HEARTBEAT_MAX_AGE = 45;

    /**
     * The build that is currently running.
     *
     * @return array{version: string, commit: string|null, short: string|null, built_at: string|null, is_dev: bool}
     */
    public function installed(): array
    {
        $commit = $this->readFile(base_path('.mc-commit'));
        $commit = ($commit && preg_match('/^[0-9a-f]{40}$/', $commit)) ? $commit : null;

        return [
            'version' => $this->readFile(base_path('VERSION')) ?: 'dev',
            'commit' => $commit,
            'short' => $commit ? substr($commit, 0, 7) : null,
            'built_at' => $this->readFile(base_path('.mc-built-at')),
            'is_dev' => $commit === null,
        ];
    }

    /**
     * Asks GitHub for the newest commit on the configured branch. Results are cached, and the
     * ETag makes an unchanged answer cost nothing against GitHub's rate limit.
     *
     * @return array<string, mixed>|null null when GitHub can't be reached and nothing is cached
     */
    public function latest(bool $force = false): ?array
    {
        $latest = $this->fetchLatest($force);
        $this->resolveRelation($latest);

        return $latest;
    }

    private function fetchLatest(bool $force): ?array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if (!$force && $cached && CarbonImmutable::parse($cached['checked_at'])->gt(now()->subMinutes(15))) {
            return $cached;
        }

        $repo = config('mcpanel.repository');
        $branch = config('mcpanel.branch');

        try {
            $headers = ['Accept' => 'application/vnd.github+json'];
            if ($cached && ($etag = Cache::get(self::ETAG_KEY))) {
                $headers['If-None-Match'] = $etag;
            }

            $response = $this->http()->withHeaders($headers)->get("https://api.github.com/repos/$repo/commits/$branch");

            if ($response->status() === 304 && $cached) {
                $cached['checked_at'] = now()->toIso8601String();
                Cache::put(self::CACHE_KEY, $cached, now()->addDay());

                return $cached;
            }

            if (!$response->successful()) {
                return $cached;
            }

            $sha = (string) $response->json('sha');
            $message = (string) $response->json('commit.message');
            $result = [
                'commit' => $sha,
                'short' => substr($sha, 0, 7),
                'title' => Str::before($message, "\n"),
                'message' => trim(Str::after($message, "\n")),
                'date' => $response->json('commit.committer.date'),
                'url' => $response->json('html_url'),
                'version' => $this->remoteVersion($repo, $sha),
                'checked_at' => now()->toIso8601String(),
            ];

            if ($etag = $response->header('ETag')) {
                Cache::put(self::ETAG_KEY, $etag, now()->addDay());
            }
            Cache::put(self::CACHE_KEY, $result, now()->addDay());

            return $result;
        } catch (\Throwable $exception) {
            report($exception);

            return $cached;
        }
    }

    /**
     * The last result of latest() without asking GitHub.
     */
    public function cachedLatest(): ?array
    {
        return Cache::get(self::CACHE_KEY);
    }

    /**
     * Whether the newest known commit differs from the installed one. Development builds
     * (no commit baked in) never report an update.
     */
    public function hasUpdate(?array $latest = null): bool
    {
        $installed = $this->installed();
        $latest ??= $this->cachedLatest();

        if ($installed['is_dev'] || !$latest || $latest['commit'] === $installed['commit']) {
            return false;
        }

        // Only a GitHub version that is *ahead* of the installed one is an update; a panel running
        // commits that were never pushed must not be "updated" backwards. Until GitHub has been
        // asked (latest() does that), a different commit counts as an update.
        $relation = Cache::get($this->relationKey($installed['commit'], $latest['commit']));

        return $relation === null || $relation === 'ahead';
    }

    /**
     * How $head relates to $base on GitHub: ahead, behind, identical, diverged, or unknown
     * when GitHub doesn't know $base (never pushed). Cached, since commits never change.
     */
    private function relation(string $base, string $head): ?string
    {
        $key = $this->relationKey($base, $head);
        if (($cached = Cache::get($key)) !== null) {
            return $cached;
        }

        try {
            $repo = config('mcpanel.repository');
            $response = $this->http()->get("https://api.github.com/repos/$repo/compare/$base...$head");
            if ($response->status() === 404) {
                $relation = 'unknown';
            } elseif ($response->successful()) {
                $relation = (string) $response->json('status');
            } else {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }

        Cache::put($key, $relation, now()->addWeek());

        return $relation;
    }

    private function relationKey(string $base, string $head): string
    {
        return 'mcpanel:update:rel:' . $base . ':' . $head;
    }

    /**
     * Makes sure the relation between the installed and the newest commit is known.
     */
    private function resolveRelation(?array $latest): void
    {
        $installed = $this->installed();
        if ($latest && !$installed['is_dev'] && $latest['commit'] !== $installed['commit']) {
            $this->relation($installed['commit'], $latest['commit']);
        }
    }

    /**
     * The commits that make up the pending update, newest first.
     *
     * @return array<int, array{short: string, title: string, date: string|null, url: string|null}>
     */
    public function changes(): array
    {
        $installed = $this->installed();
        $latest = $this->cachedLatest();
        if ($installed['is_dev'] || !$latest || !$this->hasUpdate($latest)) {
            return [];
        }

        return Cache::remember('mcpanel:update:changes:' . $installed['commit'] . ':' . $latest['commit'], now()->addDay(), function () use ($installed, $latest) {
            try {
                $repo = config('mcpanel.repository');
                $response = $this->http()->get("https://api.github.com/repos/$repo/compare/{$installed['commit']}...{$latest['commit']}");
                if (!$response->successful()) {
                    return [];
                }

                return collect($response->json('commits', []))
                    ->reverse()
                    ->take(30)
                    ->map(fn ($commit) => [
                        'short' => substr($commit['sha'], 0, 7),
                        'title' => Str::before($commit['commit']['message'], "\n"),
                        'date' => $commit['commit']['committer']['date'] ?? null,
                        'url' => $commit['html_url'] ?? null,
                    ])
                    ->values()
                    ->all();
            } catch (\Throwable $exception) {
                report($exception);

                return [];
            }
        });
    }

    public function autoUpdateEnabled(): bool
    {
        return filter_var(config('mcpanel.auto_update'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Whether the updater service is alive. It writes a heartbeat every few seconds.
     */
    public function updaterOnline(): bool
    {
        $heartbeat = $this->dir() . '/heartbeat';

        return is_file($heartbeat) && (time() - (int) @filemtime($heartbeat)) <= self::HEARTBEAT_MAX_AGE;
    }

    /**
     * Progress of the current or most recent update, as written by the updater.
     *
     * @return array<string, mixed>
     */
    public function status(): array
    {
        $raw = $this->readFile($this->dir() . '/status.json');
        $status = $raw ? json_decode($raw, true) : null;
        if (!is_array($status)) {
            $status = ['state' => 'idle'];
        }

        // A request the updater hasn't picked up yet counts as queued.
        if (($status['state'] ?? 'idle') !== 'running' && is_file($this->dir() . '/request.json')) {
            $status['state'] = 'queued';
        }

        $log = $this->readFile($this->dir() . '/update.log') ?: '';
        $status['log'] = implode("\n", array_slice(explode("\n", trim($log)), -60));

        return $status;
    }

    public function isBusy(): bool
    {
        return in_array($this->status()['state'], ['queued', 'running'], true);
    }

    /**
     * Hands an update over to the updater service.
     *
     * @throws DisplayException
     */
    public function requestUpdate(?User $by = null, bool $auto = false): void
    {
        if ($this->installed()['is_dev']) {
            throw new DisplayException(trans('admin/update.errors.dev_build'));
        }
        if (!$this->updaterOnline()) {
            throw new DisplayException(trans('admin/update.errors.updater_offline'));
        }
        if ($this->isBusy()) {
            throw new DisplayException(trans('admin/update.errors.busy'));
        }

        $payload = json_encode([
            'id' => (string) Str::uuid(),
            'auto' => $auto,
            'requested_by' => $by ? $by->username : null,
            'requested_at' => now()->toIso8601String(),
        ]);

        $target = $this->dir() . '/request.json';
        if (@file_put_contents($target . '.tmp', $payload) === false || !@rename($target . '.tmp', $target)) {
            throw new DisplayException(trans('admin/update.errors.not_writable'));
        }
    }

    /**
     * Everything the settings page and the dashboard need in one place.
     *
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $latest = $this->cachedLatest();

        return [
            'installed' => $this->installed(),
            'latest' => $latest,
            'has_update' => $this->hasUpdate($latest),
            'updater_online' => $this->updaterOnline(),
            'auto_update' => $this->autoUpdateEnabled(),
        ];
    }

    private function remoteVersion(string $repo, string $sha): ?string
    {
        try {
            $response = $this->http()->get("https://raw.githubusercontent.com/$repo/$sha/VERSION");

            return $response->successful() ? trim($response->body()) : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private function http(): PendingRequest
    {
        $request = Http::timeout(10)->withUserAgent('MC-Panel-Updater');
        if ($token = config('mcpanel.github_token')) {
            $request = $request->withToken($token);
        }

        return $request;
    }

    private function dir(): string
    {
        return rtrim(config('mcpanel.updater_dir'), '/');
    }

    private function readFile(string $path): ?string
    {
        if (!is_file($path)) {
            return null;
        }

        $content = @file_get_contents($path);

        return $content === false ? null : trim($content);
    }
}
