/**
 * Installable app (PWA) helpers: the service worker for push notifications, the browser's
 * "install app" prompt, and the light/dark theme switch.
 */
import http from '@/api/http';
import { PushService } from '@/lib/pushTypes';

type InstallPromptEvent = Event & { prompt: () => Promise<void>; userChoice: Promise<{ outcome: string }> };

let installPrompt: InstallPromptEvent | null = null;
const listeners = new Set<() => void>();

export function setupPwa(): void {
    if (typeof window === 'undefined') return;

    // Chrome/Edge/Android fire this when the panel can be installed; it is kept for the "Install" button.
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        installPrompt = event as InstallPromptEvent;
        listeners.forEach((fn) => fn());
    });
    window.addEventListener('appinstalled', () => {
        installPrompt = null;
        listeners.forEach((fn) => fn());
    });

    if ('serviceWorker' in navigator && window.isSecureContext) {
        navigator.serviceWorker.register('/sw.js').catch(() => undefined);
    }
}

export const canInstall = (): boolean => installPrompt !== null;

export const isInstalled = (): boolean =>
    window.matchMedia('(display-mode: standalone)').matches ||
    (navigator as Navigator & { standalone?: boolean }).standalone === true;

export const onInstallChange = (fn: () => void): (() => void) => {
    listeners.add(fn);
    return () => listeners.delete(fn);
};

export async function promptInstall(): Promise<boolean> {
    if (!installPrompt) return false;
    await installPrompt.prompt();
    const choice = await installPrompt.userChoice;
    installPrompt = null;
    listeners.forEach((fn) => fn());

    return choice.outcome === 'accepted';
}

// ---- push notifications ----

export const pushSupported = (): boolean =>
    typeof window !== 'undefined' &&
    window.isSecureContext &&
    'serviceWorker' in navigator &&
    'PushManager' in window &&
    'Notification' in window;

const keyToBytes = (base64: string): Uint8Array => {
    const padded = (base64 + '='.repeat((4 - (base64.length % 4)) % 4)).replace(/-/g, '+').replace(/_/g, '/');
    const raw = window.atob(padded);
    return Uint8Array.from(raw, (c) => c.charCodeAt(0));
};

export async function currentSubscription(): Promise<PushSubscription | null> {
    if (!pushSupported()) return null;
    const registration = await navigator.serviceWorker.getRegistration('/');
    return registration ? registration.pushManager.getSubscription() : null;
}

export async function enablePush(): Promise<void> {
    const { data } = await http.get<PushService>('/api/client/account/push');
    if (!data.supported || !data.public_key) {
        throw new Error('push-unsupported');
    }

    if ((await Notification.requestPermission()) !== 'granted') {
        throw new Error('push-denied');
    }

    const registration = (await navigator.serviceWorker.getRegistration('/')) || (await navigator.serviceWorker.register('/sw.js'));
    await navigator.serviceWorker.ready;
    const subscription =
        (await registration.pushManager.getSubscription()) ||
        (await registration.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: keyToBytes(data.public_key) }));

    await http.post('/api/client/account/push', subscription.toJSON());
}

export async function disablePush(): Promise<void> {
    const subscription = await currentSubscription();
    if (subscription) {
        await http.delete('/api/client/account/push', { data: { endpoint: subscription.endpoint } }).catch(() => undefined);
        await subscription.unsubscribe();
    }
}

export const testPush = (): Promise<number> => http.post('/api/client/account/push/test').then(({ data }) => data.sent);

// ---- theme ----

export type Theme = 'dark' | 'light';

export const getTheme = (): Theme => (document.documentElement.getAttribute('data-theme') === 'light' ? 'light' : 'dark');

export function setTheme(theme: Theme): void {
    document.documentElement.setAttribute('data-theme', theme);
    try {
        localStorage.setItem('rp-theme', theme);
    } catch (e) {
        // Private windows may block storage; the theme then lasts until the page is reloaded.
    }
    window.dispatchEvent(new Event('rp-theme'));
}
