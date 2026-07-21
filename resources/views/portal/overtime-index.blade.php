@extends('portal.layout')

@section('title', 'Lembur')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-gray-900">Lembur</h1><p class="text-sm text-gray-500 mt-1">Riwayat pengajuan lembur</p></div>
        <a href="{{ route('portal.overtime.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>Ajukan Lembur</a>
    </div>

    @if (session('success'))<div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>@endif

    <div class="flex gap-2">
        <a href="{{ route('portal.overtime.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('status') ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">Semua</a>
        <a href="{{ route('portal.overtime.index', ['status' => 'pending']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status') === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600' }}">Menunggu</a>
        <a href="{{ route('portal.overtime.index', ['status' => 'approved']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status') === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">Disetujui</a>
        <a href="{{ route('portal.overtime.index', ['status' => 'rejected']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status') === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">Ditolak</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-left">
                    <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Jam</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Durasi</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Rate</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Detail</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($overtimes as $ot)
                    @php $sc = ['pending'=>'bg-amber-100 text-amber-700','approved'=>'bg-emerald-100 text-emerald-700','rejected'=>'bg-red-100 text-red-700']; $sl = ['pending'=>'Menunggu','approved'=>'Disetujui','rejected'=>'Ditolak']; @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $ot->date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $ot->start_time->format('H:i') }} - {{ $ot->end_time->format('H:i') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ floor($ot->duration_minutes/60) }}j {{ $ot->duration_minutes%60 }}m</td>
                        <td class="px-4 py-3 text-gray-600">{{ $ot->rate_multiplier }}x</td>
                        <td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc[$ot->status] ?? 'bg-gray-100 text-gray-700' }}">{{ $sl[$ot->status] ?? $ot->status }}</span></td>
                        <td class="px-4 py-3 text-center"><a href="{{ route('portal.overtime.show', $ot->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Detail</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada pengajuan lembur.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($overtimes->hasPages())<div class="px-6 py-3 border-t border-gray-100">{{ $overtimes->links() }}</div>@endif
    </div>
</div>
@endsection
