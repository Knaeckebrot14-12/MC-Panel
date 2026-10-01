/*
 * Service worker of the installable panel app. It only handles push notifications (server crashes,
 * coin reminders, ticket replies, node alerts); pages are always loaded from the network.
 */
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => event.waitUntil(self.clients.claim()));

self.addEventListener('push', (event) => {
    let data = {};
    try {
        data = event.data ? event.data.json() : {};
    } catch (e) {
        data = { title: event.data ? event.data.text() : '' };
    }

    event.waitUntil(
        self.registration.showNotification(data.title || 'Recoded Ptero', {
            body: data.body || '',
            tag: data.tag || undefined,
            renotify: !!data.tag,
            icon: '/favicons/android-chrome-192x192.png',
            badge: '/favicons/android-chrome-192x192.png',
            data: { url: data.url || '/' },
        })
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    // Only paths on this panel are opened.
    const path = String((event.notification.data && event.notification.data.url) || '/');
    const url = new URL(path.startsWith('/') ? path : '/', self.location.origin).href;

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            for (const client of clients) {
                if (client.url.startsWith(self.location.origin) && 'focus' in client) {
                    client.navigate(url);
                    return client.focus();
                }
            }
            return self.clients.openWindow(url);
        })
    );
});
