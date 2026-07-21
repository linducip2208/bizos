@php
    $activeTab = request()->get('tab', 'offline');
@endphp

<x-filament-panels::page>
    <div x-data="{ activeTab: '{{ $activeTab }}' }" class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ __('Pengaturan Mobile & PWA') }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Konfigurasi mode offline, biometric auth, PWA, dan push notification untuk aplikasi mobile.
                </p>
            </div>
            <button
                type="button"
                onclick="window.open('/pwa-register.js', '_blank')"
                class="text-sm text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
            >
                📱 Lihat PWA Docs
            </button>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                <button
                    @click="activeTab = 'offline'"
                    :class="activeTab === 'offline'
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-semibold transition-colors"
                >
                    📡 Mode Offline
                </button>
                <button
                    @click="activeTab = 'biometric'"
                    :class="activeTab === 'biometric'
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-semibold transition-colors"
                >
                    🔐 Biometric Auth
                </button>
                <button
                    @click="activeTab = 'pwa'"
                    :class="activeTab === 'pwa'
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-semibold transition-colors"
                >
                    📱 PWA
                </button>
                <button
                    @click="activeTab = 'notifications'"
                    :class="activeTab === 'notifications'
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-semibold transition-colors"
                >
                    🔔 Push Notification
                </button>
                <button
                    @click="activeTab = 'devices'"
                    :class="activeTab === 'devices'
                        ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap border-b-2 py-3 px-1 text-sm font-semibold transition-colors"
                >
                    📋 Perangkat Terdaftar
                </button>
            </nav>
        </div>

        <!-- Tab: Offline Mode -->
        <div x-show="activeTab === 'offline'" x-cloak class="space-y-6">
            <form wire:submit.prevent="saveOfflineConfig" class="space-y-6">
                {{ $this->form }}

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Mode Offline</h3>
                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="offlineConfig.enabled" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktifkan mode offline</span>
                            </label>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maksimal Cache (MB)</label>
                                <input type="number" wire:model="offlineConfig.max_cache_size_mb" min="10" max="500" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <p class="mt-1 text-xs text-gray-500">Maksimal ukuran data yang disimpan di perangkat.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jenis Data untuk Offline</label>
                                <div class="space-y-2 mt-2">
                                    @foreach(['employees' => 'Data Karyawan', 'products' => 'Data Produk', 'warehouses' => 'Data Gudang', 'tasks' => 'Data Tugas', 'shifts' => 'Shift Kerja', 'settings' => 'Pengaturan Sistem'] as $key => $label)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model="offlineConfig.data_types" value="{{ $key }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Interval Auto-Sync (menit)</label>
                                <input type="number" wire:model="offlineConfig.auto_sync_interval_minutes" min="1" max="120" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="offlineConfig.sync_only_on_wifi" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Sinkronisasi hanya via WiFi</span>
                            </label>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                💾 Simpan Konfigurasi Offline
                            </button>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Info Sinkronisasi</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500">Action Pending</span>
                                <span class="font-semibold text-amber-600">{{ \App\Models\OfflineAction::where('status', 'pending')->count() }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500">Action Gagal</span>
                                <span class="font-semibold text-red-600">{{ \App\Models\OfflineAction::where('status', 'failed')->count() }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500">Action Konflik</span>
                                <span class="font-semibold text-orange-600">{{ \App\Models\OfflineAction::where('status', 'conflict')->count() }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500">Action Berhasil (30 hari)</span>
                                <span class="font-semibold text-emerald-600">{{ \App\Models\OfflineAction::where('status', 'synced')->where('synced_at', '>=', now()->subDays(30))->count() }}</span>
                            </div>
                        </div>

                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mt-6 mb-3">Tipe Action yang Didukung</h4>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">clock_in</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">clock_out</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">visit_checkin</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">visit_checkout</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">task_update</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">task_create</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">stock_opname_scan</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200">goods_receipt_scan</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">delivery_confirmation</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tab: Biometric -->
        <div x-show="activeTab === 'biometric'" x-cloak class="space-y-6">
            <form wire:submit.prevent="saveBiometricConfig" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Konfigurasi Biometric</h3>
                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="biometricConfig.enabled" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktifkan biometric authentication</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="biometricConfig.require_for_clock_in" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Wajibkan biometric untuk clock-in/out</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="biometricConfig.require_for_sensitive_actions" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Wajibkan biometric untuk aksi sensitif</span>
                            </label>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Timeout Challenge (detik)</label>
                                <input type="number" wire:model="biometricConfig.challenge_timeout_seconds" min="60" max="900" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <p class="mt-1 text-xs text-gray-500">Waktu kadaluarsa challenge biometric (default: 300 detik).</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Maksimal Perangkat per User</label>
                                <input type="number" wire:model="biometricConfig.max_devices_per_user" min="1" max="10" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                💾 Simpan Konfigurasi Biometric
                            </button>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Biometric</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500">Total Pengguna</span>
                                <span class="font-semibold text-indigo-600">{{ \App\Models\BiometricRegistration::where('is_active', true)->distinct('user_id')->count() }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500">Total Perangkat</span>
                                <span class="font-semibold text-indigo-600">{{ \App\Models\BiometricRegistration::where('is_active', true)->count() }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500">Android</span>
                                <span class="font-semibold">{{ \App\Models\BiometricRegistration::where('is_active', true)->where('platform', 'android')->count() }}</span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500">iOS</span>
                                <span class="font-semibold">{{ \App\Models\BiometricRegistration::where('is_active', true)->where('platform', 'ios')->count() }}</span>
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-200 dark:border-amber-800">
                            <p class="text-sm text-amber-800 dark:text-amber-200">
                                <strong>Peringatan:</strong> Mencabut biometric akan mengharuskan pengguna login ulang dengan password.
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tab: PWA -->
        <div x-show="activeTab === 'pwa'" x-cloak class="space-y-6">
            <form wire:submit.prevent="savePwaConfig" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Konfigurasi PWA</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Aplikasi</label>
                                <input type="text" wire:model="pwaConfig.app_name" maxlength="100" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Singkat</label>
                                <input type="text" wire:model="pwaConfig.short_name" maxlength="20" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Theme Color</label>
                                <div class="flex gap-3 items-center">
                                    <input type="color" wire:model="pwaConfig.theme_color" class="h-10 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <input type="text" wire:model="pwaConfig.theme_color" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Background Color</label>
                                <div class="flex gap-3 items-center">
                                    <input type="color" wire:model="pwaConfig.background_color" class="h-10 w-16 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                                    <input type="text" wire:model="pwaConfig.background_color" class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Display Mode</label>
                                <select wire:model="pwaConfig.display_mode" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="standalone">Standalone (full screen, no browser UI)</option>
                                    <option value="fullscreen">Fullscreen (true full screen)</option>
                                    <option value="minimal-ui">Minimal UI (minimal browser controls)</option>
                                    <option value="browser">Browser (normal browser tab)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                💾 Simpan & Regenerasi manifest.json
                            </button>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status File PWA</h3>
                        <div class="space-y-3 text-sm">
                            @php
                                $manifestExists = file_exists(public_path('manifest.json'));
                                $swExists = file_exists(public_path('sw.js'));
                                $registerExists = file_exists(public_path('pwa-register.js'));
                            @endphp
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500">manifest.json</span>
                                <span class="font-semibold {{ $manifestExists ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $manifestExists ? '✅ ' . round(filesize(public_path('manifest.json')) / 1024, 1) . ' KB' : '❌ Tidak ada' }}
                                </span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500">sw.js</span>
                                <span class="font-semibold {{ $swExists ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $swExists ? '✅ ' . round(filesize(public_path('sw.js')) / 1024, 1) . ' KB' : '❌ Tidak ada' }}
                                </span>
                            </div>
                            <div class="flex justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                                <span class="text-gray-500">pwa-register.js</span>
                                <span class="font-semibold {{ $registerExists ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $registerExists ? '✅ ' . round(filesize(public_path('pwa-register.js')) / 1024, 1) . ' KB' : '❌ Tidak ada' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <h4 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">Cara Testing PWA:</h4>
                            <ol class="list-decimal list-inside text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                <li>Buka Chrome/Edge di HP</li>
                                <li>Buka halaman <code class="bg-blue-100 dark:bg-blue-800 px-1 rounded">/admin/login</code></li>
                                <li>Buka menu browser (3 titik) → "Tambahkan ke Layar Utama"</li>
                                <li>Atau lihat banner install otomatis setelah 5 detik</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tab: Push Notification -->
        <div x-show="activeTab === 'notifications'" x-cloak class="space-y-6">
            <form wire:submit.prevent="saveNotificationConfig" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Firebase Cloud Messaging (FCM)</h3>
                        <div class="space-y-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="notificationConfig.fcm_enabled" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Aktifkan FCM Push Notification</span>
                            </label>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">FCM Server Key</label>
                                <input type="text" wire:model="notificationConfig.fcm_server_key" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sender ID</label>
                                <input type="text" wire:model="notificationConfig.fcm_sender_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Web Push (VAPID)</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">VAPID Public Key</label>
                                <input type="text" wire:model="notificationConfig.vapid_public_key" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">VAPID Private Key</label>
                                <input type="text" wire:model="notificationConfig.vapid_private_key" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-sm">
                                <p class="mt-1 text-xs text-red-500">⚠️ Kunci privat — simpan dengan aman.</p>
                            </div>
                        </div>
                        <div class="mt-6">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                💾 Simpan Konfigurasi Push
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tab: Registered Devices -->
        <div x-show="activeTab === 'devices'" x-cloak>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Perangkat Biometric Terdaftar</h3>
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
