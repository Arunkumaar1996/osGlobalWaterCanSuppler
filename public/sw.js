const CACHE_NAME = 'aquaflow-cache-v1.4';
const OFFLINE_URL = '/offline';

const PRECACHE_ASSETS = [
    '/',
    '/offline',
    '/manifest.json',
    '/assets/css/app.css',
    '/assets/js/app.js',
    '/assets/vendor/alpine/alpine.min.js',
    '/assets/vendor/jquery/jquery.min.js',
    '/assets/icons/icon-144.png',
    '/assets/icons/icon-192.png',
    '/assets/icons/icon-512.png',
    '/assets/icons/icon-maskable.png'
];

// Install: Pre-cache essential offline shell and static files
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(PRECACHE_ASSETS).catch((err) => {
                console.warn('Pre-cache asset warning:', err);
            });
        })
    );
    self.skipWaiting();
});

// Activate: Clean up stale caches from previous versions
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((name) => {
                    if (name !== CACHE_NAME) {
                        return caches.delete(name);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch: Smart Strategy
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only process GET requests from same origin
    if (request.method !== 'GET') {
        return;
    }

    // Skip caching for sensitive financial, admin, or mutation endpoints
    if (
        url.pathname.startsWith('/checkout') ||
        url.pathname.startsWith('/admin') ||
        url.pathname.includes('/payment') ||
        url.pathname.includes('/webhooks') ||
        url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/register')
    ) {
        return;
    }

    // 1. Navigation requests (HTML pages) -> Network-First with Offline Fallback
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => {
                return caches.match(OFFLINE_URL);
            })
        );
        return;
    }

    // 2. Static Assets (CSS, JS, Fonts, Images) -> Cache-First with Network Fallback
    if (
        url.pathname.startsWith('/assets/') ||
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.js') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.jpg') ||
        url.pathname.endsWith('.svg') ||
        url.pathname.endsWith('.ico')
    ) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => {
                            cache.put(request, responseClone);
                        });
                    }
                    return networkResponse;
                });
            })
        );
    }
});
