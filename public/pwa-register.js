/**
 * BizOS PWA Registration
 * Registrasi service worker, install prompt, dan update handler.
 */
(function () {
  'use strict';

  const APP_NAME = 'BizOS';
  let deferredPrompt = null;
  let swRegistration = null;

  // Register Service Worker
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker
        .register('/sw.js', { scope: '/' })
        .then(function (registration) {
          swRegistration = registration;
          console.log('[PWA] Service Worker registered:', registration.scope);

          // Check for updates
          registration.addEventListener('updatefound', function () {
            const newWorker = registration.installing;
            newWorker.addEventListener('statechange', function () {
              if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                showUpdateNotification();
              }
            });
          });
        })
        .catch(function (error) {
          console.log('[PWA] Service Worker registration failed:', error);
        });

      // Register periodic sync for offline data refresh
      navigator.serviceWorker.ready.then(function (registration) {
        if ('periodicSync' in registration) {
          registration.periodicSync
            .register('refresh-offline-data', {
              minInterval: 60 * 60 * 1000, // 1 jam
            })
            .catch(function () {
              // Periodic sync not available
            });
        }
      });
    });
  }

  // Install Prompt
  window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    deferredPrompt = event;

    // Show install button after 5 seconds if not installed
    const alreadyInstalled = window.matchMedia('(display-mode: standalone)').matches;
    if (!alreadyInstalled) {
      setTimeout(function () {
        const installBanner = document.getElementById('pwa-install-banner');
        if (installBanner) {
          installBanner.style.display = 'block';
        }
      }, 5000);
    }
  });

  // PWA installed successfully
  window.addEventListener('appinstalled', function () {
    deferredPrompt = null;
    const installBanner = document.getElementById('pwa-install-banner');
    if (installBanner) {
      installBanner.style.display = 'none';
    }
    console.log('[PWA] ' + APP_NAME + ' berhasil diinstal.');
  });

  // Track display mode changes
  window.matchMedia('(display-mode: standalone)').addEventListener('change', function (event) {
    if (event.matches) {
      console.log('[PWA] Running in standalone mode');
    }
  });

  // --- Public API ---

  window.BizOS = window.BizOS || {};

  window.BizOS.installApp = function () {
    if (!deferredPrompt) {
      alert(
        APP_NAME + ' sudah terinstal atau fitur install belum tersedia.\n\n' +
        'Untuk menginstal: buka menu browser (3 titik), lalu pilih "Tambahkan ke Layar Utama" atau "Install app".'
      );
      return;
    }

    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(function (choiceResult) {
      if (choiceResult.outcome === 'accepted') {
        console.log('[PWA] User accepted install');
      } else {
        console.log('[PWA] User dismissed install');
      }
      deferredPrompt = null;
    });
  };

  window.BizOS.isInstalled = function () {
    return window.matchMedia('(display-mode: standalone)').matches;
  };

  window.BizOS.syncOfflineActions = function () {
    if (!swRegistration || !swRegistration.sync) {
      console.log('[PWA] Background sync not available');
      return Promise.resolve();
    }
    return swRegistration.sync.register('sync-offline-actions');
  };

  window.BizOS.syncVoiceNotes = function () {
    if (!swRegistration || !swRegistration.sync) {
      return Promise.resolve({});
    }
    return swRegistration.sync.register('sync-voice-notes');
  };

  window.BizOS.getInstallStatus = function () {
    return {
      isInstalled: window.matchMedia('(display-mode: standalone)').matches,
      isSupported: 'serviceWorker' in navigator,
      canInstall: !!deferredPrompt,
    };
  };

  // Helper: Show update notification
  function showUpdateNotification() {
    const updateBanner = document.createElement('div');
    updateBanner.id = 'pwa-update-banner';
    updateBanner.style.cssText =
      'position:fixed;bottom:16px;left:50%;transform:translateX(-50%);' +
      'background:#4f46e5;color:white;padding:12px 24px;border-radius:12px;' +
      'box-shadow:0 4px 20px rgba(0,0,0,.25);z-index:99999;cursor:pointer;' +
      'font-family:Inter,sans-serif;font-size:14px;font-weight:500;' +
      'display:flex;align-items:center;gap:12px;';
    updateBanner.innerHTML =
      '<span>Pembaruan tersedia!</span>' +
      '<button style="background:white;color:#4f46e5;border:none;padding:6px 14px;border-radius:8px;font-weight:600;cursor:pointer;">Perbarui</button>';
    document.body.appendChild(updateBanner);

    updateBanner.querySelector('button').addEventListener('click', function () {
      updateBanner.remove();
      navigator.serviceWorker.getRegistration().then(function (reg) {
        if (reg && reg.waiting) {
          reg.waiting.postMessage({ type: 'SKIP_WAITING' });
        }
        window.location.reload();
      });
    });
  }

  // Handle skip waiting message
  navigator.serviceWorker?.addEventListener('message', function (event) {
    if (event.data && event.data.type === 'SKIP_WAITING') {
      navigator.serviceWorker.controller?.postMessage({ type: 'SKIP_WAITING' });
    }
  });
})();
