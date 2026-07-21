@extends('portal.layout')

@section('title', 'Reimbursement')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between"><div><h1 class="text-2xl font-bold text-gray-900">Reimbursement</h1><p class="text-sm text-gray-500 mt-1">Riwayat pengajuan reimbursement</p></div><a href="{{ route('portal.reimbursement.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Ajukan Reimbursement</a></div>

    @if (session('success'))<div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>@endif

    <div class="flex gap-2">
        <a href="{{ route('portal.reimbursement.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('status') ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">Semua</a>
        <a href="{{ route('portal.reimbursement.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status') === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">Menunggu</a>
        <a href="{{ route('portal.reimbursement.index', ['status' => 'approved']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status') === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">Disetujui</a>
        <a href="{{ route('portal.reimbursement.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status') === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">Ditolak</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kategori</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Deskripsi</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Jumlah</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Detail</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($reimbursements as $r)
                    @php $sc = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700','paid'=>'bg-blue-100 text-blue-700']; $sl = ['pending'=>'Menunggu','approved'=>'Disetujui','rejected'=>'Ditolak','paid'=>'Dibayar']; @endphp
                    <tr class="hover:bg-gray-50 transition"><td class="px-4 py-3 font-medium text-gray-900">{{ $r->date->format('d M Y') }}</td><td class="px-4 py-3 text-gray-600">{{ $r->category?->name ?? '-' }}</td><td class="px-4 py-3 text-gray-600 max-w-[200px] truncate">{{ Str::limit($r->description, 40) }}</td><td class="px-4 py-3 text-right font-mono text-gray-900">Rp {{ number_format($r->amount, 0, ',', '.') }}</td><td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc[$r->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $sl[$r->status] ?? $r->status }}</span></td><td class="px-4 py-3 text-center"><a href="{{ route('portal.reimbursement.show', $r->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Detail</a></td></tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada pengajuan reimbursement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reimbursements->hasPages())<div class="px-6 py-3 border-t border-gray-100">{{ $reimbursements->links() }}</div>@endif
    </div>
</div>
@endsection
