@extends('filament::layouts.base')
@section('content')
<div class="fi-page">
    <div class="px-6 py-4">
        <h1 class="text-2xl font-bold mb-6">Hub Integrasi</h1>

        {{-- Connector Catalog --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            @foreach($connectors as $connector)
            <div class="fi-section rounded-xl overflow-hidden border
                @if(($connector['installed'] ?? false) && ($connector['status'] ?? '') === 'connected') border-emerald-200 dark:border-emerald-800
                @elseif(($connector['status'] ?? '') === 'error') border-red-200 dark:border-red-800
                @else border-stone-200 dark:border-stone-700
                @endif">
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">
                                @switch($connector['type'])
                                    @case('jurnal_id') 📊 @break
                                    @case('xero') 📈 @break
                                    @case('google_workspace') 🔴 @break
                                    @case('microsoft_365') 🟦 @break
                                    @case('open_banking') 🏦 @break
                                    @case('djp') 📄 @break
                                    @default 🔌
                                @endswitch
                            </span>
                            <div>
                                <h3 class="font-semibold">{{ $connector['name'] }}</h3>
                                <p class="text-xs text-stone-400">{{ $connector['description'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 text-xs rounded-full
                            @if(($connector['status'] ?? '') === 'connected') bg-emerald-100 text-emerald-700
                            @elseif(($connector['status'] ?? '') === 'error') bg-red-100 text-red-700
                            @else bg-stone-100 text-stone-500
                            @endif">
                            @if(($connector['status'] ?? '') === 'connected')
                                Terhubung
                            @elseif(($connector['status'] ?? '') === 'error')
                                Error
                            @elseif(($connector['installed'] ?? false))
                                Terputus
                            @else
                                Belum Terpasang
                            @endif
                        </span>
                        @if($connector['last_sync_at'] ?? false)
                        <span class="text-xs text-stone-400">Sync: {{ $connector['last_sync_at'] }}</span>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach(($connector['entities'] ?? []) as $entity)
                        <span class="px-2 py-0.5 text-xs rounded bg-stone-100 dark:bg-stone-700 text-stone-600 dark:text-stone-300">{{ $entity }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="bg-stone-50 dark:bg-stone-800 px-5 py-3 border-t border-stone-100 dark:border-stone-700">
                    <a href="{{ url('/admin/integration-connectors') }}" class="text-xs text-primary-600 font-medium hover:underline">
                        Kelola Konektor →
                    </a>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Sync Logs --}}
        <div class="fi-section rounded-xl p-6 mb-6">
            <h2 class="text-lg font-bold mb-4">Log Sinkronisasi Terbaru</h2>
            @if(count($syncLogs))
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-stone-500 border-b border-stone-200 dark:border-stone-700">
                            <th class="pb-2 font-medium">Konektor</th>
                            <th class="pb-2 font-medium">Entitas</th>
                            <th class="pb-2 font-medium">Arah</th>
                            <th class="pb-2 font-medium">Status</th>
                            <th class="pb-2 font-medium">Diproses</th>
                            <th class="pb-2 font-medium">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($syncLogs, 0, 20) as $log)
                        <tr class="border-b border-stone-100 dark:border-stone-800">
                            <td class="py-2">{{ $log['connector_type'] ?? '-' }}</td>
                            <td class="py-2">{{ $log['entity'] ?? '-' }}</td>
                            <td class="py-2">
                                @if(($log['direction'] ?? '') === 'inbound') ↓ Masuk
                                @elseif(($log['direction'] ?? '') === 'outbound') ↑ Keluar
                                @elseif(($log['direction'] ?? '') === 'bidirectional') ↕ Dua Arah
                                @else {{ $log['direction'] ?? '-' }}
                                @endif
                            </td>
                            <td class="py-2">
                                <span class="px-2 py-0.5 text-xs rounded-full
                                    @if(($log['status'] ?? '') === 'success') bg-emerald-100 text-emerald-700
                                    @elseif(($log['status'] ?? '') === 'partial') bg-amber-100 text-amber-700
                                    @elseif(($log['status'] ?? '') === 'failed') bg-red-100 text-red-700
                                    @else bg-stone-100 text-stone-500
                                    @endif">
                                    {{ $log['status'] ?? '-' }}
                                </span>
                            </td>
                            <td class="py-2">{{ $log['records_processed'] ?? 0 }}</td>
                            <td class="py-2 text-xs text-stone-400">{{ $log['started_at'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-stone-400 text-sm">Belum ada log sinkronisasi.</p>
            @endif
        </div>

        {{-- Quick Actions --}}
        <div class="fi-section rounded-xl p-6">
            <h2 class="text-lg font-bold mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <a href="{{ url('/admin/integration-connectors/create') }}" class="flex items-center justify-center gap-2 p-4 rounded-xl bg-stone-50 dark:bg-stone-800 hover:bg-stone-100 dark:hover:bg-stone-700 transition text-sm font-medium">
                    + Tambah Konektor
                </a>
                <a href="{{ url('/admin/virtual-accounts/create') }}" class="flex items-center justify-center gap-2 p-4 rounded-xl bg-stone-50 dark:bg-stone-800 hover:bg-stone-100 dark:hover:bg-stone-700 transition text-sm font-medium">
                    + Virtual Account Baru
                </a>
                <a href="{{ url('/admin/integration-connectors') }}" class="flex items-center justify-center gap-2 p-4 rounded-xl bg-stone-50 dark:bg-stone-800 hover:bg-stone-100 dark:hover:bg-stone-700 transition text-sm font-medium">
                    Lihat Semua Konektor
                </a>
                <a href="{{ url('/admin/virtual-accounts') }}" class="flex items-center justify-center gap-2 p-4 rounded-xl bg-stone-50 dark:bg-stone-800 hover:bg-stone-100 dark:hover:bg-stone-700 transition text-sm font-medium">
                    Lihat Virtual Account
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
