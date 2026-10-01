<?php

namespace Pterodactyl\Services\Nodes;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * The newest Wings build published as a "wings-v<version>" release of the Recoded Ptero
 * repository, which is what the "Update Wings" buttons install.
 */
class WingsReleaseService
{
    private const CACHE_KEY = 'mcpanel:wings:latest';

    public function latest(bool $force = false): string
    {
        $fallback = (string) config('mcpanel.wings_version');
        if (!$force && ($cached = Cache::get(self::CACHE_KEY))) {
            return $cached;
        }

        try {
            $request = Http::timeout(10)->withUserAgent('Recoded-Ptero')->acceptJson();
            if ($token = config('mcpanel.github_token')) {
                $request = $request->withToken($token);
            }
            $response = $request->get('https://api.github.com/repos/' . config('mcpanel.repository') . '/releases', ['per_page' => 50]);
            if (!$response->successful()) {
                return $fallback;
            }

            $latest = $fallback;
            foreach ($response->json() as $release) {
                if (!empty($release['draft']) || !empty($release['prerelease'])) {
                    continue;
                }
                if (preg_match('/^wings-v(\d+\.\d+\.\d+)$/', (string) ($release['tag_name'] ?? ''), $m) && version_compare($m[1], $latest, '>')) {
                    $latest = $m[1];
                }
            }

            Cache::put(self::CACHE_KEY, $latest, now()->addMinutes(30));

            return $latest;
        } catch (\Throwable) {
            return $fallback;
        }
    }

    /**
     * Whether a node reporting $version should be updated. Unknown versions (offline nodes,
     * development builds) never are.
     */
    public function isOutdated(?string $version): bool
    {
        $version = ltrim((string) $version, 'vV');
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            return false;
        }

        return version_compare($version, $this->latest(), '<');
    }

    /**
     * Whether the node runs a Recoded Ptero Wings that can update itself and report utilization.
     */
    public static function supportsSelfUpdate(?array $monitorState): bool
    {
        return (bool) ($monitorState['capable'] ?? false);
    }
}
