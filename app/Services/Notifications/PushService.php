<?php

namespace Pterodactyl\Services\Notifications;

use Pterodactyl\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Encryption\Encrypter;
use Pterodactyl\Contracts\Repository\SettingsRepositoryInterface;

/**
 * Browser push notifications for the installable app (Web Push, RFC 8030/8291/8292), done with
 * PHP's OpenSSL functions so no extra library is needed. The VAPID key pair that identifies this
 * panel to the browsers' push services is created on first use and stored in the settings.
 */
class PushService
{
    // DER prefix of an uncompressed P-256 public key (SubjectPublicKeyInfo).
    private const P256_SPKI_PREFIX = '3059301306072a8648ce3d020106082a8648ce3d030107034200';

    public function __construct(private SettingsRepositoryInterface $settings, private Encrypter $encrypter)
    {
    }

    /**
     * Push endpoints come from the user's browser, so only the browsers' push services are
     * accepted; otherwise the panel could be made to send requests to any host.
     */
    public static function allowedEndpoint(string $endpoint): bool
    {
        $parts = parse_url($endpoint);
        if (($parts['scheme'] ?? '') !== 'https' || empty($parts['host']) || isset($parts['user']) || isset($parts['port'])) {
            return false;
        }

        return (bool) preg_match(
            '/(^|\.)(fcm\.googleapis\.com|android\.googleapis\.com|push\.services\.mozilla\.com|notify\.windows\.com|push\.apple\.com)$/i',
            $parts['host']
        );
    }

    public static function supported(): bool
    {
        return function_exists('openssl_pkey_derive') && in_array('aes-128-gcm', openssl_get_cipher_methods(), true);
    }

    /**
     * The public key browsers need for subscribing (base64url, uncompressed point).
     */
    public function publicKey(): string
    {
        return $this->keys()['public'];
    }

    public function subscribe(User $user, string $endpoint, string $p256dh, string $auth): void
    {
        DB::table('push_subscriptions')->updateOrInsert(
            ['endpoint_hash' => hash('sha256', $endpoint)],
            [
                'user_id' => $user->id,
                'endpoint' => $endpoint,
                'public_key' => $p256dh,
                'auth_token' => $auth,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function unsubscribe(User $user, string $endpoint): void
    {
        DB::table('push_subscriptions')->where('user_id', $user->id)->where('endpoint_hash', hash('sha256', $endpoint))->delete();
    }

    /**
     * Sends a notification to every device of the given users. Never throws.
     *
     * @param iterable<int>|int $userIds
     */
    public function sendToUsers(iterable|int $userIds, string $title, string $body, ?string $url = null, ?string $tag = null): int
    {
        if (!self::supported()) {
            return 0;
        }

        $ids = is_int($userIds) ? [$userIds] : collect($userIds)->unique()->values()->all();
        if (empty($ids)) {
            return 0;
        }

        $payload = json_encode([
            'title' => mb_substr($title, 0, 120),
            'body' => mb_substr($body, 0, 300),
            'url' => $url ?: '/',
            'tag' => $tag,
        ]);

        $sent = 0;
        foreach (DB::table('push_subscriptions')->whereIn('user_id', $ids)->get() as $subscription) {
            try {
                $status = $this->deliver($subscription, $payload);
            } catch (\Throwable $exception) {
                report($exception);
                continue;
            }

            if ($status === 404 || $status === 410) {
                // The browser dropped this subscription.
                DB::table('push_subscriptions')->where('id', $subscription->id)->delete();
            } elseif ($status >= 200 && $status < 300) {
                ++$sent;
            }
        }

        return $sent;
    }

    /**
     * Admins and owners (node alerts and the like).
     */
    public function sendToTeam(string $title, string $body, ?string $url = null): int
    {
        try {
            $ids = User::query()->where('root_admin', true)->pluck('id');

            return $this->sendToUsers($ids, $title, $body, $url, 'team');
        } catch (\Throwable $exception) {
            report($exception);

            return 0;
        }
    }

    private function deliver(object $subscription, string $payload): int
    {
        $endpoint = (string) $subscription->endpoint;
        if (!self::allowedEndpoint($endpoint)) {
            return 410;
        }
        $parts = parse_url($endpoint);

        $body = $this->encrypt($payload, self::b64d($subscription->public_key), self::b64d($subscription->auth_token));
        $keys = $this->keys();
        $jwt = $this->vapidJwt('https://' . $parts['host'], $keys['private']);

        return Http::timeout(10)
            ->withoutRedirecting()
            ->withHeaders([
                'Authorization' => 'vapid t=' . $jwt . ', k=' . $keys['public'],
                'Content-Encoding' => 'aes128gcm',
                'Content-Type' => 'application/octet-stream',
                'TTL' => '86400',
                'Urgency' => 'normal',
            ])
            ->withBody($body, 'application/octet-stream')
            ->post($endpoint)
            ->status();
    }

    /**
     * RFC 8291 "aes128gcm" encryption of one record for the subscriber's key.
     */
    private function encrypt(string $payload, string $uaPublic, string $authSecret): string
    {
        if (strlen($uaPublic) !== 65 || strlen($authSecret) < 16) {
            throw new \InvalidArgumentException('Invalid push subscription keys.');
        }

        $local = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        $details = openssl_pkey_get_details($local);
        $asPublic = "\x04" . str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT) . str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);

        $peer = openssl_pkey_get_public(self::pemFromPoint($uaPublic));
        $shared = openssl_pkey_derive($peer, $local, 32);
        if ($shared === false) {
            throw new \RuntimeException('ECDH failed for push subscription.');
        }

        $ikm = hash_hkdf('sha256', $shared, 32, "WebPush: info\0" . $uaPublic . $asPublic, $authSecret);
        $salt = random_bytes(16);
        $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $salt);
        $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\0", $salt);

        $tag = '';
        $cipher = openssl_encrypt($payload . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);

        return $salt . pack('N', 4096) . chr(65) . $asPublic . $cipher . $tag;
    }

    private function vapidJwt(string $audience, string $privatePem): string
    {
        $header = self::b64e(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $claims = self::b64e(json_encode([
            'aud' => $audience,
            'exp' => time() + 12 * 3600,
            'sub' => 'mailto:' . (config('pterodactyl.service.author') ?: config('mail.from.address') ?: 'noreply@example.com'),
        ], JSON_UNESCAPED_SLASHES));

        openssl_sign($header . '.' . $claims, $der, $privatePem, OPENSSL_ALGO_SHA256);

        return $header . '.' . $claims . '.' . self::b64e(self::derToRaw($der));
    }

    /**
     * @return array{public: string, private: string}
     */
    private function keys(): array
    {
        $public = config('mcpanel.push.public_key');
        $private = config('mcpanel.push.private_key');
        if ($public && $private && str_contains($private, 'PRIVATE KEY')) {
            return ['public' => $public, 'private' => $private];
        }

        $key = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        openssl_pkey_export($key, $private);
        $details = openssl_pkey_get_details($key);
        $public = self::b64e("\x04" . str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT) . str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT));

        $this->settings->set('settings::mcpanel:push:public_key', $public);
        $this->settings->set('settings::mcpanel:push:private_key', $this->encrypter->encrypt($private));
        config(['mcpanel.push.public_key' => $public, 'mcpanel.push.private_key' => $private]);

        return ['public' => $public, 'private' => $private];
    }

    private static function pemFromPoint(string $point): string
    {
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode(hex2bin(self::P256_SPKI_PREFIX) . $point), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    /**
     * ECDSA signature: DER SEQUENCE{INTEGER r, INTEGER s} -> raw r||s (64 bytes) as JWS wants it.
     */
    private static function derToRaw(string $der): string
    {
        $offset = 2;
        if (ord($der[1]) & 0x80) {
            $offset += ord($der[1]) & 0x7F;
        }
        $out = '';
        for ($i = 0; $i < 2; ++$i) {
            $len = ord($der[$offset + 1]);
            $int = substr($der, $offset + 2, $len);
            $offset += 2 + $len;
            $out .= str_pad(ltrim($int, "\0"), 32, "\0", STR_PAD_LEFT);
        }

        return $out;
    }

    public static function b64e(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function b64d(string $data): string
    {
        return (string) base64_decode(strtr($data, '-_', '+/') . str_repeat('=', (4 - strlen($data) % 4) % 4));
    }
}
