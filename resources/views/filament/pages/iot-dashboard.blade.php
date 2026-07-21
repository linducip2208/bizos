<x-filament-panels::page>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @endpush

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Dashboard IoT & Sensor</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Monitoring real-time seluruh perangkat IoT
                </p>
            </div>
            <x-filament::button wire:click="loadData" color="gray" size="sm" outlined>
                Refresh Data
            </x-filament::button>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                <p class="text-xs text-gray-500">Total Perangkat</p>
                <p class="text-2xl font-bold">{{ $totalDevices }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                <p class="text-xs text-gray-500">Online</p>
                <p class="text-2xl font-bold text-green-600">{{ $onlineDevices }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                <p class="text-xs text-gray-500">Offline</p>
                <p class="text-2xl font-bold text-red-600">{{ $offlineDevicesCount }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                <p class="text-xs text-gray-500">Alert Aktif</p>
                <p class="text-2xl font-bold text-yellow-600">{{ $activeAlertsCount }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                <p class="text-xs text-gray-500">Alert Kritis</p>
                <p class="text-2xl font-bold text-red-600">{{ $criticalAlertsCount }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                <p class="text-xs text-gray-500">Konsumsi Bulan Ini</p>
                <p class="text-xl font-bold text-indigo-600">{{ number_format($energyConsumption['total_kwh'] ?? 0, 0, ',', '.') }} kWh</p>
            </div>
        </div>

        {{-- Device Status Grid --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold">Status Perangkat</h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($devices as $device)
                        <div class="border rounded-lg p-3 {{ $device['status'] === 'online' ? 'border-green-200 bg-green-50 dark:bg-green-900/10' : 'border-red-200 bg-red-50 dark:bg-red-900/10' }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-mono text-gray-500">{{ str_replace('sensor_', '', $device['type']) }}</span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $device['status'] === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $device['status'] === 'online' ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                            <p class="font-semibold text-sm mb-1">{{ $device['name'] }}</p>
                            <p class="text-xs text-gray-500 mb-2">{{ $device['location'] ?? '-' }}</p>
                            <div class="space-y-1 text-xs">
                                @if($device['temperature'] !== null)
                                    <div class="flex justify-between">
                                        <span>Suhu</span>
                                        <span class="font-mono {{ $device['temperature'] > 35 ? 'text-red-600' : '' }}">{{ $device['temperature'] }}&deg;C</span>
                                    </div>
                                @endif
                                @if($device['humidity'] !== null)
                                    <div class="flex justify-between">
                                        <span>Kelembaban</span>
                                        <span class="font-mono">{{ $device['humidity'] }}%</span>
                                    </div>
                                @endif
                                @if($device['vibration'] !== null)
                                    <div class="flex justify-between">
                                        <span>Getaran</span>
                                        <span class="font-mono">{{ $device['vibration'] }} mm/s</span>
                                    </div>
                                @endif
                                @if($device['battery_level'] !== null)
                                    <div class="flex justify-between">
                                        <span>Baterai</span>
                                        <span class="font-mono {{ $device['battery_level'] < 20 ? 'text-red-600' : '' }}">{{ $device['battery_level'] }}%</span>
                                    </div>
                                @endif
                                @if($device['risk_level'] !== 'normal' && $device['risk_level'] !== 'unknown')
                                    <div class="mt-1 pt-1 border-t">
                                        <span class="font-semibold">Risiko:</span>
                                        <span class="px-1.5 py-0.5 rounded text-xs font-bold
                                            {{ $device['risk_level'] === 'high' ? 'bg-red-100 text-red-800' : ($device['risk_level'] === 'medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ $device['failure_probability'] }}% - {{ $device['risk_level'] === 'high' ? 'Tinggi' : ($device['risk_level'] === 'medium' ? 'Sedang' : 'Rendah') }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <p class="text-xs text-gray-400 mt-2">{{ $device['last_seen_at'] ?? 'Tidak ada data' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Active Alerts --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border">
            <div class="p-4 border-b flex items-center justify-between">
                <h2 class="text-lg font-semibold">Alert Aktif</h2>
                <span class="text-sm text-gray-500">{{ count($activeAlerts) }} alert</span>
            </div>
            <div class="p-4">
                @if(count($activeAlerts) > 0)
                    <div class="space-y-2">
                        @foreach($activeAlerts as $alert)
                            <div class="flex items-start gap-3 p-3 rounded-lg
                                {{ $alert['severity'] === 'critical' ? 'bg-red-50 dark:bg-red-900/10 border border-red-200' : ($alert['severity'] === 'warning' ? 'bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200' : 'bg-blue-50 dark:bg-blue-900/10 border border-blue-200') }}">
                                <div class="mt-0.5">
                                    @if($alert['severity'] === 'critical')
                                        <x-heroicon-o-x-circle class="w-5 h-5 text-red-600" />
                                    @elseif($alert['severity'] === 'warning')
                                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-600" />
                                    @else
                                        <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600" />
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold">{{ $alert['title'] }}</span>
                                        <span class="px-1.5 py-0.5 rounded text-xs font-medium
                                            {{ $alert['type'] === 'predictive_maintenance' ? 'bg-purple-100 text-purple-800' : ($alert['type'] === 'anomaly' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-700') }}">
                                            {{ $alert['type'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-0.5">{{ $alert['message'] }}</p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $alert['device']['name'] ?? '-' }} &middot; {{ \Carbon\Carbon::parse($alert['created_at'])->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 text-center py-8">Tidak ada alert aktif</p>
                @endif
            </div>
        </div>

        {{-- Energy Consumption --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold">Konsumsi Energi Bulanan</h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div class="text-center p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                        <p class="text-xs text-gray-500">Total kWh</p>
                        <p class="text-xl font-bold text-indigo-700">{{ number_format($energyConsumption['total_kwh'] ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                        <p class="text-xs text-gray-500">Estimasi Biaya</p>
                        <p class="text-xl font-bold text-green-700">{{ $energyConsumption['cost_estimate_formatted'] ?? 'Rp 0' }}</p>
                    </div>
                    <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                        <p class="text-xs text-gray-500">Peak Demand</p>
                        <p class="text-xl font-bold text-yellow-700">{{ number_format($energyConsumption['peak_demand_kwh'] ?? 0, 0, ',', '.') }} kWh</p>
                    </div>
                    <div class="text-center p-3 bg-gray-50 dark:bg-gray-900/20 rounded-lg">
                        <p class="text-xs text-gray-500">Emisi Karbon</p>
                        <p class="text-xl font-bold text-gray-700">{{ number_format($energyConsumption['carbon_kg'] ?? 0, 0, ',', '.') }} kg CO&#8322;</p>
                    </div>
                </div>
                @if(isset($energyConsumption['trend_percent']))
                    <p class="text-sm text-center {{ ($energyConsumption['trend_percent'] ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ ($energyConsumption['trend_percent'] ?? 0) > 0 ? '+' : '' }}{{ $energyConsumption['trend_percent'] ?? 0 }}% vs periode sebelumnya
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
