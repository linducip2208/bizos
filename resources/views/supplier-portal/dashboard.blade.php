@extends('supplier-portal.layout')

@section('title', 'Dashboard Supplier')

@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Dashboard</h1><p class="text-sm text-gray-500 mt-1">Selamat datang, {{ $supplierUser->name }}</p></div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">PO Aktif</p><p class="text-2xl font-bold text-indigo-600 mt-1">{{ $activePOs }}</p></div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Menunggu Kirim</p><p class="text-2xl font-bold text-amber-600 mt-1">{{ $pendingDeliveries }}</p></div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Selesai Dibayar</p><p class="text-2xl font-bold text-emerald-600 mt-1">{{ $paidPOs }}</p></div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Nilai PO</p><p class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalValue, 0, ',', '.') }}</p></div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between"><h2 class="text-base font-semibold text-gray-900">Purchase Order Terbaru</h2><a href="{{ route('supplier.po.index') }}" class="text-xs text-teal-600 hover:text-teal-800">Lihat Semua</a></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">No PO</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Jatuh Tempo</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Total</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Detail</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentPOs as $po)
                    @php $sc = ['draft'=>'bg-gray-100 text-gray-700','sent'=>'bg-blue-100 text-blue-700','confirmed'=>'bg-amber-100 text-amber-700','shipped'=>'bg-purple-100 text-purple-700','delivered'=>'bg-emerald-100 text-emerald-700','paid'=>'bg-green-100 text-green-700']; $sl=['draft'=>'Draft','sent'=>'Terkirim','confirmed'=>'Dikonfirmasi','shipped'=>'Dikirim','delivered'=>'Diterima','paid'=>'Dibayar']; @endphp
                    <tr class="hover:bg-gray-50"><td class="px-4 py-3 font-mono font-medium">{{ $po->po_number }}</td><td class="px-4 py-3 text-gray-600">{{ $po->order_date->format('d M Y') }}</td><td class="px-4 py-3 text-gray-600">{{ $po->expected_date->format('d M Y') }}</td><td class="px-4 py-3 text-right font-mono">Rp {{ number_format($po->total, 0, ',', '.') }}</td><td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc[$po->status] ?? '' }}">{{ $sl[$po->status] ?? $po->status }}</span></td><td class="px-4 py-3 text-center"><a href="{{ route('supplier.po.show', $po->id) }}" class="text-teal-600 hover:text-teal-800 text-xs font-medium">Detail</a></td></tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada purchase order.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
