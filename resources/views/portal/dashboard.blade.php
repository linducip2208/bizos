@extends('portal.layout')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Dashboard</h1><p class="text-sm text-gray-500 mt-1">Selamat datang, {{ Auth::user()->name }}</p></div>

    @if (session('success'))<div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ session('error') }}</div>@endif

    @if ($employee)
    {{-- Attendance Status Card --}}
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Absensi Hari Ini</h3>
        @if ($todayAttendance && $todayAttendance->clock_in)
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div>
                <p class="text-sm text-gray-500">Clock-in: <strong class="text-gray-900">{{ $todayAttendance->clock_in->format('H:i') }}</strong>
                    @if ($todayAttendance->status === 'late')<span class="text-amber-600 text-xs"> (Terlambat {{ $todayAttendance->late_minutes }} mnt)</span>@endif
                </p>
                @if ($todayAttendance->clock_out)
                <p class="text-sm text-gray-500">Clock-out: <strong class="text-gray-900">{{ $todayAttendance->clock_out->format('H:i') }}</strong></p>
                @else
                <form action="{{ route('portal.attendance.clock-out') }}" method="POST" enctype="multipart/form-data" class="mt-1 inline-flex gap-1">
                    @csrf
                    <input type="file" name="photo" accept="image/*" capture="user" class="text-xs w-24">
                    <button type="submit" class="px-2 py-1 bg-red-100 text-red-700 text-xs rounded-lg hover:bg-red-200 transition">Clock Out</button>
                </form>
                @endif
                @if ($todayAttendance->work_type)<span class="ml-2 text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">{{ strtoupper($todayAttendance->work_type) }}</span>@endif
            </div>
        </div>
        @else
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
            <div>
                <p class="text-sm text-amber-700 font-medium">Belum clock-in hari ini</p>
                <form action="{{ route('portal.attendance.clock-in') }}" method="POST" enctype="multipart/form-data" class="mt-1 inline-flex gap-1">
                    @csrf
                    <input type="file" name="photo" accept="image/*" capture="user" class="text-xs w-24">
                    <button type="submit" class="px-2 py-1 bg-emerald-100 text-emerald-700 text-xs rounded-lg hover:bg-emerald-200 transition">Clock In</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('portal.leave.index') }}" class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Cuti Tahunan</span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></div>
            @php $annual = $leaveBalances->firstWhere('leaveType.is_annual', true); @endphp
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $annual->remaining_days ?? 0 }} <span class="text-xs text-gray-400 font-normal">/ {{ $annual->total_days ?? 12 }} hari</span></p>
        </a>
        <a href="{{ route('portal.leave.index') }}" class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between"><span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Cuti Sakit</span><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></div>
            @php $sick = $leaveBalances->where('leaveType.code', 'sick')->first(); @endphp
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $sick->remaining_days ?? 0 }} <span class="text-xs text-gray-400 font-normal">/ {{ $sick->total_days ?? 0 }} hari</span></p>
        </a>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Approval Menunggu</span>
            <p class="text-2xl font-bold {{ $pendingApprovals > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ $pendingApprovals }}</p>
            @if ($pendingApprovals > 0)<p class="text-xs text-amber-600 mt-0.5">{{ $pendingLeaves }} cuti, {{ $pendingOvertimes }} lembur, {{ $pendingReimbursements }} reimbursement</p>@endif
        </div>
        <a href="{{ route('portal.overtime.index') }}" class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm hover:shadow-md transition">
            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Lembur Bulan Ini</span>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ round($monthlyAttendance->sum('overtime_minutes') / 60, 1) }} <span class="text-xs text-gray-400 font-normal">jam</span></p>
        </a>
    </div>

    {{-- Monthly Attendance + Birthdays --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Ringkasan Absensi Bulan Ini</h3>
            @php
                $presentDays = $monthlyAttendance->whereIn('status', ['present', 'late'])->count();
                $lateDays = $monthlyAttendance->where('status', 'late')->count();
                $absentDays = $monthlyAttendance->filter(fn($a) => !$a->clock_in)->count();
            @endphp
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-3 bg-emerald-50 rounded-lg"><p class="text-lg font-bold text-emerald-700">{{ $presentDays }}</p><p class="text-xs text-emerald-600">Hadir</p></div>
                <div class="text-center p-3 bg-amber-50 rounded-lg"><p class="text-lg font-bold text-amber-700">{{ $lateDays }}</p><p class="text-xs text-amber-600">Terlambat</p></div>
                <div class="text-center p-3 bg-red-50 rounded-lg"><p class="text-lg font-bold text-red-700">{{ $absentDays }}</p><p class="text-xs text-red-600">Tidak Hadir</p></div>
            </div>
        </div>

        @if ($upcomingBirthdays->isNotEmpty())
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Ulang Tahun Mendatang</h3>
            <div class="space-y-2">
                @foreach ($upcomingBirthdays as $emp)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-pink-100 flex items-center justify-center text-xs font-bold text-pink-600">{{ strtoupper(substr($emp->first_name, 0, 1)) }}</div>
                    <div class="flex-1"><p class="text-sm font-medium text-gray-900">{{ $emp->first_name }} {{ $emp->last_name }}</p><p class="text-xs text-gray-400">{{ $emp->department?->name ?? '-' }}</p></div>
                    <p class="text-xs text-pink-600 font-medium">{{ $emp->birth_date->format('d M') }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Announcements --}}
    @if ($announcements->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Pengumuman</h3>
        <div class="space-y-2">
            @foreach ($announcements as $ann)
            @php $pc = ['low'=>'bg-blue-100 text-blue-700','medium'=>'bg-amber-100 text-amber-700','high'=>'bg-red-100 text-red-700','urgent'=>'bg-red-200 text-red-800']; @endphp
            <div class="flex items-start gap-3 p-2 hover:bg-gray-50 rounded-lg transition">
                <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-semibold {{ $pc[$ann->priority] ?? 'bg-gray-100 text-gray-700' }} mt-0.5">{{ strtoupper($ann->priority) }}</span>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $ann->title }}</p>
                    <p class="text-xs text-gray-400">{{ $ann->published_at?->diffForHumans() ?? '-' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent Leaves --}}
    @if ($recentLeaves->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
        <div class="flex items-center justify-between mb-3"><h3 class="text-sm font-semibold text-gray-700">Pengajuan Cuti Terbaru</h3><a href="{{ route('portal.leave.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800">Lihat Semua</a></div>
        <div class="space-y-2">@foreach($recentLeaves as $l)<div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg"><div><p class="text-xs font-medium text-gray-900">{{ $l->leaveType?->name ?? '-' }}</p><p class="text-xs text-gray-400">{{ $l->start_date->format('d M') }} - {{ $l->end_date->format('d M Y') }} ({{ $l->total_days }} hari)</p></div>@php $sc=['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700']; $sl=['pending'=>'Menunggu','approved'=>'Disetujui','rejected'=>'Ditolak']; @endphp<span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $sc[$l->status] ?? '' }}">{{ $sl[$l->status] ?? $l->status }}</span></div>@endforeach</div>
    </div>
    @endif
    @endif

    {{-- Invoice Section --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-semibold text-gray-900">Invoice</h2></div>
        <div class="grid grid-cols-3 gap-4 p-6">
            <div class="p-4 bg-gray-50 rounded-lg"><p class="text-xs text-gray-400 uppercase tracking-wider">Total Invoice</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($dashboardInvoices->count() ?? 0) }}</p></div>
            <div class="p-4 bg-emerald-50 rounded-lg"><p class="text-xs text-gray-400 uppercase tracking-wider">Total Dibayar</p><p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalPaid ?? 0, 0, ',', '.') }}</p></div>
            <div class="p-4 bg-red-50 rounded-lg"><p class="text-xs text-gray-400 uppercase tracking-wider">Tunggakan</p><p class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format($totalOutstanding ?? 0, 0, ',', '.') }}</p></div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-left"><th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Nomor</th><th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th><th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Jatuh Tempo</th><th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Total</th><th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Sisa</th><th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th><th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Aksi</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($dashboardInvoices as $inv)
                    @php $sc = ['draft'=>'bg-gray-100 text-gray-700','sent'=>'bg-blue-100 text-blue-700','partial'=>'bg-amber-100 text-amber-700','paid'=>'bg-emerald-100 text-emerald-700','overdue'=>'bg-red-100 text-red-700']; $sl=['draft'=>'Draft','sent'=>'Terkirim','partial'=>'Sebagian','paid'=>'Lunas','overdue'=>'Terlambat']; @endphp
                    <tr class="hover:bg-gray-50"><td class="px-6 py-3 font-mono font-medium">{{ $inv->invoice_number }}</td><td class="px-6 py-3 text-gray-600">{{ $inv->invoice_date->format('d M Y') }}</td><td class="px-6 py-3 text-gray-600">{{ $inv->due_date->format('d M Y') }}</td><td class="px-6 py-3 text-right font-mono">Rp {{ number_format($inv->total, 0, ',', '.') }}</td><td class="px-6 py-3 text-right font-mono">Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}</td><td class="px-6 py-3"><span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $sc[$inv->status] ?? '' }}">{{ $sl[$inv->status] ?? $inv->status }}</span></td><td class="px-6 py-3 text-center"><a href="{{ route('portal.invoice-detail', $inv->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Detail</a></td></tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">Belum ada invoice.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
