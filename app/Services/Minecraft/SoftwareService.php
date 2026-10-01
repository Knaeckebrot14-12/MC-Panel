<?php

namespace Pterodactyl\Services\Minecraft;

use Pterodactyl\Models\Server;
use Pterodactyl\Models\EggVariable;
use Pterodactyl\Models\ServerVariable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Repositories\Wings\DaemonPowerRepository;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;

/**
 * The version changer: lists the versions of Paper, Purpur, Folia, Fabric, Vanilla and Velocity
 * and installs one by downloading its server jar over the server's jar file. The Java image is
 * switched to one that fits the version. World and plugins stay where they are.
 */
class SoftwareService
{
    public const TYPES = ['paper', 'purpur', 'folia', 'fabric', 'vanilla', 'velocity'];

    private const FILL = 'https://fill.papermc.io/v3/projects/';

    /** yolks images that exist, for versions the egg itself has no image for. */
    private const YOLKS_JAVA = [8, 11, 16, 17, 18, 19, 21, 22, 23, 24, 25];

    public function __construct(
        private DaemonFileRepository $files,
        private DaemonPowerRepository $power,
        private DaemonServerRepository $daemon,
    ) {
    }

    /**
     * Servers whose startup runs {{SERVER_JARFILE}} (the Minecraft eggs) can switch software.
     */
    public function supports(Server $server): bool
    {
        return str_contains((string) $server->startup, '{{SERVER_JARFILE}}')
            || str_contains((string) $server->egg?->startup, '{{SERVER_JARFILE}}');
    }

    /**
     * Versions of one software, newest first: [['id' => '1.21.4', 'java' => 21], ...].
     *
     * @throws DisplayException
     */
    public function versions(string $type): array
    {
        $this->assertType($type);

        return Cache::remember("mcpanel:software:$type", now()->addHour(), function () use ($type) {
            return match ($type) {
                'paper', 'folia', 'velocity' => $this->fillVersions($type),
                'purpur' => collect(array_reverse($this->json('https://api.purpurmc.org/v2/purpur')['versions'] ?? []))
                    ->map(fn ($v) => ['id' => (string) $v, 'java' => self::javaFor((string) $v)])->values()->all(),
                'fabric' => collect($this->json('https://meta.fabricmc.net/v2/versions/game'))
                    ->filter(fn ($v) => !empty($v['stable']))
                    ->map(fn ($v) => ['id' => (string) $v['version'], 'java' => self::javaFor((string) $v['version'])])->values()->all(),
                'vanilla' => collect($this->json('https://piston-meta.mojang.com/mc/game/version_manifest_v2.json')['versions'] ?? [])
                    ->filter(fn ($v) => ($v['type'] ?? '') === 'release')
                    ->map(fn ($v) => ['id' => (string) $v['id'], 'java' => self::javaFor((string) $v['id'])])->values()->all(),
            };
        });
    }

    /**
     * Installs $type $version on the server. The server is stopped first if it is running.
     *
     * @return array{type: string, version: string, build: string|null, image: string}
     *
     * @throws DisplayException
     */
    public function install(Server $server, string $type, string $version): array
    {
        $this->assertType($type);
        if (!$this->supports($server)) {
            throw new DisplayException(trans('server_software.errors.unsupported'));
        }

        $known = collect($this->versions($type))->firstWhere('id', $version);
        if (!$known) {
            throw new DisplayException(trans('server_software.errors.unknown_version'));
        }

        $jar = $this->jarFile($server);
        [$url, $build, $java] = $this->resolveDownload($type, $version, (int) $known['java']);

        $this->stopServer($server);

        try {
            if ($type === 'fabric') {
                // Fabric's launcher is tiny and served without a Content-Length, which Wings' remote
                // download needs; the panel fetches it and writes it instead.
                $response = Http::timeout(60)->withUserAgent('Recoded-Ptero')->get($url);
                if (!$response->successful() || strlen($response->body()) < 1000 || strlen($response->body()) > 20 * 1024 * 1024) {
                    throw new DisplayException(trans('server_software.errors.download'));
                }
                $this->files->setServer($server)->putContent($jar, $response->body());
            } else {
                $this->files->setServer($server)->getHttpClient()->post(sprintf('/api/servers/%s/files/pull', $server->uuid), [
                    'timeout' => 900,
                    'json' => ['url' => $url, 'root' => '/', 'file_name' => $jar, 'foreground' => true],
                ]);
            }
        } catch (DisplayException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            throw new DisplayException(trans('server_software.errors.download'));
        }

        $image = $this->imageFor($server, $java);
        $software = ['type' => $type, 'version' => $version, 'build' => $build, 'java' => $java, 'installed_at' => now()->toIso8601String()];
        Server::query()->whereKey($server->id)->update(['image' => $image, 'software' => json_encode($software)]);
        $this->setVariable($server, 'MINECRAFT_VERSION', $version);
        $this->setVariable($server, 'BUILD_NUMBER', 'latest');

        return $software + ['image' => $image];
    }

    /**
     * @return array{0: string, 1: string|null, 2: int} download URL, build, Java version
     *
     * @throws DisplayException
     */
    private function resolveDownload(string $type, string $version, int $java): array
    {
        switch ($type) {
            case 'paper':
            case 'folia':
            case 'velocity':
                $builds = $this->json(self::FILL . "$type/versions/" . rawurlencode($version) . '/builds');
                $build = collect($builds)->firstWhere('channel', 'STABLE') ?? ($builds[0] ?? null);
                $url = $build['downloads']['server:default']['url'] ?? null;
                if (!$url) {
                    throw new DisplayException(trans('server_software.errors.no_build'));
                }

                return [$url, (string) $build['id'], $java];
            case 'purpur':
                return ['https://api.purpurmc.org/v2/purpur/' . rawurlencode($version) . '/latest/download', 'latest', $java];
            case 'fabric':
                $loader = collect($this->json('https://meta.fabricmc.net/v2/versions/loader'))->firstWhere('stable', true)['version'] ?? null;
                $installer = collect($this->json('https://meta.fabricmc.net/v2/versions/installer'))->firstWhere('stable', true)['version'] ?? null;
                if (!$loader || !$installer) {
                    throw new DisplayException(trans('server_software.errors.no_build'));
                }

                return ['https://meta.fabricmc.net/v2/versions/loader/' . rawurlencode($version) . "/$loader/$installer/server/jar", $loader, $this->mojangJava($version) ?? $java];
            default:
                $entry = collect($this->json('https://piston-meta.mojang.com/mc/game/version_manifest_v2.json')['versions'] ?? [])->firstWhere('id', $version);
                $details = $entry ? $this->json($entry['url']) : [];
                $url = $details['downloads']['server']['url'] ?? null;
                if (!$url) {
                    throw new DisplayException(trans('server_software.errors.no_build'));
                }

                return [$url, null, (int) ($details['javaVersion']['majorVersion'] ?? $java)];
        }
    }

    private function fillVersions(string $project): array
    {
        return collect($this->json(self::FILL . $project . '/versions')['versions'] ?? [])
            ->map(fn ($v) => $v['version'] ?? [])
            // Release versions only for the game servers; Velocity only publishes snapshots.
            ->filter(fn ($v) => !empty($v['id']) && ($project === 'velocity' || preg_match('/^\d+(\.\d+)+$/', $v['id'])))
            ->map(fn ($v) => ['id' => (string) $v['id'], 'java' => (int) ($v['java']['version']['minimum'] ?? self::javaFor((string) $v['id']))])
            ->values()->all();
    }

    private function mojangJava(string $version): ?int
    {
        $entry = collect($this->json('https://piston-meta.mojang.com/mc/game/version_manifest_v2.json')['versions'] ?? [])->firstWhere('id', $version);
        $java = $entry ? ($this->json($entry['url'])['javaVersion']['majorVersion'] ?? null) : null;

        return $java ? (int) $java : null;
    }

    /**
     * Minimum Java for a Minecraft version when the source doesn't say.
     */
    public static function javaFor(string $version): int
    {
        if (preg_match('/^(\d+)\./', $version, $m) && (int) $m[1] >= 26) {
            return 25;
        }
        if (!preg_match('/^1\.(\d+)(?:\.(\d+))?/', $version, $m)) {
            return 21;
        }
        $minor = (int) $m[1];
        $patch = (int) ($m[2] ?? 0);

        return match (true) {
            $minor <= 16 => 8,
            $minor === 17 => 16,
            $minor < 20 || ($minor === 20 && $patch < 5) => 17,
            default => 21,
        };
    }

    /**
     * The egg's image for the lowest Java that is at least $java, else the matching yolks image.
     */
    private function imageFor(Server $server, int $java): string
    {
        $best = null;
        foreach ((array) ($server->egg?->docker_images ?? []) as $name => $image) {
            if (preg_match('/java[_ -]?(\d+)/i', $name . ' ' . $image, $m) && (int) $m[1] >= $java && (!$best || (int) $m[1] < $best[0])) {
                $best = [(int) $m[1], $image];
            }
        }
        if ($best) {
            return $best[1];
        }

        foreach (self::YOLKS_JAVA as $available) {
            if ($available >= $java) {
                return 'ghcr.io/pterodactyl/yolks:java_' . $available;
            }
        }

        return $server->image;
    }

    private function jarFile(Server $server): string
    {
        $variable = EggVariable::query()->where('egg_id', $server->egg_id)->where('env_variable', 'SERVER_JARFILE')->first();
        $value = $variable
            ? (ServerVariable::query()->where('server_id', $server->id)->where('variable_id', $variable->id)->value('variable_value') ?? $variable->default_value)
            : null;

        // Only a plain file name in the server's main directory.
        return is_string($value) && preg_match('/^[\w.-]+\.jar$/', $value) ? $value : 'server.jar';
    }

    private function setVariable(Server $server, string $env, string $value): void
    {
        $variable = EggVariable::query()->where('egg_id', $server->egg_id)->where('env_variable', $env)->first();
        if ($variable) {
            ServerVariable::query()->updateOrCreate(
                ['server_id' => $server->id, 'variable_id' => $variable->id],
                ['variable_value' => $value]
            );
        }
    }

    /**
     * @throws DisplayException
     */
    private function stopServer(Server $server): void
    {
        $state = fn () => $this->daemon->setServer($server)->getDetails()['state'] ?? 'offline';

        try {
            if ($state() === 'offline') {
                return;
            }
            $this->power->setServer($server)->send('stop');
            for ($i = 0; $i < 30; ++$i) {
                sleep(2);
                if ($state() === 'offline') {
                    return;
                }
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        throw new DisplayException(trans('server_software.errors.still_running'));
    }

    /**
     * @throws DisplayException
     */
    private function json(string $url): array
    {
        try {
            $response = Http::timeout(20)->withUserAgent('Recoded-Ptero')->acceptJson()->get($url);
        } catch (\Throwable) {
            throw new DisplayException(trans('server_software.errors.api'));
        }
        if (!$response->successful() || !is_array($response->json())) {
            throw new DisplayException(trans('server_software.errors.api'));
        }

        return $response->json();
    }

    /**
     * @throws DisplayException
     */
    private function assertType(string $type): void
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new DisplayException(trans('server_software.errors.unknown_type'));
        }
    }
}
