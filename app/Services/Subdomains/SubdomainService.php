<?php

namespace Pterodactyl\Services\Subdomains;

use Pterodactyl\Models\Server;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Pterodactyl\Exceptions\DisplayException;
use Illuminate\Http\Client\PendingRequest;

/**
 * Subdomains like "myserver.play.example.com" for servers. The panel creates an A record for the
 * name (pointing at the node's public IP) and, for Minecraft, an SRV record carrying the port, so
 * players can join with just the name. DNS is managed through the Cloudflare API.
 */
class SubdomainService
{
    private const API = 'https://api.cloudflare.com/client/v4';

    /** Names nobody may take, so they can't shadow the panel's own hosts. */
    private const RESERVED = ['www', 'mail', 'smtp', 'imap', 'pop', 'ftp', 'panel', 'admin', 'api', 'node', 'wings', 'ns1', 'ns2',
        'dns', 'status', 'cdn', 'webmail', 'cpanel', 'autodiscover', 'autoconfig', 'localhost', 'dashboard', 'billing', 'support'];

    public function enabled(): bool
    {
        return filter_var(config('mcpanel.subdomains.enabled'), FILTER_VALIDATE_BOOLEAN)
            && !empty(config('mcpanel.subdomains.cloudflare_token'))
            && !empty($this->domains());
    }

    /**
     * @return string[]
     */
    public function domains(): array
    {
        return collect(explode(',', (string) config('mcpanel.subdomains.domains')))
            ->map(fn ($d) => strtolower(trim($d)))
            ->filter(fn ($d) => self::validHostname($d))
            ->unique()->values()->all();
    }

    public static function validHostname(string $host): bool
    {
        return strlen($host) <= 190 && (bool) preg_match('/^(?=.{1,190}$)([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/', $host);
    }

    public function current(Server $server): ?object
    {
        return DB::table('server_subdomains')->where('server_id', $server->id)->first();
    }

    /**
     * Gives the server name.domain, replacing its previous subdomain.
     *
     * @throws DisplayException
     */
    public function assign(Server $server, string $name, string $domain): object
    {
        if (!$this->enabled()) {
            throw new DisplayException(trans('server_subdomain.errors.disabled'));
        }
        if (!$this->isMinecraft($server)) {
            throw new DisplayException(trans('server_subdomain.errors.not_minecraft'));
        }

        $name = strtolower(trim($name));
        $domain = strtolower(trim($domain));
        if (!preg_match('/^[a-z0-9]([a-z0-9-]{1,30}[a-z0-9])$/', $name) || in_array($name, self::RESERVED, true)) {
            throw new DisplayException(trans('server_subdomain.errors.invalid_name'));
        }
        if (!in_array($domain, $this->domains(), true)) {
            throw new DisplayException(trans('server_subdomain.errors.invalid_domain'));
        }

        $taken = DB::table('server_subdomains')->where('name', $name)->where('domain', $domain)->where('server_id', '!=', $server->id)->exists();
        if ($taken) {
            throw new DisplayException(trans('server_subdomain.errors.taken'));
        }

        $allocation = $server->allocation;
        $ip = $this->publicIp($server);
        if (!$allocation || !$ip) {
            throw new DisplayException(trans('server_subdomain.errors.no_ip'));
        }

        $fqdn = "$name.$domain";
        $zone = $this->zoneId($domain);
        $previous = $this->current($server);

        // Records that already exist under this name and weren't made by the panel belong to someone else.
        $ours = $previous && $previous->name === $name && $previous->domain === $domain ? array_values((array) json_decode($previous->records, true)) : [];
        foreach ([$fqdn, "_minecraft._tcp.$fqdn"] as $recordName) {
            foreach ($this->cf()->get(self::API . "/zones/$zone/dns_records", ['name' => $recordName])->json('result') ?? [] as $existing) {
                if (!in_array($existing['id'], $ours, true)) {
                    throw new DisplayException(trans('server_subdomain.errors.taken'));
                }
            }
        }

        if ($previous) {
            $this->deleteRecords($previous);
        }

        $comment = 'Recoded Ptero: server ' . $server->uuidShort;
        $records = [];
        $a = $this->cf()->post(self::API . "/zones/$zone/dns_records", [
            'type' => 'A', 'name' => $fqdn, 'content' => $ip, 'ttl' => 1, 'proxied' => false, 'comment' => $comment,
        ]);
        if (!$a->successful()) {
            throw new DisplayException(trans('server_subdomain.errors.cloudflare', ['error' => $a->json('errors.0.message') ?? $a->status()]));
        }
        $records['a'] = $a->json('result.id');

        if ($this->isMinecraft($server)) {
            $srv = $this->cf()->post(self::API . "/zones/$zone/dns_records", [
                'type' => 'SRV', 'name' => "_minecraft._tcp.$fqdn", 'ttl' => 1, 'comment' => $comment,
                'data' => ['priority' => 0, 'weight' => 5, 'port' => (int) $allocation->port, 'target' => $fqdn],
            ]);
            if ($srv->successful()) {
                $records['srv'] = $srv->json('result.id');
            }
        }

        DB::table('server_subdomains')->updateOrInsert(
            ['server_id' => $server->id],
            ['name' => $name, 'domain' => $domain, 'records' => json_encode($records + ['zone' => $zone]), 'updated_at' => now(), 'created_at' => now()]
        );

        return $this->current($server);
    }

    public function remove(Server $server): void
    {
        if ($current = $this->current($server)) {
            $this->deleteRecords($current);
            DB::table('server_subdomains')->where('id', $current->id)->delete();
        }
    }

    private function deleteRecords(object $subdomain): void
    {
        $records = (array) json_decode($subdomain->records, true);
        $zone = $records['zone'] ?? null;
        if (!$zone || empty(config('mcpanel.subdomains.cloudflare_token'))) {
            return;
        }
        foreach (['a', 'srv'] as $key) {
            if (!empty($records[$key])) {
                try {
                    $this->cf()->delete(self::API . "/zones/$zone/dns_records/" . $records[$key]);
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
        }
    }

    /**
     * The address players connect to: the allocation's alias or IP when public, else the node's address.
     */
    private function publicIp(Server $server): ?string
    {
        $allocation = $server->allocation;
        foreach ([$allocation?->ip_alias, $allocation?->ip, $server->node?->fqdn] as $candidate) {
            if (!$candidate) {
                continue;
            }
            $ip = filter_var($candidate, FILTER_VALIDATE_IP) ? $candidate : gethostbyname($candidate);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }

        return null;
    }

    /**
     * Subdomains are only offered for Minecraft servers (they get the SRV record with the port).
     */
    public function isMinecraft(Server $server): bool
    {
        // Same check as the client area's Minecraft-only tabs, plus the stock eggs' markers.
        return $server->variables->contains(fn ($variable) => $variable->env_variable === 'MINECRAFT_VERSION')
            || str_contains((string) $server->startup, '{{SERVER_JARFILE}}') || str_contains(strtolower((string) $server->egg?->name), 'minecraft')
            || str_contains(strtolower((string) $server->nest?->name), 'minecraft');
    }

    /**
     * @throws DisplayException
     */
    private function zoneId(string $domain): string
    {
        return Cache::remember('mcpanel:cf-zone:' . $domain, now()->addDay(), function () use ($domain) {
            // The zone is the domain itself or one of its parents (play.example.com -> example.com).
            $parts = explode('.', $domain);
            while (count($parts) >= 2) {
                $response = $this->cf()->get(self::API . '/zones', ['name' => implode('.', $parts)]);
                if ($id = $response->json('result.0.id')) {
                    return $id;
                }
                array_shift($parts);
            }

            throw new DisplayException(trans('server_subdomain.errors.no_zone', ['domain' => $domain]));
        });
    }

    private function cf(): PendingRequest
    {
        return Http::timeout(15)->withToken((string) config('mcpanel.subdomains.cloudflare_token'))->acceptJson();
    }

    /**
     * Asks Cloudflare whether the token is valid and active (Settings page; also before saving a new one).
     * Account-owned tokens can't use the user endpoint, so a token that can list zones counts as working too.
     */
    public function verifyToken(?string $token = null): bool
    {
        $token ??= (string) config('mcpanel.subdomains.cloudflare_token');
        if ($token === '') {
            return false;
        }
        $client = fn () => Http::timeout(15)->withToken($token)->acceptJson();

        try {
            $response = $client()->get(self::API . '/user/tokens/verify');
            if ($response->successful() && $response->json('result.status') === 'active') {
                return true;
            }

            return $client()->get(self::API . '/zones', ['per_page' => 1])->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Checks that the token can read the zones of all configured domains (Settings page).
     *
     * @return array<string, bool>
     */
    public function verify(): array
    {
        $out = [];
        foreach ($this->domains() as $domain) {
            try {
                Cache::forget('mcpanel:cf-zone:' . $domain);
                $this->zoneId($domain);
                $out[$domain] = true;
            } catch (\Throwable) {
                $out[$domain] = false;
            }
        }

        return $out;
    }
}
