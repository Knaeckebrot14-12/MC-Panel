<?php

namespace Pterodactyl\Services\Minecraft;

use Pterodactyl\Models\Server;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;
use Pterodactyl\Repositories\Wings\DaemonPowerRepository;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;

/**
 * Bedrock players (phones, consoles, Windows 10/11) on a Java server: installs Geyser and Floodgate
 * from GeyserMC's download API (checked against the published SHA-256) and makes Geyser listen on
 * the server's own port over UDP ("clone-remote-port"), so no extra port or allocation is needed.
 * Geyser picks Floodgate authentication by itself when Floodgate is installed.
 */
class GeyserService
{
    private const API = 'https://download.geysermc.org/v2/projects/%s/versions/latest/builds/latest';

    /** Platforms Geyser and Floodgate support, with their jar names and Geyser's data folder. */
    private const PLATFORMS = [
        'spigot' => ['geyser' => 'Geyser-Spigot.jar', 'floodgate' => 'floodgate-spigot.jar', 'config' => 'plugins/Geyser-Spigot'],
        'velocity' => ['geyser' => 'Geyser-Velocity.jar', 'floodgate' => 'floodgate-velocity.jar', 'config' => 'plugins/geyser'],
    ];

    /** Software of the version changer that runs Bukkit plugins and works with Geyser and Floodgate. */
    private const SPIGOT_TYPES = ['paper', 'purpur', 'spigot', 'bukkit'];

    public function __construct(
        private DaemonFileRepository $files,
        private DaemonPowerRepository $power,
        private DaemonServerRepository $daemon,
    ) {
    }

    /**
     * "spigot", "velocity" or null when the server's software can't run Geyser as a plugin.
     */
    public function platform(Server $server): ?string
    {
        $software = json_decode((string) $server->software, true);
        $type = is_array($software) ? (string) ($software['type'] ?? '') : '';
        if ($type !== '') {
            return in_array($type, self::SPIGOT_TYPES, true) ? 'spigot' : ($type === 'velocity' ? 'velocity' : null);
        }

        // Not installed through the version changer: go by the egg.
        $egg = strtolower((string) $server->egg?->name . ' ' . $server->nest?->name);
        if (str_contains($egg, 'velocity')) {
            return 'velocity';
        }
        if (preg_match('/folia|fabric|forge|quilt|sponge|vanilla|bungee|waterfall/', $egg)) {
            return null;
        }

        return preg_match('/paper|purpur|spigot|bukkit/', $egg) ? 'spigot' : null;
    }

    /**
     * @return array{supported: bool, installed: bool, configured: bool, port: int|null, address: string|null}
     */
    public function status(Server $server): array
    {
        $platform = $this->platform($server);
        $port = $server->allocation?->port;
        $subdomain = DB::table('server_subdomains')->where('server_id', $server->id)->first();
        // What players type: the subdomain, else the allocation's alias or IP, else the node's address.
        $ip = $server->allocation?->ip;
        $address = $subdomain ? $subdomain->name . '.' . $subdomain->domain
            : ($server->allocation?->ip_alias ?: ($ip && $ip !== '0.0.0.0' ? $ip : $server->node?->fqdn));

        if (!$platform) {
            return ['supported' => false, 'installed' => false, 'configured' => false, 'port' => $port, 'address' => $address];
        }

        $names = $this->pluginNames($server);
        $installed = in_array(strtolower(self::PLATFORMS[$platform]['geyser']), $names, true)
            || count(array_filter($names, fn ($n) => str_starts_with($n, 'geyser'))) > 0;

        $configured = false;
        if ($installed) {
            try {
                $config = $this->files->setServer($server)->getContent(self::PLATFORMS[$platform]['config'] . '/config.yml', 512 * 1024);
                $configured = (bool) preg_match('/^\s*clone-remote-port:\s*true\b/m', $config);
            } catch (\Throwable) {
                $configured = false;
            }
        }

        return [
            'supported' => true,
            'installed' => $installed,
            'configured' => $configured,
            'port' => $port,
            'address' => $address ?: null,
        ];
    }

    /**
     * Installs (or updates) Geyser and Floodgate, sets the Bedrock port to the server's port and
     * restarts the server when it was running.
     *
     * @throws DisplayException
     */
    /**
     * @return string|null the Minecraft version ViaVersion was added for (Geyser only speaks the newest one)
     *
     * @throws DisplayException
     */
    public function install(Server $server): ?string
    {
        $platform = $this->platform($server);
        if (!$platform) {
            throw new DisplayException(trans('server_plugins.geyser.errors.unsupported'));
        }
        // Geyser needs Java 17 or newer.
        if (preg_match('/java[_ -]?(\d+)/i', (string) $server->image, $m) && (int) $m[1] < 17) {
            throw new DisplayException(trans('server_plugins.geyser.errors.java'));
        }
        $jars = self::PLATFORMS[$platform];

        // Download and check everything before touching the server, so a failed download changes nothing.
        $geyser = $this->download('geyser', $platform);
        $floodgate = $this->download('floodgate', $platform);
        // Geyser translates for the newest Java version only; older servers need ViaVersion in between.
        $viaFor = $platform === 'spigot' ? $this->olderThanLatest($server) : null;
        $via = $viaFor ? $this->downloadViaVersion() : null;

        $files = $this->files->setServer($server);
        try {
            // Older copies under other names would load twice.
            $keep = [$jars['geyser'], $jars['floodgate'], 'ViaVersion.jar'];
            $pattern = $via ? '/^(geyser|floodgate|viaversion).*\.jar$/i' : '/^(geyser|floodgate).*\.jar$/i';
            $old = array_values(array_filter($this->pluginNames($server, true), fn ($name) => preg_match($pattern, $name)
                && !in_array($name, $keep, true)));
            if ($old) {
                $files->deleteFiles('/plugins', $old);
            }
            $files->putContent('plugins/' . $jars['geyser'], $geyser);
            $files->putContent('plugins/' . $jars['floodgate'], $floodgate);
            if ($via) {
                $files->putContent('plugins/ViaVersion.jar', $via);
            }
            $this->configurePort($server, $jars['config'] . '/config.yml');
        } catch (DisplayException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            throw new DisplayException(trans('server_plugins.geyser.errors.write'));
        }

        $this->restartIfRunning($server);

        return $via ? $viaFor : null;
    }

    /**
     * The server's Minecraft version when it is older than the newest release, else null. A version
     * that can't be told ("latest", unknown) counts as the newest.
     */
    private function olderThanLatest(Server $server): ?string
    {
        $software = json_decode((string) $server->software, true);
        $version = is_array($software) ? (string) ($software['version'] ?? '') : '';
        if ($version === '') {
            $variable = $server->variables->firstWhere('env_variable', 'MINECRAFT_VERSION');
            $version = (string) ($variable?->server_value ?? $variable?->default_value ?? '');
        }
        if ($version === '' || strtolower($version) === 'latest') {
            return null;
        }

        try {
            $latest = (string) Http::timeout(15)->withUserAgent('Recoded-Ptero')->acceptJson()
                ->get('https://piston-meta.mojang.com/mc/game/version_manifest_v2.json')->json('latest.release');
        } catch (\Throwable) {
            $latest = '';
        }

        // Without an answer ViaVersion is added anyway; it does no harm on the newest version either.
        return $latest !== '' && $latest === $version ? null : $version;
    }

    /**
     * ViaVersion's newest release for Paper/Spigot from Modrinth, checked against its SHA-512.
     *
     * @throws DisplayException
     */
    private function downloadViaVersion(): string
    {
        try {
            $versions = Http::timeout(20)->withUserAgent('Recoded-Ptero')->acceptJson()
                ->get('https://api.modrinth.com/v2/project/viaversion/version', ['loaders' => '["paper"]'])->json();
            $release = collect(is_array($versions) ? $versions : [])->firstWhere('version_type', 'release');
            $file = collect($release['files'] ?? [])->firstWhere('primary', true) ?? ($release['files'][0] ?? null);
            if (!$file || empty($file['hashes']['sha512']) || !str_starts_with((string) $file['url'], 'https://cdn.modrinth.com/')) {
                throw new \RuntimeException('No ViaVersion release found.');
            }
            $jar = Http::timeout(120)->withUserAgent('Recoded-Ptero')->get($file['url'])->body();
        } catch (\Throwable $exception) {
            report($exception);
            throw new DisplayException(trans('server_plugins.geyser.errors.download'));
        }
        if (strlen($jar) < 10000 || !hash_equals(strtolower($file['hashes']['sha512']), hash('sha512', $jar))) {
            throw new DisplayException(trans('server_plugins.geyser.errors.download'));
        }

        return $jar;
    }

    /**
     * Removes the Geyser and Floodgate jars (their settings folders stay).
     *
     * @throws DisplayException
     */
    public function uninstall(Server $server): void
    {
        $jars = array_values(array_filter($this->pluginNames($server, true), fn ($name) => preg_match('/^(geyser|floodgate).*\.jar$/i', $name)));
        if ($jars) {
            try {
                $this->files->setServer($server)->deleteFiles('/plugins', $jars);
            } catch (\Throwable $exception) {
                report($exception);
                throw new DisplayException(trans('server_plugins.geyser.errors.write'));
            }
        }
        $this->restartIfRunning($server);
    }

    /**
     * Bedrock on the Java port: switches "clone-remote-port" on in an existing config, or writes a
     * small one that Geyser completes with its defaults on the first start.
     */
    private function configurePort(Server $server, string $path): void
    {
        $files = $this->files->setServer($server);
        try {
            $config = $files->getContent($path, 512 * 1024);
        } catch (\Throwable) {
            $config = null;
        }

        if ($config === null || trim($config) === '') {
            $files->putContent($path, "# Written by Recoded Ptero: Bedrock players use the server's own port (UDP).\n"
                . "# Geyser adds all other settings with their defaults when it starts.\nbedrock:\n  clone-remote-port: true\n");

            return;
        }

        if (preg_match('/^(\s*clone-remote-port:\s*)\S+/m', $config)) {
            $config = preg_replace('/^(\s*clone-remote-port:\s*)\S+/m', '${1}true', $config, 1);
        } elseif (preg_match('/^bedrock:\s*$/m', $config)) {
            $config = preg_replace('/^bedrock:\s*$/m', "bedrock:\n  clone-remote-port: true", $config, 1);
        } else {
            $config = rtrim($config) . "\nbedrock:\n  clone-remote-port: true\n";
        }
        $files->putContent($path, $config);
    }

    /**
     * @throws DisplayException
     */
    private function download(string $project, string $platform): string
    {
        try {
            $build = Http::timeout(20)->withUserAgent('Recoded-Ptero')->acceptJson()->get(sprintf(self::API, $project))->json();
            $file = $build['downloads'][$platform] ?? null;
            if (!is_array($file) || empty($file['sha256'])) {
                throw new \RuntimeException("No $platform download for $project.");
            }
            $jar = Http::timeout(120)->withUserAgent('Recoded-Ptero')
                ->get(sprintf(self::API, $project) . '/downloads/' . $platform)->body();
        } catch (\Throwable $exception) {
            report($exception);
            throw new DisplayException(trans('server_plugins.geyser.errors.download'));
        }

        // Only exactly the published file is installed.
        if (strlen($jar) < 10000 || !hash_equals(strtolower($file['sha256']), hash('sha256', $jar))) {
            throw new DisplayException(trans('server_plugins.geyser.errors.download'));
        }

        return $jar;
    }

    /**
     * File names in /plugins (lower case unless $original).
     */
    private function pluginNames(Server $server, bool $original = false): array
    {
        try {
            $list = $this->files->setServer($server)->getDirectory('/plugins');
        } catch (\Throwable) {
            return [];
        }

        return collect($list)->filter(fn ($entry) => !empty($entry['file']))
            ->map(fn ($entry) => $original ? (string) $entry['name'] : strtolower((string) $entry['name']))
            ->values()->all();
    }

    private function restartIfRunning(Server $server): void
    {
        try {
            if (($this->daemon->setServer($server)->getDetails()['state'] ?? 'offline') !== 'offline') {
                $this->power->setServer($server)->send('restart');
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
