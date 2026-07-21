@extends('portal.layout')

@section('title', 'Daftar Invoice')

@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Daftar Invoice</h1><p class="text-sm text-gray-500 mt-1">Semua invoice Anda</p></div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">No Invoice</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Jatuh Tempo</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Total</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Sisa</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Detail</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($invoices as $inv)
                    @php $sc = ['draft'=>'bg-gray-100 text-gray-700','sent'=>'bg-blue-100 text-blue-700','partial'=>'bg-amber-100 text-amber-700','paid'=>'bg-emerald-100 text-emerald-700','overdue'=>'bg-red-100 text-red-700','cancelled'=>'bg-gray-100 text-gray-500']; $sl = ['draft'=>'Draft','sent'=>'Terkirim','partial'=>'Sebagian','paid'=>'Lunas','overdue'=>'Terlambat','cancelled'=>'Batal']; @endphp
                    <tr class="hover:bg-gray-50 transition"><td class="px-4 py-3 font-mono font-medium text-gray-900">{{ $inv->invoice_number }}</td><td class="px-4 py-3 text-gray-600">{{ $inv->invoice_date->format('d M Y') }}</td><td class="px-4 py-3 text-gray-600">{{ $inv->due_date->format('d M Y') }}</td><td class="px-4 py-3 text-right font-mono">Rp {{ number_format($inv->total, 0, ',', '.') }}</td><td class="px-4 py-3 text-right font-mono">Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}</td><td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc[$inv->status] ?? '' }}">{{ $sl[$inv->status] ?? $inv->status }}</span></td><td class="px-4 py-3 text-center"><a href="{{ route('portal.invoice-detail', $inv->id) }}" class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Detail</a></td></tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-10 text-center text-gray-400">Belum ada invoice.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
