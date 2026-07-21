<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Dashboard Hotel</h1>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-4">
            <p class="text-sm text-gray-500">Tingkat Okupansi</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $occupancyRate }}%</p>
            <p class="text-xs text-gray-400">{{ $occupiedRooms }} dari {{ $totalRooms }} kamar terisi</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-sm text-gray-500">Pendapatan Hari Ini</p>
            <p class="text-2xl font-bold text-green-600">Rp {{ number_format($revenueToday, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-sm text-gray-500">Pendapatan Bulan Ini</p>
            <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($revenueMtd, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border p-4">
            <p class="text-sm text-gray-500">Kamar Kotor</p>
            <p class="text-2xl font-bold text-amber-600">{{ $dirtyRooms }} Kamar</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl border p-6">
            <h2 class="text-lg font-semibold mb-4">Check-in Hari Ini</h2>
            @if(count($todayCheckIns) > 0)
                <div class="space-y-2">
                    @foreach($todayCheckIns as $checkin)
                        <div class="flex justify-between items-center p-2 bg-green-50 rounded-lg">
                            <div>
                                <p class="font-medium">{{ $checkin['guest_name'] }}</p>
                                <p class="text-xs text-gray-500">Kamar {{ $checkin['room']['room_number'] ?? '-' }}</p>
                            </div>
                            @php
                                $statusColors = [
                                    'pending' => 'bg-gray-100 text-gray-600',
                                    'confirmed' => 'bg-blue-100 text-blue-600',
                                    'checked_in' => 'bg-green-100 text-green-600',
                                    'checked_out' => 'bg-indigo-100 text-indigo-600',
                                    'cancelled' => 'bg-red-100 text-red-600',
                                    'no_show' => 'bg-yellow-100 text-yellow-600',
                                ];
                                $statusLabels = [
                                    'pending' => 'Pending',
                                    'confirmed' => 'Konfirmasi',
                                    'checked_in' => 'Sudah Check-in',
                                    'checked_out' => 'Check-out',
                                    'cancelled' => 'Batal',
                                    'no_show' => 'No Show',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $statusColors[$checkin['status']] ?? 'bg-gray-100' }}">
                                {{ $statusLabels[$checkin['status']] ?? $checkin['status'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-center py-4">Tidak ada check-in hari ini</p>
            @endif
        </div>

        <div class="bg-white rounded-xl border p-6">
            <h2 class="text-lg font-semibold mb-4">Check-out Hari Ini</h2>
            @if(count($todayCheckOuts) > 0)
                <div class="space-y-2">
                    @foreach($todayCheckOuts as $checkout)
                        <div class="flex justify-between items-center p-2 bg-amber-50 rounded-lg">
                            <div>
                                <p class="font-medium">{{ $checkout['guest_name'] }}</p>
                                <p class="text-xs text-gray-500">Kamar {{ $checkout['room']['room_number'] ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $statusColors[$checkout['status']] ?? 'bg-gray-100' }}">
                                {{ $statusLabels[$checkout['status']] ?? $checkout['status'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400 text-center py-4">Tidak ada check-out hari ini</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl border p-6">
        <h2 class="text-lg font-semibold mb-4">Status Kamar</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 lg:grid-cols-8 gap-2">
            @foreach($rooms as $room)
                @php
                    $colorMap = [
                        'available' => 'bg-green-100 border-green-300 text-green-800',
                        'occupied' => 'bg-red-100 border-red-300 text-red-800',
                        'dirty' => 'bg-yellow-100 border-yellow-300 text-yellow-800',
                        'maintenance' => 'bg-gray-200 border-gray-400 text-gray-600',
                        'reserved' => 'bg-blue-100 border-blue-300 text-blue-800',
                    ];
                    $labelMap = [
                        'available' => 'Tersedia',
                        'occupied' => 'Terisi',
                        'dirty' => 'Kotor',
                        'maintenance' => 'Perbaikan',
                        'reserved' => 'Dipesan',
                    ];
                    $colorClass = $colorMap[$room['status']] ?? 'bg-gray-100 border-gray-300';
                    $label = $labelMap[$room['status']] ?? $room['status'];
                @endphp
                <div class="border rounded-lg p-3 text-center {{ $colorClass }}">
                    <p class="font-bold text-lg">{{ $room['room_number'] }}</p>
                    <p class="text-xs">{{ $room['room_type'] }}</p>
                    <p class="text-xs font-semibold mt-1">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
