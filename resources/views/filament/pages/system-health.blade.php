<x-filament-panels::page>
    <div class="space-y-6" wire:poll.30s="pollReport">

        {{-- Top Row: Status Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Application --}}
            <div class="relative flex flex-col p-5 rounded-xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                            <x-heroicon-o-cpu-chip class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                        </div>
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Aplikasi</span>
                    </div>
                    <span @class([
                        'px-2 py-0.5 rounded-full text-[11px] font-semibold',
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' => ($report['application']['debug_mode'] ?? false) === false,
                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' => ($report['application']['debug_mode'] ?? false) === true,
                    ])>
                        {{ ($report['application']['debug_mode'] ?? false) ? 'Debug' : 'Production' }}
                    </span>
                </div>
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Laravel</span>
                        <span class="font-mono text-xs font-medium text-gray-900 dark:text-gray-200">{{ $report['application']['laravel_version'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>PHP</span>
                        <span class="font-mono text-xs font-medium text-gray-900 dark:text-gray-200">{{ $report['application']['php_version'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Environment</span>
                        <span class="font-mono text-xs font-medium text-gray-900 dark:text-gray-200">{{ $report['application']['environment'] ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Database --}}
            <div class="relative flex flex-col p-5 rounded-xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center">
                            <x-heroicon-o-circle-stack class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                        </div>
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Database</span>
                    </div>
                    @php
                        $dbStatus = $report['database']['status'] ?? 'error';
                        $dbOk = $dbStatus === 'connected';
                    @endphp
                    <span @class([
                        'px-2 py-0.5 rounded-full text-[11px] font-semibold',
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' => $dbOk,
                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' => !$dbOk,
                    ])>
                        {{ $dbOk ? 'Connected' : 'Error' }}
                    </span>
                </div>
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Koneksi</span>
                        <span class="font-mono text-xs font-medium text-gray-900 dark:text-gray-200">{{ $report['database']['connection'] ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Ukuran</span>
                        <span class="font-mono text-xs font-medium text-gray-900 dark:text-gray-200">{{ number_format($report['database']['size_mb'] ?? 0, 1) }} MB</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Tabel</span>
                        <span class="font-mono text-xs font-medium text-gray-900 dark:text-gray-200">{{ number_format($report['database']['tables_count'] ?? 0) }}</span>
                    </div>
                    @if (($report['database']['migrations_pending'] ?? 0) > 0)
                        <div class="flex justify-between text-amber-600">
                            <span>Migrasi Tertunda</span>
                            <span class="font-mono text-xs font-bold">{{ $report['database']['migrations_pending'] }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Cache --}}
            <div class="relative flex flex-col p-5 rounded-xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                            <x-heroicon-o-bolt class="w-5 h-5 text-violet-600 dark:text-violet-400" />
                        </div>
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Cache</span>
                    </div>
                    @php
                        $cacheOk = ($report['cache']['status'] ?? '') === 'ok';
                    @endphp
                    <span @class([
                        'px-2 py-0.5 rounded-full text-[11px] font-semibold',
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' => $cacheOk,
                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' => !$cacheOk,
                    ])>
                        {{ $cacheOk ? 'OK' : 'Error' }}
                    </span>
                </div>
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Driver</span>
                        <span class="font-mono text-xs font-medium text-gray-900 dark:text-gray-200">{{ $report['cache']['driver'] ?? '-' }}</span>
                    </div>
                    @if (!$cacheOk)
                        <div class="text-red-600 text-xs mt-1">{{ $report['cache']['status'] }}</div>
                    @endif
                </div>
            </div>

            {{-- Queue --}}
            <div class="relative flex flex-col p-5 rounded-xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <x-heroicon-o-queue-list class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                        </div>
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Queue</span>
                    </div>
                    @php
                        $queueStatus = $report['queue']['status'] ?? 'unknown';
                    @endphp
                    <span @class([
                        'px-2 py-0.5 rounded-full text-[11px] font-semibold',
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' => $queueStatus === 'ok',
                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' => $queueStatus === 'warning',
                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' => $queueStatus === 'error',
                    ])>
                        {{ match($queueStatus) { 'ok' => 'OK', 'warning' => 'Warning', default => 'Error' } }}
                    </span>
                </div>
                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Pending</span>
                        <span class="font-mono text-xs font-medium text-gray-900 dark:text-gray-200">{{ number_format($report['queue']['pending_jobs'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Gagal</span>
                        <span @class([
                            'font-mono text-xs font-medium',
                            'text-red-600 dark:text-red-400' => ($report['queue']['failed_jobs'] ?? 0) > 0,
                            'text-gray-900 dark:text-gray-200' => ($report['queue']['failed_jobs'] ?? 0) === 0,
                        ])>{{ number_format($report['queue']['failed_jobs'] ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Driver</span>
                        <span class="font-mono text-xs font-medium text-gray-900 dark:text-gray-200">{{ $report['queue']['driver'] ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Second Row: Storage + Scheduler --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Storage --}}
            <div class="rounded-xl border bg-white dark:bg-gray-900 shadow-sm p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                            <x-heroicon-o-server class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Penyimpanan</span>
                    </div>
                    <span class="text-xs text-gray-400 font-mono">{{ $report['storage']['disk'] ?? '-' }}</span>
                </div>
                @php
                    $usedPercent = $report['storage']['used_percent'] ?? 0;
                    $barColor = $usedPercent > 90 ? 'bg-red-500' : ($usedPercent > 70 ? 'bg-amber-500' : 'bg-emerald-500');
                    $totalGb = $report['storage']['total_gb'] ?? 0;
                    $freeGb = $report['storage']['free_gb'] ?? 0;
                    $usedGb = $report['storage']['used_gb'] ?? 0;
                @endphp
                @if ($totalGb > 0)
                    <div class="mb-3">
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                            <span>Terpakai {{ number_format($usedGb, 1) }} / {{ number_format($totalGb, 1) }} GB</span>
                            <span>{{ $usedPercent }}%</span>
                        </div>
                        <div class="w-full h-3 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full {{ $barColor }} rounded-full transition-all duration-500"
                                 style="width: {{ min($usedPercent, 100) }}%"></div>
                        </div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Tersedia {{ number_format($freeGb, 1) }} GB</span>
                        <span>{{ number_format($totalGb, 1) }} GB Total</span>
                    </div>
                @else
                    <div class="text-sm text-gray-400 text-center py-4">
                        Informasi penyimpanan tidak tersedia
                    </div>
                @endif
            </div>

            {{-- Scheduler --}}
            <div class="rounded-xl border bg-white dark:bg-gray-900 shadow-sm p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-lg bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                            <x-heroicon-o-clock class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                        </div>
                        <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Scheduler</span>
                    </div>
                    @php
                        $schedulerStatus = $report['scheduler']['status'] ?? 'unknown';
                    @endphp
                    <span @class([
                        'px-2 py-0.5 rounded-full text-[11px] font-semibold',
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' => $schedulerStatus === 'ok',
                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' => $schedulerStatus === 'warning',
                        'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' => $schedulerStatus === 'unknown',
                    ])>
                        {{ match($schedulerStatus) { 'ok' => 'OK', 'warning' => 'Warning', default => 'Unknown' } }}
                    </span>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Terakhir dijalankan</span>
                        <span class="text-sm font-mono font-medium text-gray-900 dark:text-gray-200 ml-auto">
                            {{ $report['scheduler']['last_run'] ?? 'Belum pernah' }}
                        </span>
                    </div>
                    @if ($schedulerStatus === 'ok')
                        <p class="text-xs text-emerald-600 dark:text-emerald-400">Scheduler berjalan normal dalam 10 menit terakhir.</p>
                    @elseif ($schedulerStatus === 'warning')
                        <p class="text-xs text-amber-600 dark:text-amber-400">Scheduler belum berjalan dalam 10 menit terakhir. Periksa cron job.</p>
                    @else
                        <p class="text-xs text-gray-500 dark:text-gray-400">Belum ada data scheduler. Pastikan cron job sudah dikonfigurasi.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Third Row: Integrations --}}
        <div class="rounded-xl border bg-white dark:bg-gray-900 shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-9 h-9 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                    <x-heroicon-o-link class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                </div>
                <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Integrasi</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @php
                    $integrations = $report['integrations'] ?? [];
                @endphp
                {{-- SMS Gateway --}}
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-2 mb-2">
                        <x-heroicon-o-envelope class="w-4 h-4 text-gray-500" />
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">SMS Gateway</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-gray-500">Aktif: <strong class="text-gray-900 dark:text-gray-200">{{ $integrations['sms_gateway']['active_count'] ?? 0 }}</strong></span>
                        <span class="text-gray-500">Total: <strong class="text-gray-900 dark:text-gray-200">{{ $integrations['sms_gateway']['total_count'] ?? 0 }}</strong></span>
                    </div>
                </div>

                {{-- AI Providers --}}
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-2 mb-2">
                        <x-heroicon-o-sparkles class="w-4 h-4 text-gray-500" />
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">AI Providers</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-gray-500">Aktif: <strong class="text-gray-900 dark:text-gray-200">{{ $integrations['ai_providers']['active_count'] ?? 0 }}</strong></span>
                        <span class="text-gray-500">Total: <strong class="text-gray-900 dark:text-gray-200">{{ $integrations['ai_providers']['total_count'] ?? 0 }}</strong></span>
                    </div>
                </div>

                {{-- Webhooks --}}
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-2 mb-2">
                        <x-heroicon-o-arrow-path-rounded-square class="w-4 h-4 text-gray-500" />
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">Webhooks</span>
                    </div>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="text-gray-500">Aktif: <strong class="text-gray-900 dark:text-gray-200">{{ $integrations['webhooks']['active_count'] ?? 0 }}</strong></span>
                        <span class="text-gray-500">Total: <strong class="text-gray-900 dark:text-gray-200">{{ $integrations['webhooks']['total_count'] ?? 0 }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom: Security Overview --}}
        <div class="rounded-xl border bg-white dark:bg-gray-900 shadow-sm p-5 hover:shadow-md transition-shadow">
            <div class="flex items-center gap-2.5 mb-4">
                <div class="w-9 h-9 rounded-lg bg-cyan-100 dark:bg-cyan-900/30 flex items-center justify-center">
                    <x-heroicon-o-shield-check class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
                </div>
                <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">Keamanan</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @php
                    $security = $report['security'] ?? [];
                @endphp
                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 text-center">
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-gray-200">{{ number_format($security['users_count'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Total Pengguna</div>
                </div>
                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 text-center">
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-gray-200">{{ number_format($security['roles_count'] ?? 0) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Total Role</div>
                </div>
                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 text-center">
                    @php $isHttps = $security['https'] ?? false; @endphp
                    <span @class([
                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold',
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' => $isHttps,
                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' => !$isHttps,
                    ])>
                        {{ $isHttps ? 'HTTPS' : 'HTTP' }}
                    </span>
                    <div class="text-xs text-gray-500 mt-1">Protokol</div>
                </div>
                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 text-center">
                    <div class="text-sm font-mono font-medium text-gray-900 dark:text-gray-200 truncate">{{ $security['app_url'] ?? '-' }}</div>
                    <div class="text-xs text-gray-500 mt-1">APP URL</div>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
