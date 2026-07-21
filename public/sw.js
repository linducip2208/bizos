const CACHE_NAME = 'bizos-mobile-v2';
const STATIC_CACHE = 'bizos-static-v1';
const API_CACHE = 'bizos-api-v1';

const STATIC_ASSETS = [
  '/',
  '/admin/login',
  '/manifest.json',
  '/pwa-register.js',
  '/css/filament/admin/theme.css',
  '/favicon.ico',
];

const API_PATTERNS = [
  '/api/v1/mobile/home',
  '/api/v1/mobile/offline-data',
  '/api/v1/mobile/dashboard',
];

// Install event — cache static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) => {
      return cache.addAll(STATIC_ASSETS);
    })
  );
  self.skipWaiting();
});

// Activate event — cleanup old caches
self.addEventListener('activate', (event) => {
  const validCaches = [CACHE_NAME, STATIC_CACHE, API_CACHE];
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => !validCaches.includes(name))
          .map((name) => caches.delete(name))
      );
    })
  );
  self.clients.claim();
});

// Fetch event — network first, fallback to cache
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  // Skip non-GET requests
  if (event.request.method !== 'GET') return;

  // Skip API sync endpoints (POST only, already skipped above)
  // Skip Chrome extensions and other non-app requests
  if (!url.pathname.startsWith(self.location.pathname.replace(/\/sw\.js$/, ''))) {
    if (!url.hostname.includes('bizos') && !url.hostname.includes('localhost')) return;
  }

  // API requests: network first with cache fallback
  if (url.pathname.includes('/api/')) {
    event.respondWith(networkFirstWithCache(event.request, API_CACHE));
    return;
  }

  // Static assets: cache first with network update
  if (
    url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/) ||
    url.pathname === '/manifest.json' ||
    url.pathname === '/favicon.ico'
  ) {
    event.respondWith(cacheFirstWithNetwork(event.request, STATIC_CACHE));
    return;
  }

  // HTML navigation: network first
  event.respondWith(networkFirstWithCache(event.request, CACHE_NAME));
});

// Background Sync for offline actions
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-offline-actions') {
    event.waitUntil(syncOfflineActions());
  }
  if (event.tag === 'sync-voice-notes') {
    event.waitUntil(syncVoiceNotes());
  }
});

// Periodic background sync for data refresh
self.addEventListener('periodicsync', (event) => {
  if (event.tag === 'refresh-offline-data') {
    event.waitUntil(refreshOfflineData());
  }
});

// Push notification
self.addEventListener('push', (event) => {
  const data = event.data ? event.data.json() : {};
  const title = data.title || 'BizOS';
  const options = {
    body: data.body || 'Notifikasi baru',
    icon: data.icon || '/favicon.ico',
    badge: '/favicon.ico',
    vibrate: [200, 100, 200],
    data: {
      url: data.url || '/',
    },
    actions: data.actions || [],
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const url = event.notification.data?.url || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if (client.url.includes(url) && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(url);
      }
    })
  );
});

// --- Helper strategies ---

async function networkFirstWithCache(request, cacheName) {
  const cache = await caches.open(cacheName);
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) {
      cache.put(request, networkResponse.clone());
    }
    return networkResponse;
  } catch (error) {
    const cachedResponse = await cache.match(request);
    if (cachedResponse) return cachedResponse;
    throw error;
  }
}

async function cacheFirstWithNetwork(request, cacheName) {
  const cache = await caches.open(cacheName);
  const cachedResponse = await cache.match(request);
  if (cachedResponse) {
    // Update cache in background
    fetch(request)
      .then((response) => {
        if (response.ok) cache.put(request, response);
      })
      .catch(() => {});
    return cachedResponse;
  }
  try {
    const networkResponse = await fetch(request);
    if (networkResponse.ok) cache.put(request, networkResponse.clone());
    return networkResponse;
  } catch (error) {
    return new Response('Offline', { status: 503 });
  }
}

async function syncOfflineActions() {
  try {
    const apiBase = self.location.origin + '/api/v1/mobile/sync';
    const response = await fetch(apiBase, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ actions: [] }),
    });

    if (!response.ok) throw new Error('Sync failed');
    return response.json();
  } catch (error) {
    throw error;
  }
}

async function syncVoiceNotes() {
  // Voice notes are uploaded via POST /api/v1/mobile/voice-note
  // Background sync will retry pending uploads stored in IndexedDB
  const pendingUploads = await getPendingVoiceUploads();
  for (const upload of pendingUploads) {
    try {
      const formData = new FormData();
      formData.append('audio', upload.blob);
      formData.append('recipient_ids', JSON.stringify(upload.recipientIds));

      await fetch(upload.endpoint, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body: formData,
      });

      await removePendingUpload(upload.id);
    } catch (error) {
      // Will retry on next sync
    }
  }
}

async function refreshOfflineData() {
  try {
    const response = await fetch('/api/v1/mobile/offline-data', {
      headers: { 'Accept': 'application/json' },
    });
    if (response.ok) {
      const cache = await caches.open(API_CACHE);
      await cache.put('/api/v1/mobile/offline-data', response.clone());
    }
  } catch (error) {
    // Silent fail — will retry next period
  }
}

// IndexedDB helpers for pending uploads
async function getPendingVoiceUploads() {
  // In a real implementation, this reads from IndexedDB
  // For now, return empty array
  return [];
}

async function removePendingUpload(id) {
  // In a real implementation, this removes from IndexedDB
}
