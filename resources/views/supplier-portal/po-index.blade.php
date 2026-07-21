@extends('supplier-portal.layout')

@section('title', 'Daftar Purchase Order')

@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Purchase Order</h1><p class="text-sm text-gray-500 mt-1">Daftar semua PO untuk {{ $supplierUser->name }}</p></div>

    <div class="flex gap-2 flex-wrap">
        <a href="{{ route('supplier.po.index') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ !request('status') ? 'bg-teal-100 text-teal-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Semua</a>
        <a href="{{ route('supplier.po.index', ['status'=>'sent']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status')==='sent' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Terkirim</a>
        <a href="{{ route('supplier.po.index', ['status'=>'confirmed']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status')==='confirmed' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Dikonfirmasi</a>
        <a href="{{ route('supplier.po.index', ['status'=>'delivered']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status')==='delivered' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Diterima</a>
        <a href="{{ route('supplier.po.index', ['status'=>'paid']) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ request('status')==='paid' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Dibayar</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">No PO</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Jatuh Tempo</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Total</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Aksi</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pos as $po)
                    @php $sc = ['draft'=>'bg-gray-100 text-gray-700','sent'=>'bg-blue-100 text-blue-700','confirmed'=>'bg-amber-100 text-amber-700','shipped'=>'bg-purple-100 text-purple-700','delivered'=>'bg-emerald-100 text-emerald-700','paid'=>'bg-green-100 text-green-700']; $sl=['draft'=>'Draft','sent'=>'Terkirim','confirmed'=>'Dikonfirmasi','shipped'=>'Dikirim','delivered'=>'Diterima','paid'=>'Dibayar']; @endphp
                    <tr class="hover:bg-gray-50"><td class="px-4 py-3 font-mono font-medium">{{ $po->po_number }}</td><td class="px-4 py-3 text-gray-600">{{ $po->order_date->format('d M Y') }}</td><td class="px-4 py-3 text-gray-600">{{ $po->expected_date->format('d M Y') }}</td><td class="px-4 py-3 text-right font-mono">Rp {{ number_format($po->total, 0, ',', '.') }}</td><td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc[$po->status] ?? '' }}">{{ $sl[$po->status] ?? $po->status }}</span></td><td class="px-4 py-3 text-center"><a href="{{ route('supplier.po.show', $po->id) }}" class="text-teal-600 hover:text-teal-800 text-xs font-medium">Detail</a></td></tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada purchase order.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($pos->hasPages())<div class="px-6 py-3 border-t border-gray-100">{{ $pos->links() }}</div>@endif
    </div>
</div>
@endsection
