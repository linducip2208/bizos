<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Dashboard Properti</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-4">
            <p class="text-sm text-gray-500">Tingkat Okupansi</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $occupancyRate['occupancy_rate'] ?? 0 }}%</p>
            <p class="text-xs text-gray-400">{{ $occupancyRate['occupied_units'] ?? 0 }} dari {{ $occupancyRate['total_units'] ?? 0 }} unit</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-sm text-gray-500">Pendapatan Bulan Ini</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($revenueThisMonth, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-sm text-gray-500">Permintaan Perbaikan</p>
            <p class="text-2xl font-bold text-amber-600">{{ $pendingMaintenanceCount }}</p>
            <p class="text-xs text-gray-400">Pending</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-sm text-gray-500">Kontrak Segera Habis</p>
            <p class="text-2xl font-bold text-red-600">{{ count($expiringContracts) }}</p>
            <p class="text-xs text-gray-400">Dalam 90 hari</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl border p-6">
            <h2 class="text-lg font-semibold mb-4">Kontrak Segera Habis (90 Hari)</h2>
            @if(count($expiringContracts) > 0)
                <div class="space-y-2">
                    @foreach($expiringContracts as $contract)
                        @php
                            $daysLeft = \Carbon\Carbon::parse($contract['end_date'])->diffInDays(now());
                            $urgentClass = $daysLeft <= 30 ? 'bg-red-50 border-red-200' : ($daysLeft <= 60 ? 'bg-amber-50 border-amber-200' : 'bg-blue-50 border-blue-200');
                        @endphp
                        <div class="border rounded-lg p-3 {{ $urgentClass }}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="font-medium">{{ $contract['contract_number'] ?? '-' }}</p>
                                    <p class="text-xs text-gray-500">
                                        Unit {{ $contract['property_unit']['unit_number'] ?? '-' }}
                                        - {{ $contract['client']['name'] ?? '-' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Sisa</p>
                                    <p class="font-bold {{ $daysLeft <= 30 ? 'text-red-600' : 'text-gray-700' }}">
                                        {{ (int) $daysLeft }} Hari
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-center py-4">Tidak ada kontrak yang segera habis</p>
            @endif
        </div>

        <div class="bg-white rounded-xl border p-6">
            <h2 class="text-lg font-semibold mb-4">Okupansi per Tipe</h2>
            @if(!empty($occupancyRate['by_type'] ?? []))
                <div class="space-y-3">
                    @foreach($occupancyRate['by_type'] as $type)
                        @php
                            $typeLabels = [
                                'apartment' => 'Apartemen',
                                'house' => 'Rumah',
                                'shop' => 'Ruko',
                                'office' => 'Kantor',
                                'warehouse' => 'Gudang',
                            ];
                            $label = $typeLabels[$type['type']] ?? $type['type'];
                        @endphp
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium">{{ $label }}</span>
                                <span class="text-sm text-gray-500">{{ $type['occupied'] }}/{{ $type['total'] }} ({{ $type['rate'] }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $type['rate'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-center py-4">Tidak ada data okupansi</p>
            @endif
        </div>
    </div>
</div>
