<x-filament-panels::page>
    <div class="space-y-6" x-data="{ copied: false }">

        {{-- Header + Actions --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Sinkronisasi Kalender
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Hubungkan kalender BizOS dengan Google Calendar atau Outlook, dan bagikan feed iCal read-only ke aplikasi kalender apa pun.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    wire:click="toggleAutoSync"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold transition
                        {{ $autoSync
                            ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-900/20 dark:border-emerald-800 dark:text-emerald-300'
                            : 'bg-white border-gray-300 text-gray-600 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300' }}">
                    <span class="relative inline-flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full rounded-full opacity-75 {{ $autoSync ? 'animate-ping bg-emerald-400' : 'bg-gray-400' }}"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full {{ $autoSync ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                    </span>
                    Auto-sync: {{ $autoSync ? 'Aktif' : 'Nonaktif' }}
                </button>
                <button
                    type="button"
                    wire:click="syncNow"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                    <svg wire:loading wire:target="syncNow" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <svg wire:loading.remove wire:target="syncNow" class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Sync Sekarang
                </button>
            </div>
        </div>

        {{-- Provider Connection Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($providers as $provider)
            <div class="fi-section rounded-xl border p-6
                {{ ($provider['connected'] ?? false) ? 'border-emerald-200 dark:border-emerald-800' : 'border-gray-200 dark:border-gray-700' }}">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">
                            @if($provider['key'] === 'google') 🔴
                            @elseif($provider['key'] === 'outlook') 🟦
                            @else 🔗
                            @endif
                        </span>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $provider['label'] }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $provider['description'] }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 text-xs font-medium rounded-full
                        {{ ($provider['connected'] ?? false)
                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                            : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' }}">
                        <span class="h-2 w-2 rounded-full {{ ($provider['connected'] ?? false) ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                        {{ ($provider['connected'] ?? false) ? 'Terhubung' : 'Terputus' }}
                    </span>
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Konfigurasi OAuth</span>
                        <span class="font-medium {{ ($provider['configured'] ?? false) ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ ($provider['configured'] ?? false) ? 'Siap' : 'Belum dikonfigurasi' }}
                        </span>
                    </div>
                    @if($provider['last_sync_at'] ?? null)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Sinkronisasi terakhir</span>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $provider['last_sync_at'] }}</span>
                    </div>
                    @endif
                    @if($provider['last_error_message'] ?? null)
                    <div class="mt-2 rounded-lg bg-red-50 dark:bg-red-900/20 px-3 py-2 text-xs text-red-700 dark:text-red-300">
                        {{ $provider['last_error_message'] }}
                    </div>
                    @endif
                </div>

                @if(!($provider['connected'] ?? false) && ($provider['auth_url'] ?? null))
                <div class="mt-4 rounded-lg bg-blue-50 dark:bg-blue-900/20 px-3 py-2 text-xs text-blue-700 dark:text-blue-300">
                    <p class="font-medium">Belum ada token? Buka URL otorisasi lalu tempel kode ke kolom "Kode Otorisasi" pada form OAuth di bawah.</p>
                    <a href="{{ $provider['auth_url'] }}" target="_blank" rel="noopener" class="mt-1 inline-flex items-center gap-1 font-semibold underline hover:opacity-80">
                        Buka halaman otorisasi →
                    </a>
                </div>
                @endif

                <div class="mt-5 flex gap-2">
                    @if($provider['connected'] ?? false)
                    <button
                        type="button"
                        wire:click="disconnect('{{ $provider['key'] }}')"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:border-red-800 dark:bg-transparent dark:text-red-400 dark:hover:bg-red-900/20 transition">
                        Putuskan Koneksi
                    </button>
                    @else
                    <button
                        type="button"
                        wire:click="connect('{{ $provider['key'] }}')"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500 transition">
                        Hubungkan
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- OAuth Configuration Form --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Konfigurasi OAuth</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Isi Client ID dan Client Secret dari penyedia, lalu klik "Hubungkan" pada kartu provider di atas. Kode otorisasi bersifat opsional (mode stub).
            </p>
            <form wire:submit.prevent="saveOauth" class="space-y-4">
                {{ $this->form }}
                <div>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                        💾 Simpan Konfigurasi OAuth
                    </button>
                </div>
            </form>
        </div>

        {{-- iCal Feed --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <div class="flex items-center gap-3">
                <span class="text-2xl">📅</span>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Feed iCal (Read-Only)</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Berlangganan dari aplikasi kalender apa pun (Google, Apple, Outlook) tanpa perlu OAuth. URL ini hanya bisa dibaca.
                    </p>
                </div>
            </div>

            @if($icalFeedUrl)
            <div class="mt-4 flex flex-col sm:flex-row gap-2">
                <div class="relative flex-1">
                    <input
                        type="text"
                        readonly
                        value="{{ $icalFeedUrl }}"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 dark:text-white py-2 pl-3 pr-10 text-sm font-mono focus:outline-none">
                    <button
                        type="button"
                        @click="navigator.clipboard.writeText('{{ $icalFeedUrl }}').then(() => { copied = true; setTimeout(() => copied = false, 2000); })"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-indigo-500 transition">
                        <span x-show="!copied" title="Salin">
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" /></svg>
                        </span>
                        <span x-cloak x-show="copied" class="text-emerald-500 text-sm font-semibold">Tersalin!</span>
                    </button>
                </div>
                <div class="flex gap-2">
                    <a href="{{ $icalFeedUrl }}"
                       class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Download .ics
                    </a>
                    <button
                        type="button"
                        wire:click="regenerateFeed"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-amber-300 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20 px-4 py-2 text-sm font-semibold text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition">
                        Buat Ulang Token
                    </button>
                </div>
            </div>

            <div class="mt-4 rounded-lg bg-gray-50 dark:bg-gray-700/50 px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                <p class="font-semibold text-gray-700 dark:text-gray-300 mb-1">Cara berlangganan:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong>Google Calendar:</strong> Pengaturan → Tambah kalender → Dari URL → tempel URL di atas.</li>
                    <li><strong>Apple Calendar:</strong> File → Kalender Baru Berlangganan → tempel URL.</li>
                    <li><strong>Outlook:</strong> Tambah kalender → Berlangganan dari web → tempel URL.</li>
                </ul>
            </div>
            @else
            <p class="mt-4 text-sm text-gray-400">Feed iCal tersedia setelah Anda login dengan perusahaan aktif.</p>
            @endif
        </div>

        {{-- Recent Sync Activity --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Aktivitas Sinkronisasi Terbaru</h2>
            @if(count($syncLogs))
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-2 font-medium">Provider</th>
                            <th class="pb-2 font-medium">Entitas</th>
                            <th class="pb-2 font-medium">Arah</th>
                            <th class="pb-2 font-medium">Status</th>
                            <th class="pb-2 font-medium">Diproses</th>
                            <th class="pb-2 font-medium">Ringkasan</th>
                            <th class="pb-2 font-medium">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($syncLogs as $log)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="py-2 text-gray-700 dark:text-gray-300">
                                @if(($log['connector_type'] ?? '') === 'google_calendar') 🔴 Google
                                @elseif(($log['connector_type'] ?? '') === 'outlook_calendar') 🟦 Outlook
                                @else {{ $log['connector_type'] ?? '-' }}
                                @endif
                            </td>
                            <td class="py-2 text-gray-700 dark:text-gray-300">{{ $log['entity'] ?? '-' }}</td>
                            <td class="py-2">
                                @if(($log['direction'] ?? '') === 'inbound') <span class="text-gray-600 dark:text-gray-400">↓ Masuk</span>
                                @elseif(($log['direction'] ?? '') === 'outbound') <span class="text-gray-600 dark:text-gray-400">↑ Keluar</span>
                                @else <span class="text-gray-600 dark:text-gray-400">↕ Dua Arah</span>
                                @endif
                            </td>
                            <td class="py-2">
                                <span class="px-2 py-0.5 text-xs rounded-full
                                    @if(($log['status'] ?? '') === 'success') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300
                                    @elseif(($log['status'] ?? '') === 'partial') bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300
                                    @elseif(($log['status'] ?? '') === 'failed') bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300
                                    @else bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400
                                    @endif">
                                    {{ $log['status'] ?? '-' }}
                                </span>
                            </td>
                            <td class="py-2 text-gray-700 dark:text-gray-300">{{ $log['records_processed'] ?? 0 }}</td>
                            <td class="py-2 text-gray-500 dark:text-gray-400 max-w-xs truncate">{{ $log['summary'] ?? '-' }}</td>
                            <td class="py-2 text-xs text-gray-400">{{ $log['started_at'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-gray-400 text-sm">Belum ada aktivitas sinkronisasi. Hubungkan provider atau klik "Sync Sekarang".</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
