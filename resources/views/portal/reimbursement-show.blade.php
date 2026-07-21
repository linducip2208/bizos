@extends('portal.layout')

@section('title', 'Detail Reimbursement')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3"><a href="{{ route('portal.reimbursement.index') }}" class="text-gray-400 hover:text-gray-600"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a><h1 class="text-2xl font-bold text-gray-900">Detail Reimbursement</h1></div>
    @php $sc = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700','paid'=>'bg-blue-100 text-blue-700']; $sl = ['pending'=>'Menunggu','approved'=>'Disetujui','rejected'=>'Ditolak','paid'=>'Dibayar']; @endphp
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"><h2 class="text-base font-semibold text-gray-900">Informasi Reimbursement</h2><span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $sc[$reimbursement->status] ?? '' }}">{{ $sl[$reimbursement->status] ?? $reimbursement->status }}</span></div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4"><div><p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Kategori</p><p class="text-sm font-medium text-gray-900">{{ $reimbursement->category?->name ?? '-' }}</p></div><div><p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tanggal</p><p class="text-sm font-medium text-gray-900">{{ $reimbursement->date->format('d M Y') }}</p></div></div>
            <div><p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Jumlah</p><p class="text-lg font-bold text-gray-900">Rp {{ number_format($reimbursement->amount, 0, ',', '.') }}</p></div>
            <div><p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Deskripsi</p><p class="text-sm text-gray-700">{{ $reimbursement->description }}</p></div>
            @if ($reimbursement->rejection_reason)<div class="p-3 bg-red-50 rounded-lg border border-red-200"><p class="text-xs text-red-400 uppercase tracking-wider mb-1">Alasan Penolakan</p><p class="text-sm text-red-700">{{ $reimbursement->rejection_reason }}</p></div>@endif
            @if ($reimbursement->paid_date)<div class="p-3 bg-emerald-50 rounded-lg border border-emerald-200"><p class="text-xs text-emerald-400 uppercase tracking-wider mb-1">Pembayaran</p><p class="text-sm text-emerald-700">Dibayar: <strong>Rp {{ number_format($reimbursement->paid_amount, 0, ',', '.') }}</strong> pada {{ $reimbursement->paid_date->format('d M Y') }}</p></div>@endif
            @if ($reimbursement->reimbursementAttachments->isNotEmpty())
            <div><p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Lampiran ({{ $reimbursement->reimbursementAttachments->count() }})</p><div class="grid grid-cols-2 gap-2">@foreach($reimbursement->reimbursementAttachments as $att)<a href="{{ asset('storage/'.$att->file_path) }}" target="_blank" class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg text-xs text-indigo-600 hover:bg-indigo-50 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>{{ $att->file_name }}</a>@endforeach</div></div>
            @endif
        </div>
    </div>
</div>
@endsection
