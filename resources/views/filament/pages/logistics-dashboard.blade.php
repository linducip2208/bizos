<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Period Filter --}}
        <div class="flex justify-end">
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="period" class="text-sm">
                    <option value="today">Hari Ini</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="this_week">Minggu Ini</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="last_month">Bulan Lalu</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Pengiriman</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $performance['total_deliveries'] ?? 0 }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $activeVehicles }} kendaraan aktif</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Tepat Waktu</div>
                <div class="text-2xl font-bold text-green-600">{{ $performance['on_time_rate'] ?? 0 }}%</div>
                <div class="text-xs text-gray-400 mt-1">{{ $performance['on_time'] ?? 0 }} dari {{ $performance['total_deliveries'] ?? 0 }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Rata-rata Waktu Kirim</div>
                <div class="text-2xl font-bold text-blue-600">{{ $performance['avg_delivery_time_minutes'] ?? 0 }} mnt</div>
                <div class="text-xs text-gray-400 mt-1">Dalam perjalanan: {{ $performance['in_progress'] ?? 0 }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Gagal / Return</div>
                <div class="text-2xl font-bold text-red-600">{{ $performance['failed'] ?? 0 }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $performance['failed_rate'] ?? 0 }}% dari total</div>
            </div>
        </div>

        {{-- Status Distribution --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status Pengiriman</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                @php
                    $statusLabels = [
                        'pending' => ['label' => 'Menunggu', 'color' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'],
                        'picked' => ['label' => 'Diambil', 'color' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'],
                        'in_transit' => ['label' => 'Dalam Perjalanan', 'color' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300'],
                        'delivered' => ['label' => 'Terkirim', 'color' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'],
                        'failed' => ['label' => 'Gagal', 'color' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300'],
                        'returned' => ['label' => 'Return', 'color' => 'bg-orange-100 text-orange-700 dark:bg-orange-900 dark:text-orange-300'],
                    ];
                @endphp
                @foreach($statusLabels as $key => $info)
                    <div class="rounded-lg p-3 text-center {{ $info['color'] }}">
                        <div class="text-2xl font-bold">{{ $statusCounts[$key] ?? 0 }}</div>
                        <div class="text-sm">{{ $info['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Live Map --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Peta Posisi Armada (Live)</h3>
            <div id="fleet-map" style="height: 400px; border-radius: 12px;"></div>
        </div>

        {{-- Driver Ranking --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Peringkat Driver</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Total Kirim</th>
                            <th class="px-4 py-3">Terkirim</th>
                            <th class="px-4 py-3">Tepat Waktu</th>
                            <th class="px-4 py-3">OTIF Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($driverRanking as $i => $driver)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium">{{ $i + 1 }}</td>
                                <td class="px-4 py-3">{{ $driver['total_deliveries'] ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $driver['delivered'] ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $driver['on_time'] ?? 0 }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ ($driver['on_time_rate'] ?? 0) >= 90 ? 'bg-green-100 text-green-700' : (($driver['on_time_rate'] ?? 0) >= 70 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $driver['on_time_rate'] ?? 0 }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- GPS Track Log --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Log GPS Terbaru</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Kendaraan</th>
                            <th class="px-4 py-3">Driver</th>
                            <th class="px-4 py-3">Lat,Lng</th>
                            <th class="px-4 py-3">Kecepatan</th>
                            <th class="px-4 py-3">Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($gpsTracks, 0, 10) as $track)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium">{{ $track['vehicle'] }}</td>
                                <td class="px-4 py-3">{{ $track['driver'] }}</td>
                                <td class="px-4 py-3 font-mono text-xs">{{ number_format($track['lat'], 6) }}, {{ number_format($track['lng'], 6) }}</td>
                                <td class="px-4 py-3">{{ $track['speed'] ?? 0 }} km/h</td>
                                <td class="px-4 py-3 text-xs">{{ $track['recorded_at'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var map = L.map('fleet-map').setView([-6.2088, 106.8456], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(map);

            var points = @json($mapPoints);
            var bounds = [];
            points.forEach(function(p) {
                if (p.lat && p.lng) {
                    var marker = L.marker([p.lat, p.lng]).addTo(map);
                    marker.bindPopup('<b>' + p.vehicle + '</b><br>Kecepatan: ' + (p.speed ?? 0) + ' km/h');
                    bounds.push([p.lat, p.lng]);
                }
            });
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        });
    </script>
    @endpush
</x-filament-panels::page>
