// Dynamic cache version - update this when you want to force cache refresh
const CACHE_VERSION = 'v1.1.1';
const CACHE_NAME = `jdih-padang-${CACHE_VERSION}`;

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
        `${base}/assets/css/home-optimized.css`,
        `${base}/assets/js/home-optimized.js`,
        `${base}/assets/img/hero-image.webp`,
        `${base}/vendors/bootstrap/css/bootstrap.min.css`,
        `${base}/vendors/fontawesome/css/all.min.css`,
        `${base}/vendors/jquery/jquery.min.js`,
        `${base}/vendors/bootstrap/js/bootstrap.bundle.min.js`
    ];
};

// Install event - skip waiting to activate immediately
self.addEventListener('install', event => {
    console.log('[ServiceWorker] Installing...', CACHE_NAME);
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('[ServiceWorker] Cache opened:', CACHE_NAME);
                const urlsToCache = getUrlsToCache();
                // Use addAll with error handling
                return Promise.allSettled(
                    urlsToCache.map(url => 
                        cache.add(url).catch(err => {
                            console.warn('[ServiceWorker] Failed to cache:', url, err);
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
            .catch(err => {
                console.error('[ServiceWorker] Install failed:', err);
            })
    );
    
    // Force activation of new service worker
    self.skipWaiting();
});

// Fetch event with smart caching strategy - More permissive to avoid canceling requests
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
        return; // Let browser handle these normally
    }

    // Skip admin panel and protected routes
    if (url.pathname.startsWith('/dashboard') ||
        url.pathname.startsWith('/harmonisasi') ||
        url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/legalisasi')) {
        return; // Don't cache admin pages
    }

    // Only intercept specific resource types
    const acceptHeader = event.request.headers.get('accept') || '';
    const isHTML = acceptHeader.includes('text/html');
    const isStaticAsset = acceptHeader.includes('text/css') || 
                         acceptHeader.includes('application/javascript') ||
                         acceptHeader.includes('image/') ||
                         url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/i);

    // For HTML pages, use network-first with cache fallback
    if (isHTML) {
        event.respondWith(
            (async () => {
                try {
                    // Try network first
                    const networkResponse = await fetch(event.request.clone());
                    
                    // Only cache successful responses
                    if (networkResponse && networkResponse.status === 200) {
                        const cache = await caches.open(CACHE_NAME);
                        // Clone response before caching
                        cache.put(event.request, networkResponse.clone()).catch(err => {
                            console.warn('[ServiceWorker] Failed to cache:', event.request.url, err);
                        });
                    }
                    
                    return networkResponse;
                } catch (err) {
                    console.warn('[ServiceWorker] Network failed, trying cache:', event.request.url);
                    
                    // Fallback to cache
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
                    
                    // If all else fails, let browser handle it
                    return fetch(event.request);
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
                    // Try cache first
                    const cachedResponse = await caches.match(event.request);
                    if (cachedResponse) {
                        // Update cache in background (don't wait)
                        fetch(event.request.clone()).then(response => {
                            if (response && response.status === 200) {
                                caches.open(CACHE_NAME).then(cache => {
                                    cache.put(event.request, response.clone());
                                });
                            }
                        }).catch(() => {
                            // Silently fail background update
                        });
                        return cachedResponse;
                    }

                    // If not in cache, fetch from network
                    const networkResponse = await fetch(event.request);
                    if (networkResponse && networkResponse.status === 200) {
                        // Cache for future use
                        const cache = await caches.open(CACHE_NAME);
                        cache.put(event.request, networkResponse.clone()).catch(() => {});
                    }
                    return networkResponse;
                } catch (err) {
                    console.warn('[ServiceWorker] Static asset fetch failed:', event.request.url, err);
                    // Return cached version if available, otherwise let browser handle
                    const cachedResponse = await caches.match(event.request);
                    return cachedResponse || fetch(event.request);
                }
            })()
        );
        return;
    }

    // For other requests, don't intercept - let browser handle normally
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
            // Take control of all pages immediately
            return self.clients.claim();
        })
    );
});

// Background sync for offline functionality
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

function doBackgroundSync() {
    // Sync any pending data when back online
    console.log('JDIH Padang: Background sync triggered');
    
    // Could sync offline form submissions, feedback, etc.
    return Promise.resolve();
}

// Handle push notifications
self.addEventListener('push', event => {
    console.log('[ServiceWorker] Push notification received');
    
    let notificationData = {
        title: 'JDIH Kota Padang',
        body: 'Ada dokumen hukum baru di JDIH Kota Padang',
        icon: `${getBaseUrl()}/images/logo-jdih.png`,
        badge: `${getBaseUrl()}/images/logo-jdih.png`,
        vibrate: [100, 50, 100],
        data: {
            url: `${getBaseUrl()}/`,
            timestamp: Date.now()
        }
    };

    // Parse push data if available
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
                icon: `${getBaseUrl()}/images/logo-jdih.png`
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
