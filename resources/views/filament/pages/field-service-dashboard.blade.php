<x-filament-panels::page>
<div x-data="{ activeTab: 'overview' }" class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Field Service</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Overview operasional field service hari ini</p>
        </div>
        <div class="flex gap-2">
            <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300'" class="px-4 py-2 rounded-lg text-sm font-medium transition">Overview</button>
            <button @click="activeTab = 'map'" :class="activeTab === 'map' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300'" class="px-4 py-2 rounded-lg text-sm font-medium transition">Peta Teknisi</button>
            <button @click="activeTab = 'leaderboard'" :class="activeTab === 'leaderboard' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300'" class="px-4 py-2 rounded-lg text-sm font-medium transition">Leaderboard</button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Kontrak Aktif</span>
                <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $stats['total_contracts'] }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">WO Terbuka</span>
                <div class="w-10 h-10 bg-amber-100 dark:bg-amber-900 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $stats['open_work_orders'] }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Selesai Hari Ini</span>
                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $stats['completed_today'] }}</div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">First-Time Fix Rate</span>
                <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-600 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ $ftfrRate }}%</div>
        </div>
    </div>

    {{-- Tab: Overview --}}
    <div x-show="activeTab === 'overview'">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Work Order Hari Ini</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-left">
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">No WO</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Klien</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teknisi</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Prioritas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($todayOrders as $wo)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-6 py-3 text-sm text-gray-900 dark:text-white font-mono">{{ $wo['wo_number'] }}</td>
                            <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $wo['client']['name'] ?? '-' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $wo['technician']['first_name'] ?? '-' }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                    @php $statusColor = match($wo['status']) {
                                        'open' => 'bg-gray-100 text-gray-700',
                                        'assigned' => 'bg-blue-100 text-blue-700',
                                        'en_route' => 'bg-yellow-100 text-yellow-700',
                                        'in_progress' => 'bg-indigo-100 text-indigo-700',
                                        'completed', 'verified' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    }; @endphp
                                    {{ $statusColor }}
                                ">
                                    @php
                                        echo match($wo['status']) {
                                            'open' => 'Open',
                                            'assigned' => 'Ditugaskan',
                                            'en_route' => 'Di Perjalanan',
                                            'in_progress' => 'Dalam Pengerjaan',
                                            'completed' => 'Selesai',
                                            'verified' => 'Terverifikasi',
                                            'cancelled' => 'Dibatalkan',
                                            default => $wo['status'],
                                        };
                                    @endphp
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($wo['priority']) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Tidak ada work order hari ini</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Tab: Map --}}
    <div x-show="activeTab === 'map'" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Lokasi Teknisi (Live)</h3>
        </div>
        <div id="map-container" style="height: 500px;" class="relative">
            @if(empty($vanLocations))
                <div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                    <div class="text-center">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p>Belum ada data lokasi teknisi</p>
                    </div>
                </div>
            @else
                <div id="fieldservice-map" style="width: 100%; height: 100%;"></div>
            @endif
        </div>
        @if(!empty($vanLocations))
        <div class="p-4 space-y-2 max-h-48 overflow-y-auto">
            @foreach($vanLocations as $van)
            <div class="flex items-center justify-between text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse"></span>
                    <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $van['technician'] }}</span>
                    <span class="text-gray-400 text-xs">{{ $van['license_plate'] }}</span>
                </div>
                <span class="text-gray-400 text-xs">{{ $van['updated'] ?? '-' }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Tab: Leaderboard --}}
    <div x-show="activeTab === 'leaderboard'">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Leaderboard Teknisi</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-left">
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teknisi</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">WO Selesai</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rating</th>
                            <th class="px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($technicianLeaderboard as $i => $tech)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                                    {{ $i === 0 ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $i === 1 ? 'bg-gray-200 text-gray-600' : '' }}
                                    {{ $i === 2 ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $i > 2 ? 'bg-gray-50 text-gray-500' : '' }}
                                ">{{ $i + 1 }}</span>
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $tech['name'] }}</td>
                            <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $tech['completed'] }}</td>
                            <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">
                                <div class="flex items-center gap-1">
                                    <span class="text-amber-500">&#9733;</span>
                                    {{ number_format($tech['rating'], 1) }}
                                </div>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-700 dark:text-gray-300">Rp {{ number_format($tech['revenue'], 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Belum ada data teknisi</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Leaflet Map --}}
@if(!empty($vanLocations))
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var mapEl = document.getElementById('fieldservice-map');
    if (!mapEl) return;

    var vanData = @json($vanLocations);
    var map = L.map('fieldservice-map').setView([-6.2088, 106.8456], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var markers = [];
    vanData.forEach(function(van) {
        if (van.lat && van.lng) {
            var marker = L.marker([parseFloat(van.lat), parseFloat(van.lng)])
                .addTo(map)
                .bindPopup('<strong>' + van.technician + '</strong><br>' + van.license_plate + '<br><small>' + (van.updated || '') + '</small>');
            markers.push(marker);
        }
    });

    if (markers.length > 0) {
        var group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
});
</script>
@endif
</x-filament-panels::page>
