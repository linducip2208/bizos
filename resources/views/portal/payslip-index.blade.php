@extends('portal.layout')

@section('title', 'Slip Gaji')

@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Slip Gaji</h1><p class="text-sm text-gray-500 mt-1">Riwayat slip gaji Anda</p></div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Periode</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Periode Gaji</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Gaji Kotor</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Gaji Bersih</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Aksi</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($payrolls as $p)
                    @php $sLabels = ['draft'=>'bg-gray-100 text-gray-700','calculated'=>'bg-blue-100 text-blue-700','approved'=>'bg-emerald-100 text-emerald-700','paid'=>'bg-green-100 text-green-700']; $slStatus = ['draft'=>'Draft','calculated'=>'Dihitung','approved'=>'Disetujui','paid'=>'Dibayar']; @endphp
                    <tr class="hover:bg-gray-50 transition"><td class="px-4 py-3 font-mono font-medium text-gray-900">{{ $p->period?->period_code ?? '-' }}</td><td class="px-4 py-3 text-gray-600">{{ $p->period?->start_date?->format('d M') ?? '-' }} - {{ $p->period?->end_date?->format('d M Y') ?? '-' }}</td><td class="px-4 py-3 text-right font-mono text-gray-900">Rp {{ number_format($p->gross_salary, 0, ',', '.') }}</td><td class="px-4 py-3 text-right font-mono font-semibold text-gray-900">Rp {{ number_format($p->net_salary, 0, ',', '.') }}</td><td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sLabels[$p->status] ?? 'bg-gray-100' }}">{{ $slStatus[$p->status] ?? $p->status }}</span></td><td class="px-4 py-3 text-center">@if($p->paySlip)<a href="{{ route('portal.payslip.download', $p->paySlip->id) }}" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 text-xs font-medium"><svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>Download</a>@else<span class="text-xs text-gray-400">-</span>@endif</td></tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada data slip gaji.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($payrolls->hasPages())<div class="px-6 py-3 border-t border-gray-100">{{ $payrolls->links() }}</div>@endif
    </div>
</div>
@endsection
