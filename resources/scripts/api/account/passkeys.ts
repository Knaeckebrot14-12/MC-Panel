import http from '@/api/http';

export interface Passkey {
    id: number;
    name: string;
    createdAt: Date | null;
    lastUsedAt: Date | null;
}

/**
 * WebAuthn moves binary data around as base64url, JSON can't carry ArrayBuffers.
 */
export const decodeBase64Url = (value: string): ArrayBuffer => {
    const base64 = value.replace(/-/g, '+').replace(/_/g, '/');
    const binary = atob(base64 + '='.repeat((4 - (base64.length % 4)) % 4));
    const bytes = new Uint8Array(binary.length);
    for (let i = 0; i < binary.length; i++) {
        bytes[i] = binary.charCodeAt(i);
    }

    return bytes.buffer;
};

export const encodeBase64Url = (buffer: ArrayBuffer | null | undefined): string => {
    if (!buffer) return '';

    let binary = '';
    new Uint8Array(buffer).forEach((byte) => (binary += String.fromCharCode(byte)));

    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
};

/**
 * Whether this browser can do passkeys at all (it also needs a secure context: https or localhost).
 */
export const passkeysSupported = (): boolean =>
    typeof window !== 'undefined' &&
    window.isSecureContext &&
    typeof window.PublicKeyCredential !== 'undefined' &&
    !!navigator.credentials;

/**
 * Maps what the browser throws during a ceremony to a translation key of the "passkeys" namespace,
 * or null if it is something else (e.g. an HTTP error).
 */
export const passkeyErrorKey = (error: unknown): string | null => {
    if (error instanceof DOMException) {
        if (error.name === 'InvalidStateError') return 'already_registered';
        if (error.name === 'NotAllowedError' || error.name === 'AbortError') return 'cancelled';

        return 'failed';
    }

    return null;
};

const toCreationOptions = (options: any): PublicKeyCredentialCreationOptions => ({
    rp: options.rp,
    user: { ...options.user, id: decodeBase64Url(options.user.id) },
    challenge: decodeBase64Url(options.challenge),
    pubKeyCredParams: options.pubKeyCredParams,
    timeout: options.timeout,
    attestation: options.attestation,
    authenticatorSelection: options.authenticatorSelection,
    excludeCredentials: (options.excludeCredentials || []).map((credential: any) => ({
        type: credential.type,
        id: decodeBase64Url(credential.id),
        transports: credential.transports,
    })),
});

const toRequestOptions = (options: any): PublicKeyCredentialRequestOptions => ({
    challenge: decodeBase64Url(options.challenge),
    timeout: options.timeout,
    rpId: options.rpId,
    userVerification: options.userVerification,
    allowCredentials: (options.allowCredentials || []).map((credential: any) => ({
        type: credential.type,
        id: decodeBase64Url(credential.id),
        transports: credential.transports,
    })),
});

/**
 * Runs the whole registration: challenge from the server, the browser's passkey dialog, then
 * the result back to the server. Needs the current password, which the server checks twice.
 */
export const createPasskey = async (name: string, password: string): Promise<Passkey> => {
    const { data: options } = await http.post('/api/client/account/passkeys/options', { password });

    const credential = (await navigator.credentials.create({
        publicKey: toCreationOptions(options.data.publicKey),
    })) as PublicKeyCredential | null;
    if (!credential) {
        throw new DOMException('No credential', 'NotAllowedError');
    }

    const response = credential.response as AuthenticatorAttestationResponse;
    const { data } = await http.post('/api/client/account/passkeys', {
        name,
        password,
        credential: {
            id: credential.id,
            rawId: encodeBase64Url(credential.rawId),
            type: credential.type,
            response: {
                clientDataJSON: encodeBase64Url(response.clientDataJSON),
                attestationObject: encodeBase64Url(response.attestationObject),
                transports: typeof response.getTransports === 'function' ? response.getTransports() : [],
            },
        },
    });

    return rawDataToPasskey(data.data);
};

export const rawDataToPasskey = (data: any): Passkey => ({
    id: data.id,
    name: data.name,
    createdAt: data.created_at ? new Date(data.created_at) : null,
    lastUsedAt: data.last_used_at ? new Date(data.last_used_at) : null,
});

export const getPasskeys = async (): Promise<Passkey[]> => {
    const { data } = await http.get('/api/client/account/passkeys');

    return (data.data as any[]).map(rawDataToPasskey);
};

export const deletePasskey = async (id: number, password: string): Promise<void> => {
    await http.delete(`/api/client/account/passkeys/${id}`, { data: { password } });
};

/**
 * Passkey login without a username: the browser shows the passkeys it has for this site.
 * Resolves with the URL to continue to.
 */
export const loginWithPasskey = async (): Promise<string> => {
    await http.get('/sanctum/csrf-cookie');
    const { data: options } = await http.post('/auth/passkey/options');

    const credential = (await navigator.credentials.get({
        publicKey: toRequestOptions(options.data.publicKey),
    })) as PublicKeyCredential | null;
    if (!credential) {
        throw new DOMException('No credential', 'NotAllowedError');
    }

    const response = credential.response as AuthenticatorAssertionResponse;
    const { data } = await http.post('/auth/passkey/login', {
        credential: {
            id: credential.id,
            rawId: encodeBase64Url(credential.rawId),
            type: credential.type,
            response: {
                clientDataJSON: encodeBase64Url(response.clientDataJSON),
                authenticatorData: encodeBase64Url(response.authenticatorData),
                signature: encodeBase64Url(response.signature),
                userHandle: response.userHandle ? encodeBase64Url(response.userHandle) : null,
            },
        },
    });

    return data.data.intended || '/';
};
