// Dynamic cache version - update this when you want to force cache refresh
const CACHE_VERSION = 'v1.2.0';
const CACHE_NAME = `jdih-padang-${CACHE_VERSION}`;
const MAX_CACHE_ITEMS = 100;

// Get base URL dynamically
const getBaseUrl = () => {
    return self.location.origin;
};

// Core pages to cache
const getUrlsToCache = () => {
    const base = getBaseUrl();
    return [
        `${base}/`,
        `${base}/peraturan`,
        `${base}/berita`,
        `${base}/agenda`,
        `${base}/statistik`,
        `${base}/tentang`,
        `${base}/kontak`,
        `${base}/panduan`,
        `${base}/cari`,
        `${base}/offline`, // Offline fallback page
        // Static assets
        `${base}/favicon.ico`,
        `${base}/images/logo-jdih.png`,
        `${base}/images/jdihkotapadang.png`,
        `${base}/images/icons/icon-192x192.png`,
        `${base}/images/icons/icon-512x512.png`,
        `${base}/vendors/bootstrap/css/bootstrap.min.css`,
        `${base}/vendors/fontawesome/css/all.min.css`,
        `${base}/vendors/jquery/jquery.min.js`,
        `${base}/vendors/bootstrap/js/bootstrap.bundle.min.js`
    ];
};

// Limit cache size to prevent unbounded growth
async function trimCache(cacheName, maxItems) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    if (keys.length > maxItems) {
        await cache.delete(keys[0]);
        return trimCache(cacheName, maxItems);
    }
}

// Install event
self.addEventListener('install', event => {
    console.log('[ServiceWorker] Installing...', CACHE_NAME);
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('[ServiceWorker] Cache opened:', CACHE_NAME);
                const urlsToCache = getUrlsToCache();
                return Promise.allSettled(
                    urlsToCache.map(url => 
                        cache.add(url).catch(err => {
                            console.warn('[ServiceWorker] Failed to cache:', url, err.message);
                            return null;
                        })
                    )
                ).then(results => {
                    const failed = results.filter(r => r.status === 'rejected').length;
                    if (failed > 0) {
                        console.warn(`[ServiceWorker] ${failed} resources failed to cache`);
                    }
                    console.log(`[ServiceWorker] Cached ${results.length - failed} resources`);
                });
            })
            .then(() => self.skipWaiting()) // skipWaiting inside waitUntil chain
            .catch(err => {
                console.error('[ServiceWorker] Install failed:', err);
            })
    );
});

// Fetch event with smart caching strategy
self.addEventListener('fetch', event => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    const baseUrl = getBaseUrl();
    const baseUrlObj = new URL(baseUrl);
    
    // Only handle same-origin requests
    if (url.origin !== baseUrlObj.origin) {
        return;
    }

    // Skip service worker, manifest, and API endpoints
    if (url.pathname.includes('/sw.js') || 
        url.pathname.includes('/manifest.json') ||
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/ajax/') ||
        url.pathname.includes('/integrasiJDIH/')) {
        return;
    }

    // Skip admin panel and protected routes
    if (url.pathname.startsWith('/dashboard') ||
        url.pathname.startsWith('/harmonisasi') ||
        url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/legalisasi')) {
        return;
    }

    // Only intercept specific resource types
    const acceptHeader = event.request.headers.get('accept') || '';
    const isHTML = acceptHeader.includes('text/html');
    const isStaticAsset = acceptHeader.includes('text/css') || 
                         acceptHeader.includes('application/javascript') ||
                         acceptHeader.includes('image/') ||
                         url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot|webp)$/i);

    // For HTML pages, use network-first with cache fallback
    if (isHTML) {
        event.respondWith(
            (async () => {
                try {
                    const networkResponse = await fetch(event.request.clone());
                    
                    if (networkResponse && networkResponse.status === 200) {
                        const cache = await caches.open(CACHE_NAME);
                        cache.put(event.request, networkResponse.clone()).catch(() => {});
                        trimCache(CACHE_NAME, MAX_CACHE_ITEMS).catch(() => {});
                    }
                    
                    return networkResponse;
                } catch (err) {
                    console.warn('[ServiceWorker] Network failed, trying cache:', url.pathname);
                    
                    const cachedResponse = await caches.match(event.request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    
                    // Last resort: offline page for navigation
                    if (event.request.mode === 'navigate') {
                        const offlinePage = await caches.match(`${baseUrl}/offline`);
                        if (offlinePage) {
                            return offlinePage;
                        }
                    }
                    
                    // Return a simple offline response
                    return new Response('Offline', {
                        status: 503,
                        statusText: 'Service Unavailable',
                        headers: new Headers({ 'Content-Type': 'text/plain' })
                    });
                }
            })()
        );
        return;
    }

    // For static assets, use cache-first with network fallback
    if (isStaticAsset) {
        event.respondWith(
            (async () => {
                try {
                    const cachedResponse = await caches.match(event.request);
                    if (cachedResponse) {
                        // Stale-while-revalidate: update cache in background
                        fetch(event.request.clone()).then(response => {
                            if (response && response.status === 200) {
                                caches.open(CACHE_NAME).then(cache => {
                                    cache.put(event.request, response.clone());
                                    trimCache(CACHE_NAME, MAX_CACHE_ITEMS).catch(() => {});
                                });
                            }
                        }).catch(() => {});
                        return cachedResponse;
                    }

                    // If not in cache, fetch from network
                    const networkResponse = await fetch(event.request);
                    if (networkResponse && networkResponse.status === 200) {
                        const cache = await caches.open(CACHE_NAME);
                        cache.put(event.request, networkResponse.clone()).catch(() => {});
                        trimCache(CACHE_NAME, MAX_CACHE_ITEMS).catch(() => {});
                    }
                    return networkResponse;
                } catch (err) {
                    const cachedResponse = await caches.match(event.request);
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    return new Response('', { status: 408, statusText: 'Request Timeout' });
                }
            })()
        );
        return;
    }

    // For other requests, don't intercept
    return;
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    console.log('[ServiceWorker] Activating...', CACHE_NAME);
    
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheName !== CACHE_NAME && cacheName.startsWith('jdih-padang-')) {
                        console.log('[ServiceWorker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            return self.clients.claim();
        })
    );
});

// Handle push notifications
self.addEventListener('push', event => {
    console.log('[ServiceWorker] Push notification received');
    
    let notificationData = {
        title: 'JDIH Kota Padang',
        body: 'Ada dokumen hukum baru di JDIH Kota Padang',
        icon: `${getBaseUrl()}/images/icons/icon-192x192.png`,
        badge: `${getBaseUrl()}/images/icons/icon-192x192.png`,
        vibrate: [100, 50, 100],
        data: {
            url: `${getBaseUrl()}/`,
            timestamp: Date.now()
        }
    };

    if (event.data) {
        try {
            const data = event.data.json();
            notificationData = { ...notificationData, ...data };
        } catch (e) {
            notificationData.body = event.data.text() || notificationData.body;
        }
    }

    const options = {
        ...notificationData,
        actions: [
            {
                action: 'explore',
                title: 'Lihat Dokumen',
                icon: `${getBaseUrl()}/images/icons/icon-192x192.png`
            },
            {
                action: 'close',
                title: 'Tutup'
            }
        ],
        requireInteraction: false,
        tag: 'jdih-notification'
    };

    event.waitUntil(
        self.registration.showNotification(notificationData.title, options)
    );
});

// Handle notification clicks
self.addEventListener('notificationclick', event => {
    event.notification.close();

    if (event.action === 'explore' || !event.action) {
        event.waitUntil(
            clients.openWindow(event.notification.data?.url || `${getBaseUrl()}/`)
        );
    }
});
