@extends('portal.layout')

@section('title', 'Absensi')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Absensi</h1>
            <p class="text-sm text-gray-500 mt-1">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}</p>
        </div>
        <div>
            <form method="GET" class="flex items-center gap-2">
                <input type="month" name="month" value="{{ $month }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                    onchange="this.form.submit()">
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <form action="{{ route('portal.attendance.clock-in') }}" method="POST" enctype="multipart/form-data" id="clockInForm">
            @csrf
            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
                    Clock In
                </h2>
                @if ($todayAttendance && $todayAttendance->clock_in)
                    <div class="p-3 bg-emerald-50 rounded-lg border border-emerald-200 text-sm text-emerald-700">
                        Sudah clock-in: <strong>{{ $todayAttendance->clock_in->format('d M Y H:i') }}</strong>
                        @if ($todayAttendance->clock_out)
                            <br>Sudah clock-out: <strong>{{ $todayAttendance->clock_out->format('H:i') }}</strong>
                        @endif
                    </div>
                @else
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Lokasi</label>
                            <input type="text" id="clockInLocation" readonly
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs bg-gray-50 text-gray-500">
                            <input type="hidden" name="latitude" id="clockInLat">
                            <input type="hidden" name="longitude" id="clockInLng">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Foto Selfie</label>
                            <input type="file" name="photo" accept="image/*" capture="user"
                                class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
                            <input type="text" name="notes" maxlength="500"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                placeholder="Opsional">
                        </div>
                        <button type="submit" id="clockInBtn"
                            class="w-full px-4 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition cursor-pointer disabled:opacity-50">
                            Clock In Sekarang
                        </button>
                    </div>
                @endif
            </div>
        </form>

        <form action="{{ route('portal.attendance.clock-out') }}" method="POST" enctype="multipart/form-data" id="clockOutForm">
            @csrf
            <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Clock Out
                </h2>
                @if (!$todayAttendance || !$todayAttendance->clock_in)
                    <div class="p-3 bg-amber-50 rounded-lg border border-amber-200 text-sm text-amber-700">
                        Silakan clock-in terlebih dahulu.
                    </div>
                @elseif ($todayAttendance->clock_out)
                    <div class="p-3 bg-emerald-50 rounded-lg border border-emerald-200 text-sm text-emerald-700">
                        Sudah clock-out: <strong>{{ $todayAttendance->clock_out->format('d M Y H:i') }}</strong>
                    </div>
                @else
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Lokasi</label>
                            <input type="text" id="clockOutLocation" readonly
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs bg-gray-50 text-gray-500">
                            <input type="hidden" name="latitude" id="clockOutLat">
                            <input type="hidden" name="longitude" id="clockOutLng">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Foto Selfie</label>
                            <input type="file" name="photo" accept="image/*" capture="user"
                                class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
                            <input type="text" name="notes" maxlength="500"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                placeholder="Opsional">
                        </div>
                        <button type="submit"
                            class="w-full px-4 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition cursor-pointer">
                            Clock Out Sekarang
                        </button>
                    </div>
                @endif
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Riwayat Absensi Bulan Ini</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Shift</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Clock In</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Clock Out</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Terlambat</th>
                        <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Lembur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($attendances as $att)
                    @php
                        $dayStatusLabels = [
                            'present' => ['bg-emerald-100 text-emerald-700', 'Hadir'],
                            'late' => ['bg-amber-100 text-amber-700', 'Terlambat'],
                            'absent' => ['bg-red-100 text-red-700', 'Tidak Hadir'],
                            'half_day' => ['bg-orange-100 text-orange-700', 'Setengah Hari'],
                            'holiday' => ['bg-blue-100 text-blue-700', 'Libur'],
                            'leave' => ['bg-purple-100 text-purple-700', 'Cuti'],
                        ];
                        $ds = $dayStatusLabels[$att->status] ?? ['bg-gray-100 text-gray-700', $att->status];
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $att->date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $att->shift?->name ?? '-' }}</td>
                        <td class="px-4 py-3 font-mono text-gray-600">{{ $att->clock_in?->format('H:i') ?? '-' }}</td>
                        <td class="px-4 py-3 font-mono text-gray-600">{{ $att->clock_out?->format('H:i') ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $ds[0] }}">{{ $ds[1] }}</span></td>
                        <td class="px-4 py-3 text-gray-600">{{ $att->late_minutes ? $att->late_minutes . ' mnt' : '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $att->overtime_minutes ? $att->overtime_minutes . ' mnt' : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">Belum ada data absensi bulan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            document.getElementById('clockInLat').value = pos.coords.latitude;
            document.getElementById('clockInLng').value = pos.coords.longitude;
            document.getElementById('clockInLocation').value = pos.coords.latitude.toFixed(6) + ', ' + pos.coords.longitude.toFixed(6);
            document.getElementById('clockOutLat').value = pos.coords.latitude;
            document.getElementById('clockOutLng').value = pos.coords.longitude;
            document.getElementById('clockOutLocation').value = pos.coords.latitude.toFixed(6) + ', ' + pos.coords.longitude.toFixed(6);
        }, function(err) {
            document.getElementById('clockInLocation').value = 'GPS tidak tersedia';
            document.getElementById('clockOutLocation').value = 'GPS tidak tersedia';
        });
    }
});
</script>
@endsection
