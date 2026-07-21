@extends('supplier-portal.layout')

@section('title', 'Detail Purchase Order')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3"><a href="{{ route('supplier.po.index') }}" class="text-gray-400 hover:text-gray-600"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a><h1 class="text-2xl font-bold text-gray-900">Purchase Order #{{ $po->po_number }}</h1></div>

    @if (session('success'))<div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ session('error') }}</div>@endif

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between"><h2 class="text-base font-semibold text-gray-900">Informasi PO</h2>@php $sc = ['draft'=>'bg-gray-100 text-gray-700','sent'=>'bg-blue-100 text-blue-700','confirmed'=>'bg-amber-100 text-amber-700','shipped'=>'bg-purple-100 text-purple-700','delivered'=>'bg-emerald-100 text-emerald-700','paid'=>'bg-green-100 text-green-700']; $sl=['draft'=>'Draft','sent'=>'Terkirim','confirmed'=>'Dikonfirmasi','shipped'=>'Dikirim','delivered'=>'Diterima','paid'=>'Dibayar']; @endphp<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $sc[$po->status] ?? '' }}">{{ $sl[$po->status] ?? $po->status }}</span></div>
        <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div><p class="text-xs text-gray-400 uppercase">Tanggal Order</p><p class="text-sm font-medium">{{ $po->order_date->format('d M Y') }}</p></div>
            <div><p class="text-xs text-gray-400 uppercase">Estimasi</p><p class="text-sm font-medium">{{ $po->expected_date->format('d M Y') }}</p></div>
            <div><p class="text-xs text-gray-400 uppercase">PR Ref</p><p class="text-sm font-medium">{{ $po->purchaseRequisition?->pr_number ?? '-' }}</p></div>
            <div><p class="text-xs text-gray-400 uppercase">Gudang</p><p class="text-sm font-medium">{{ $po->warehouse?->name ?? '-' }}</p></div>
            <div class="md:col-span-4"><p class="text-xs text-gray-400 uppercase">Catatan</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $po->notes ?? '-' }}</p></div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-semibold text-gray-900">Item</h2></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Produk</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase text-right">Qty</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase text-right">Harga</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase text-right">Subtotal</th></tr></thead>
                <tbody class="divide-y divide-gray-100">@foreach($po->items as $item)<tr><td class="px-4 py-3 font-medium">{{ $item->product?->name ?? 'Item #'.$item->id }}</td><td class="px-4 py-3 text-right">{{ $item->quantity }} {{ $item->unit ?? '' }}</td><td class="px-4 py-3 text-right font-mono">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td><td class="px-4 py-3 text-right font-mono">Rp {{ number_format($item->subtotal ?? ($item->quantity * $item->unit_price), 0, ',', '.') }}</td></tr>@endforeach</tbody>
                <tfoot>
                    <tr class="border-t"><td colspan="3" class="px-4 py-3 text-right text-sm font-semibold">Subtotal</td><td class="px-4 py-3 text-right font-mono font-semibold">Rp {{ number_format($po->subtotal, 0, ',', '.') }}</td></tr>
                    @if($po->tax_amount > 0)<tr><td colspan="3" class="px-4 py-3 text-right text-sm">Pajak</td><td class="px-4 py-3 text-right font-mono">Rp {{ number_format($po->tax_amount, 0, ',', '.') }}</td></tr>@endif
                    @if($po->shipping_cost > 0)<tr><td colspan="3" class="px-4 py-3 text-right text-sm">Ongkir</td><td class="px-4 py-3 text-right font-mono">Rp {{ number_format($po->shipping_cost, 0, ',', '.') }}</td></tr>@endif
                    <tr class="border-t-2 border-gray-800"><td colspan="3" class="px-4 py-3 text-right font-bold">Total</td><td class="px-4 py-3 text-right font-mono font-bold text-lg">Rp {{ number_format($po->total, 0, ',', '.') }}</td></tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($po->goodsReceipts->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-semibold text-gray-900">Penerimaan Barang</h2></div>
        <div class="p-6 space-y-3">@foreach($po->goodsReceipts as $gr)<div class="p-4 bg-gray-50 rounded-lg"><div class="flex justify-between"><p class="text-sm font-medium">{{ $gr->grn_number }}</p><p class="text-xs text-gray-400">{{ $gr->receipt_date->format('d M Y') }}</p></div><p class="text-xs text-gray-500 mt-1">Status: {{ $gr->status }} | Diterima oleh: {{ $gr->receiver?->first_name ?? '-' }}</p></div>@endforeach</div>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm space-y-4">
        <h2 class="text-base font-semibold text-gray-900">Update Status</h2>
        @php $nextStatus = ['sent' => ['confirmed', 'Konfirmasi PO'], 'confirmed' => ['shipped', 'Tandai Dikirim'], 'shipped' => ['delivered', 'Tandai Diterima']]; $next = $nextStatus[$po->status] ?? null; @endphp
        @if($next)
        <form action="{{ route('supplier.po.status', $po->id) }}" method="POST" class="flex items-end gap-3">
            @csrf
            <input type="hidden" name="status" value="{{ $next[0] }}">
            <div class="flex-1"><label class="block text-xs text-gray-500 mb-1">Catatan</label><input type="text" name="notes" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" placeholder="Opsional"></div>
            <button type="submit" class="px-4 py-2 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition cursor-pointer">{{ $next[1] }}</button>
        </form>
        @else
        <p class="text-sm text-gray-400">Status saat ini: <strong>{{ $sl[$po->status] }}</strong>. Tidak ada update status yang tersedia.</p>
        @endif

        @if(in_array($po->status, ['delivered', 'shipped']))
        <div class="border-t border-gray-100 pt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Upload Invoice</h3>
            <form action="{{ route('supplier.po.invoice', $po->id) }}" method="POST" enctype="multipart/form-data" class="flex items-end gap-3">
                @csrf
                <div><label class="block text-xs text-gray-500 mb-1">No Invoice</label><input type="text" name="invoice_number" required class="w-40 px-3 py-2 border border-gray-200 rounded-lg text-sm"></div>
                <div><label class="block text-xs text-gray-500 mb-1">File</label><input type="file" name="invoice_file" required accept=".pdf,.jpg,.jpeg,.png" class="text-xs"></div>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition cursor-pointer">Upload Invoice</button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
