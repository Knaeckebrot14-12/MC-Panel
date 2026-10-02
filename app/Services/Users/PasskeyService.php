<?php

namespace Pterodactyl\Services\Users;

use Carbon\CarbonImmutable;
use Pterodactyl\Models\User;
use lbuchs\WebAuthn\WebAuthn;
use Pterodactyl\Models\UserPasskey;
use Illuminate\Support\Facades\Cache;
use lbuchs\WebAuthn\Binary\ByteBuffer;
use Illuminate\Contracts\Session\Session;
use Pterodactyl\Exceptions\DisplayException;

/**
 * Passkeys (WebAuthn): registers credentials for a user and verifies sign-ins with them.
 *
 * The cryptography (CBOR/COSE parsing, attestation, signature checks) is done by lbuchs/webauthn.
 * What this class adds around it:
 *  - the relying party ID and the origin are derived from APP_URL only, never from the request
 *    (the library only does a loose suffix match on the host and ignores the port, so the origin
 *    is additionally compared exactly here),
 *  - user verification is always required (PIN, fingerprint, face) and credentials are always
 *    discoverable, which is what makes the username-less sign-in possible,
 *  - challenges live for five minutes and can be used exactly once,
 *  - a signature counter that does not increase is treated as a cloned authenticator.
 */
class PasskeyService
{
    private const CHALLENGE_TTL = 300;
    private const TRANSPORTS = ['usb', 'nfc', 'ble', 'hybrid', 'internal', 'smart-card', 'cable'];

    /**
     * The relying party ID: the host of APP_URL, without port.
     */
    public function rpId(): string
    {
        return mb_strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
    }

    /**
     * The only origin credentials may be created or used on, e.g. "https://panel.example.com".
     */
    public function origin(): string
    {
        $url = (string) config('app.url');
        $port = parse_url($url, PHP_URL_PORT);

        return mb_strtolower((string) parse_url($url, PHP_URL_SCHEME)) . '://' . $this->rpId() . ($port ? ':' . $port : '');
    }

    /**
     * Options for navigator.credentials.create() to add a passkey to the account.
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    public function registrationOptions(User $user): \stdClass
    {
        $existing = UserPasskey::query()->where('user_id', $user->id)->pluck('credential_id');
        if ($existing->count() >= UserPasskey::MAX_PER_USER) {
            throw new DisplayException(trans('passkeys.errors.limit', ['max' => UserPasskey::MAX_PER_USER]));
        }

        $exclude = $existing->map(fn (string $id) => new ByteBuffer(self::decode($id)))->all();

        // The user handle is the account UUID: stable, opaque (it does not reveal the numeric ID)
        // and it never changes when the username or e-mail does.
        $options = $this->server()->getCreateArgs(
            $user->uuid,
            $user->username,
            trim($user->name_first . ' ' . $user->name_last) ?: $user->username,
            60,
            true, // discoverable credential, needed to sign in without typing a username
            true, // user verification required
            null,
            $exclude,
        );

        Cache::put($this->registrationKey($user), self::encode($this->challengeOf($options)), self::CHALLENGE_TTL);

        return $options;
    }

    /**
     * Finishes the registration with the browser's answer to {@see registrationOptions()}.
     *
     * @param array{id?: mixed, rawId?: mixed, response?: array<string, mixed>} $credential
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    public function register(User $user, string $name, array $credential): UserPasskey
    {
        // Single use: taken out of the cache whether the verification below succeeds or not.
        $challenge = Cache::pull($this->registrationKey($user));
        if (!is_string($challenge)) {
            throw new DisplayException(trans('passkeys.errors.expired'));
        }

        if (UserPasskey::query()->where('user_id', $user->id)->count() >= UserPasskey::MAX_PER_USER) {
            throw new DisplayException(trans('passkeys.errors.limit', ['max' => UserPasskey::MAX_PER_USER]));
        }

        try {
            $response = $credential['response'] ?? [];
            $clientData = self::decode($response['clientDataJSON'] ?? null);
            $this->assertOrigin($clientData);

            $data = $this->server()->processCreate(
                $clientData,
                self::decode($response['attestationObject'] ?? null),
                self::decode($challenge),
                true, // user verification
                true, // user presence
                false,
                false,
            );

            $id = self::encode($data->credentialId);
        } catch (\Throwable $exception) {
            // Not passed on as "previous": failed attempts are normal (cancelled dialogs, probing) and
            // must not fill the error log with stack traces. The reason is only kept at debug level.
            logger()->debug('Passkey verification failed: ' . $exception->getMessage());

            throw new DisplayException(trans('passkeys.errors.verification_failed'));
        }

        if (strlen($id) > 512 || UserPasskey::query()->where('credential_id', $id)->exists()) {
            throw new DisplayException(trans('passkeys.errors.duplicate'));
        }

        $transports = array_values(array_intersect(
            is_array($response['transports'] ?? null) ? $response['transports'] : [],
            self::TRANSPORTS,
        ));

        $passkey = new UserPasskey([
            'name' => mb_substr(trim($name), 0, 64),
            'credential_id' => $id,
            'public_key' => $data->credentialPublicKey,
            'sign_count' => (int) ($data->signatureCounter ?? 0),
            'transports' => $transports ?: null,
            'created_at' => CarbonImmutable::now(),
        ]);
        $passkey->user_id = $user->id;
        $passkey->save();

        return $passkey;
    }

    /**
     * Options for navigator.credentials.get() without a username: the authenticator offers the
     * passkeys it holds for this site (allowCredentials stays empty).
     */
    public function loginOptions(Session $session): \stdClass
    {
        $options = $this->server()->getGetArgs([], 60, true, true, true, true, true, true);

        $session->put('passkey_login', [
            'challenge' => self::encode($this->challengeOf($options)),
            'expires_at' => CarbonImmutable::now()->addSeconds(self::CHALLENGE_TTL)->getTimestamp(),
        ]);

        return $options;
    }

    /**
     * Verifies a sign-in and returns the passkey that was used. Every failure, no matter if the
     * credential is unknown, the signature is wrong or the challenge was used before, is the same
     * exception: callers must not tell the cases apart to the outside.
     *
     * @param array{id?: mixed, rawId?: mixed, response?: array<string, mixed>} $credential
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    public function authenticate(Session $session, array $credential): UserPasskey
    {
        // Single use: removed from the session whether the verification below succeeds or not.
        $stored = $session->pull('passkey_login');
        if (!is_array($stored) || ($stored['expires_at'] ?? 0) < time() || !is_string($stored['challenge'] ?? null)) {
            throw new DisplayException(trans('passkeys.errors.expired'));
        }

        try {
            $id = self::encode(self::decode($credential['rawId'] ?? null));
            if (!hash_equals($id, (string) ($credential['id'] ?? ''))) {
                throw new \InvalidArgumentException('id mismatch');
            }

            /** @var UserPasskey|null $passkey */
            $passkey = UserPasskey::query()->with('user')->where('credential_id', $id)->first();
            if (!$passkey || !$passkey->user || !hash_equals($passkey->credential_id, $id)) {
                throw new \InvalidArgumentException('unknown credential');
            }

            $response = $credential['response'] ?? [];

            // The credential has to belong to the user the authenticator says it belongs to.
            $handle = $response['userHandle'] ?? null;
            if ($handle !== null && $handle !== '' && !hash_equals($passkey->user->uuid, self::decode($handle))) {
                throw new \InvalidArgumentException('user handle mismatch');
            }

            $clientData = self::decode($response['clientDataJSON'] ?? null);
            $this->assertOrigin($clientData);

            $server = $this->server();
            $server->processGet(
                $clientData,
                self::decode($response['authenticatorData'] ?? null),
                self::decode($response['signature'] ?? null),
                $passkey->public_key,
                self::decode($stored['challenge']),
                $passkey->sign_count, // a counter that does not grow means a cloned authenticator
                true, // user verification: this is what lets a passkey replace password AND second factor
                true,
            );

            $newCount = (int) ($server->getSignatureCounter() ?? 0);
            $update = ['last_used_at' => CarbonImmutable::now()];
            if ($newCount > 0) {
                $update['sign_count'] = $newCount;
            }

            // Compare-and-set, so two parallel requests with the same assertion can't both pass.
            $changed = UserPasskey::query()->whereKey($passkey->id)
                ->when($newCount > 0, fn ($query) => $query->where('sign_count', '<', $newCount))
                ->update($update);
            if ($changed !== 1) {
                throw new \RuntimeException('counter race');
            }
        } catch (\Throwable $exception) {
            // Not passed on as "previous": failed attempts are normal (cancelled dialogs, probing) and
            // must not fill the error log with stack traces. The reason is only kept at debug level.
            logger()->debug('Passkey verification failed: ' . $exception->getMessage());

            throw new DisplayException(trans('passkeys.errors.verification_failed'));
        }

        return $passkey;
    }

    /**
     * WebAuthn server for this panel. Attestation is requested as "none": the panel only needs the
     * key, not a statement about the authenticator model, and "none" is the privacy-friendly choice.
     */
    private function server(): WebAuthn
    {
        return new WebAuthn((string) config('app.name', 'Panel'), $this->rpId(), ['none'], true);
    }

    /**
     * clientDataJSON.origin has to be exactly this panel's origin.
     *
     * @throws \InvalidArgumentException
     */
    private function assertOrigin(string $clientDataJson): void
    {
        $data = json_decode($clientDataJson, true);
        if (!is_array($data) || !is_string($data['origin'] ?? null) || !hash_equals($this->origin(), mb_strtolower($data['origin']))) {
            throw new \InvalidArgumentException('invalid origin');
        }

        // Ceremonies started from a cross-origin iframe are not accepted.
        if (($data['crossOrigin'] ?? false) === true) {
            throw new \InvalidArgumentException('cross origin');
        }
    }

    private function challengeOf(\stdClass $options): string
    {
        return $options->publicKey->challenge->getBinaryString();
    }

    private function registrationKey(User $user): string
    {
        return 'passkey:registration:' . $user->id;
    }

    /**
     * base64url without padding, as WebAuthn uses it.
     */
    public static function encode(string $binary): string
    {
        return rtrim(strtr(base64_encode($binary), '+/', '-_'), '=');
    }

    /**
     * Strict base64url decoding; anything else than the base64url alphabet is refused.
     *
     * @throws \InvalidArgumentException
     */
    public static function decode(mixed $value): string
    {
        if (!is_string($value) || $value === '' || strlen($value) > 8192 || !preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            throw new \InvalidArgumentException('invalid base64url');
        }

        $binary = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4), true);
        if ($binary === false) {
            throw new \InvalidArgumentException('invalid base64url');
        }

        return $binary;
    }
}
