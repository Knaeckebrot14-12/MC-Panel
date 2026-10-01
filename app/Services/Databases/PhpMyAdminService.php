<?php

namespace Pterodactyl\Services\Databases;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Pterodactyl\Models\User;
use Pterodactyl\Models\Database;
use Pterodactyl\Models\DatabaseHost;
use Illuminate\Encryption\Encrypter;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Encryption\DecryptException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * phpMyAdmin at <panel>/phpmyadmin/ (installed into the image by the Dockerfile, configured by
 * .github/docker/phpmyadmin). It has no login form of its own: the panel checks permissions and
 * issues a one-time ticket, and /phpmyadmin/signon.php redeems that ticket into phpMyAdmin's
 * signon session. Host, port and account come only from the ticket, never from the browser.
 */
class PhpMyAdminService
{
    public const PATH = '/phpmyadmin/';

    /** Where php-fpm keeps the phpMyAdmin sessions (see .github/docker/phpmyadmin/config.inc.php). */
    public const SESSION_DIR = '/tmp/phpmyadmin/sessions';

    /** Seconds a ticket can be redeemed; the browser opens it right away. */
    private const TICKET_TTL = 60;

    private const CACHE_PREFIX = 'phpmyadmin:ticket:';

    public function __construct(private CacheRepository $cache, private Encrypter $encrypter)
    {
    }

    /**
     * Turned on and off by the owner under Admin -> Settings -> Advanced.
     */
    public static function enabled(): bool
    {
        return filter_var(config('mcpanel.phpmyadmin.enabled', true), FILTER_VALIDATE_BOOLEAN);
    }

    public static function url(): string
    {
        return rtrim((string) config('app.url'), '/') . self::PATH;
    }

    /**
     * Opening phpMyAdmin with a host's admin account is for admins with the "databases"
     * permission only (the owner always has it).
     */
    public static function adminMayOpen(?User $user): bool
    {
        return self::enabled() && $user !== null && $user->root_admin && $user->hasStaffPermission('databases');
    }

    /**
     * A launch URL that signs in with the host's own account (it can create and drop databases).
     */
    public function launchForHost(DatabaseHost $host, Request $request): string
    {
        return $this->issue($request, 'host', $host->host, (int) $host->port, $host->username, $host->password);
    }

    /**
     * A launch URL that signs in as the database's own user, which MySQL limits to that database.
     */
    public function launchForDatabase(Database $database, Request $request): string
    {
        $host = $database->host()->firstOrFail();

        return $this->issue($request, 'database', $host->host, (int) $host->port, $database->username, $database->password);
    }

    /**
     * Redeems a ticket exactly once. It must be redeemed in the browser session that asked for it.
     * Returns the decrypted connection details, or null for an unknown, used or expired ticket.
     *
     * @return array{host: string, port: int, user: string, password: string}|null
     */
    public function redeem(string $ticket, ?string $sessionCookie): ?array
    {
        if (!self::enabled() || !preg_match('/^[A-Za-z0-9]{64}$/', $ticket)) {
            return null;
        }

        $key = self::CACHE_PREFIX . hash('sha256', $ticket);
        $data = $this->cache->get($key);
        // On Redis only the first request that deletes the key gets true: a ticket opened twice at
        // the same moment still works only once.
        if (!is_array($data) || !$this->cache->forget($key)) {
            return null;
        }

        $sessionId = $this->panelSessionId($sessionCookie);
        if ($sessionId === null || !hash_equals((string) ($data['session'] ?? ''), hash('sha256', $sessionId))) {
            return null;
        }

        try {
            $password = $this->encrypter->decrypt($data['password']);
        } catch (DecryptException) {
            return null;
        }

        return [
            'host' => (string) $data['host'],
            'port' => (int) $data['port'],
            'user' => (string) $data['user'],
            'password' => (string) $password,
        ];
    }

    /**
     * Signs everyone out of phpMyAdmin (used when the owner turns it off).
     */
    public function endAllSessions(): void
    {
        foreach (glob(self::SESSION_DIR . '/sess_*') ?: [] as $file) {
            @unlink($file);
        }
    }

    /**
     * @param string $encryptedPassword the password as stored in the database (encrypted with APP_KEY)
     */
    private function issue(Request $request, string $kind, string $host, int $port, string $username, string $encryptedPassword): string
    {
        // The ticket is bound to the panel session, so only a browser can use it.
        if (!$request->hasSession()) {
            throw new BadRequestHttpException('phpMyAdmin can only be opened from the panel in a browser.');
        }

        $ticket = Str::random(64);

        // Only the encrypted password is cached; signon.php decrypts it.
        $this->cache->put(self::CACHE_PREFIX . hash('sha256', $ticket), [
            'kind' => $kind,
            'host' => $host,
            'port' => $port,
            'user' => $username,
            'password' => $encryptedPassword,
            'issuer' => optional($request->user())->id,
            'session' => hash('sha256', $request->session()->getId()),
        ], self::TICKET_TTL);

        return url(self::PATH . 'signon.php') . '?ticket=' . $ticket;
    }

    /**
     * The panel's session id from its (encrypted) session cookie, as Laravel's EncryptCookies reads it.
     */
    private function panelSessionId(?string $cookie): ?string
    {
        if (!is_string($cookie) || $cookie === '') {
            return null;
        }

        try {
            $value = $this->encrypter->decrypt($cookie, false);
        } catch (DecryptException) {
            return null;
        }

        $id = is_string($value)
            ? CookieValuePrefix::validate((string) config('session.cookie'), $value, $this->encrypter->getAllKeys())
            : null;

        return is_string($id) && $id !== '' ? $id : null;
    }
}
