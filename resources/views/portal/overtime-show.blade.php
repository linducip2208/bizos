@extends('portal.layout')

@section('title', 'Detail Lembur')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3"><a href="{{ route('portal.overtime.index') }}" class="text-gray-400 hover:text-gray-600"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a><h1 class="text-2xl font-bold text-gray-900">Detail Lembur</h1></div>
    @php $sc = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700']; $sl = ['pending'=>'Menunggu','approved'=>'Disetujui','rejected'=>'Ditolak']; @endphp
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"><h2 class="text-base font-semibold text-gray-900">Informasi Lembur</h2><span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $sc[$overtime->status] ?? '' }}">{{ $sl[$overtime->status] ?? $overtime->status }}</span></div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div><p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tanggal</p><p class="text-sm font-medium text-gray-900">{{ $overtime->date->format('d M Y') }}</p></div>
                <div><p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Rate</p><p class="text-sm font-medium text-gray-900">{{ $overtime->rate_multiplier }}x</p></div>
                <div><p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Jam Mulai</p><p class="text-sm font-medium text-gray-900">{{ $overtime->start_time->format('H:i') }}</p></div>
                <div><p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Jam Selesai</p><p class="text-sm font-medium text-gray-900">{{ $overtime->end_time->format('H:i') }}</p></div>
                <div><p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Durasi</p><p class="text-sm font-medium text-gray-900">{{ floor($overtime->duration_minutes/60) }} jam {{ $overtime->duration_minutes%60 }} menit</p></div>
            </div>
            <div><p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Alasan</p><p class="text-sm text-gray-700">{{ $overtime->reason }}</p></div>
            @if ($overtime->approvedBy)<div><p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Disetujui Oleh</p><p class="text-sm text-gray-700">{{ $overtime->approvedBy->first_name }} {{ $overtime->approvedBy->last_name }} @if($overtime->approved_at)<span class="text-xs text-gray-400">({{ $overtime->approved_at->format('d M Y H:i') }})</span>@endif</p></div>@endif
        </div>
    </div>
</div>
@endsection
