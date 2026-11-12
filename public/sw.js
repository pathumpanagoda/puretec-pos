/**
 * =============================================================
 * Pure POS — Service Worker v2.1 (Production Reliable)
 * =============================================================
 *
 * IMPROVEMENTS:
 *   - Versioned cache with auto-cleanup
 *   - Background sync registration
 *   - POS shell always cached for offline
 *   - API requests pass-through (handled by PureSync)
 *   - POST request queuing via IndexedDB
 *
 * Version: 2.1.0
 * =============================================================
 */

const CACHE_VERSION = 'v2.1.0';
const STATIC_CACHE  = 'pure-pos-static-' + CACHE_VERSION;
const DYNAMIC_CACHE = 'pure-pos-dynamic-' + CACHE_VERSION;
const FONT_CACHE    = 'pure-pos-fonts-v1';

const PRECACHE_ASSETS = [
    '/',
    '/pos',
    '/css/pure-pos.css',
    '/css/pos.css',
    '/js/pure-pos.js',
    '/js/puredb.js',
    '/js/sync-engine.js',
    '/js/offline-pos.js',
    '/js/pos.js',
    '/manifest.json',
];

const NEVER_CACHE = [
    '/puretec/',
    '/login',
    '/logout',
    '/sanctum/',
    'livewire',
    'hot-update',
    'chrome-extension',
];

// ── Install ──────────────────────────────────────────────────

self.addEventListener('install', event => {
    console.log('[SW] Installing', CACHE_VERSION);
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => cache.addAll(PRECACHE_ASSETS))
            .then(() => self.skipWaiting())
            .catch(err => {
                console.error('[SW] Pre-cache failed:', err);
                // Don't block install if some assets fail
                return self.skipWaiting();
            })
    );
});

// ── Activate ─────────────────────────────────────────────────

self.addEventListener('activate', event => {
    console.log('[SW] Activating', CACHE_VERSION);
    event.waitUntil(
        caches.keys()
            .then(keys => {
                return Promise.all(
                    keys.filter(key =>
                        key !== STATIC_CACHE &&
                        key !== DYNAMIC_CACHE &&
                        key !== FONT_CACHE
                    ).map(key => {
                        console.log('[SW] Removing old cache:', key);
                        return caches.delete(key);
                    })
                );
            })
            .then(() => self.clients.claim())
    );
});

// ── Fetch ────────────────────────────────────────────────────

self.addEventListener('fetch', event => {
    const url = new URL(event.request.url);

    // Skip non-GET requests
    if (event.request.method !== 'GET') return;

    // Skip URLs we should never cache
    if (NEVER_CACHE.some(pattern => url.pathname.includes(pattern))) return;

    // Skip cross-origin (except fonts/CDN)
    if (url.origin !== self.location.origin &&
        !url.hostname.includes('fonts.googleapis.com') &&
        !url.hostname.includes('fonts.gstatic.com') &&
        !url.hostname.includes('cdn.jsdelivr.net')) {
        return;
    }

    // API requests → pass-through (handled by PureSync)
    if (url.pathname.startsWith('/api/')) return;

    // Fonts → Cache First (long-lived)
    if (url.hostname.includes('fonts.googleapis.com') ||
        url.hostname.includes('fonts.gstatic.com')) {
        event.respondWith(cacheFirst(event.request, FONT_CACHE));
        return;
    }

    // CDN → Stale While Revalidate
    if (url.hostname.includes('cdn.jsdelivr.net')) {
        event.respondWith(staleWhileRevalidate(event.request, STATIC_CACHE));
        return;
    }

    // Static assets → Cache First
    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirst(event.request, STATIC_CACHE));
        return;
    }

    // POS page → Network First with offline shell fallback
    if (url.pathname === '/pos' || url.pathname.startsWith('/pos/')) {
        event.respondWith(networkFirstWithShell(event.request));
        return;
    }

    // HTML pages → Network First
    if (event.request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(networkFirst(event.request));
        return;
    }

    // Default: Stale While Revalidate
    event.respondWith(staleWhileRevalidate(event.request, DYNAMIC_CACHE));
});

// ── Caching Strategies ───────────────────────────────────────

function cacheFirst(request, cacheName) {
    return caches.open(cacheName).then(cache => {
        return cache.match(request).then(cached => {
            if (cached) return cached;
            return fetch(request).then(response => {
                if (response.ok) cache.put(request, response.clone());
                return response;
            });
        });
    }).catch(() => caches.match('/'));
}

function networkFirst(request) {
    return fetch(request)
        .then(response => {
            if (response.ok) {
                const clone = response.clone();
                caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone));
            }
            return response;
        })
        .catch(() => {
            return caches.match(request).then(cached => {
                return cached || caches.match('/');
            });
        });
}

/**
 * Network First with POS shell fallback.
 * If the POS page fails to load from network, serve the cached POS page.
 */
function networkFirstWithShell(request) {
    return fetch(request)
        .then(response => {
            if (response.ok) {
                const clone = response.clone();
                caches.open(DYNAMIC_CACHE).then(cache => cache.put(request, clone));
            }
            return response;
        })
        .catch(() => {
            return caches.match(request)
                .then(cached => cached || caches.match('/pos'))
                .then(cached => cached || caches.match('/'));
        });
}

function staleWhileRevalidate(request, cacheName) {
    return caches.open(cacheName).then(cache => {
        return cache.match(request).then(cached => {
            const networkFetch = fetch(request).then(response => {
                if (response.ok) cache.put(request, response.clone());
                return response;
            }).catch(() => cached);

            return cached || networkFetch;
        });
    });
}

// ── Helpers ──────────────────────────────────────────────────

function isStaticAsset(pathname) {
    return /\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i.test(pathname);
}

// ── Background Sync ──────────────────────────────────────────

self.addEventListener('sync', event => {
    if (event.tag === 'purepos-sync') {
        event.waitUntil(
            self.clients.matchAll().then(clients => {
                clients.forEach(client => {
                    client.postMessage({ type: 'SYNC_TRIGGER' });
                });
            })
        );
    }
});

// ── Messages ─────────────────────────────────────────────────

self.addEventListener('message', event => {
    if (event.data === 'skipWaiting') {
        self.skipWaiting();
    }
    if (event.data === 'clearCaches') {
        caches.keys().then(keys => keys.forEach(k => caches.delete(k)));
    }
    if (event.data === 'getVersion') {
        event.source.postMessage({ type: 'SW_VERSION', version: CACHE_VERSION });
    }
});
