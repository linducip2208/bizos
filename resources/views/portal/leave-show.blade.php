@extends('portal.layout')

@section('title', 'Detail Cuti')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('portal.leave.index') }}" class="text-gray-400 hover:text-gray-600 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Cuti</h1>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-900">Informasi Cuti</h2>
            @php
                $statusColors = ['pending' => 'bg-amber-100 text-amber-700', 'approved' => 'bg-emerald-100 text-emerald-700', 'rejected' => 'bg-red-100 text-red-700', 'cancelled' => 'bg-gray-100 text-gray-500'];
                $statusLabels = ['pending' => 'Menunggu', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'cancelled' => 'Dibatalkan'];
            @endphp
            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$leave->status] ?? '' }}">
                {{ $statusLabels[$leave->status] ?? $leave->status }}
            </span>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tipe Cuti</p>
                    <p class="text-sm font-medium text-gray-900">{{ $leave->leaveType?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total Hari</p>
                    <p class="text-sm font-medium text-gray-900">{{ $leave->total_days }} hari</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tanggal Mulai</p>
                    <p class="text-sm font-medium text-gray-900">{{ $leave->start_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tanggal Selesai</p>
                    <p class="text-sm font-medium text-gray-900">{{ $leave->end_date->format('d M Y') }}</p>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Alasan</p>
                <p class="text-sm text-gray-700">{{ $leave->reason }}</p>
            </div>
            @if ($leave->attachment)
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Lampiran</p>
                <a href="{{ asset('storage/' . $leave->attachment) }}" target="_blank" class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download Lampiran
                </a>
            </div>
            @endif
            @if ($leave->rejection_reason)
            <div class="p-3 bg-red-50 rounded-lg border border-red-200">
                <p class="text-xs text-red-400 uppercase tracking-wider mb-1">Alasan Penolakan</p>
                <p class="text-sm text-red-700">{{ $leave->rejection_reason }}</p>
            </div>
            @endif
        </div>
    </div>

    @if ($leave->leaveApprovals->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Timeline Approval</h2>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @foreach ($leave->leaveApprovals as $approval)
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        @php
                            $dotColor = match($approval->status) {
                                'approved' => 'bg-emerald-500',
                                'rejected' => 'bg-red-500',
                                default => 'bg-amber-500'
                            };
                        @endphp
                        <div class="w-3 h-3 rounded-full {{ $dotColor }}"></div>
                        @if (!$loop->last)<div class="w-0.5 h-full bg-gray-200 mt-1"></div>@endif
                    </div>
                    <div class="pb-4">
                        <p class="text-sm font-medium text-gray-900">Level {{ $approval->level }}</p>
                        <p class="text-xs text-gray-500">{{ $approval->approver?->first_name . ' ' . $approval->approver?->last_name ?? 'Menunggu penunjukan' }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            @if ($approval->status === 'approved' && $approval->approved_at)
                                Disetujui {{ $approval->approved_at->format('d M Y H:i') }}
                            @elseif ($approval->status === 'rejected')
                                Ditolak
                            @else
                                Menunggu approval
                            @endif
                        </p>
                        @if ($approval->notes)
                        <p class="text-xs text-gray-500 mt-1 italic">"{{ $approval->notes }}"</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
