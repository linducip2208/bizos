@extends('client-portal.layout')

@section('title', 'Detail Invoice')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3"><a href="{{ route('client.invoices') }}" class="text-gray-400 hover:text-gray-600"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a><h1 class="text-2xl font-bold text-gray-900">Invoice #{{ $invoice->invoice_number }}</h1></div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between"><h2 class="text-base font-semibold">Detail</h2>@php $sc=['paid'=>'bg-emerald-100 text-emerald-700','overdue'=>'bg-red-100 text-red-700']; @endphp<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $sc[$invoice->status] ?? 'bg-blue-100 text-blue-700' }}">{{ strtoupper($invoice->status) }}</span></div>
        <div class="p-6 grid grid-cols-2 gap-4"><div><p class="text-xs text-gray-400 uppercase">Tgl Invoice</p><p class="text-sm font-medium">{{ $invoice->invoice_date->format('d M Y') }}</p></div><div><p class="text-xs text-gray-400 uppercase">Jatuh Tempo</p><p class="text-sm font-medium">{{ $invoice->due_date->format('d M Y') }}</p></div><div><p class="text-xs text-gray-400 uppercase">Total</p><p class="text-lg font-bold">Rp {{ number_format($invoice->total, 0, ',', '.') }}</p></div><div><p class="text-xs text-gray-400 uppercase">Sisa</p><p class="text-lg font-bold text-red-600">Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</p></div></div>
    </div>
    @if($invoice->invoiceItems->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"><div class="px-6 py-4 border-b"><h2 class="text-base font-semibold">Item</h2></div><div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Deskripsi</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase text-right">Qty</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase text-right">Harga</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase text-right">Total</th></tr></thead><tbody>@foreach($invoice->invoiceItems as $item)<tr><td class="px-4 py-3">{{ $item->description }}</td><td class="px-4 py-3 text-right">{{ $item->quantity }}</td><td class="px-4 py-3 text-right font-mono">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td><td class="px-4 py-3 text-right font-mono">Rp {{ number_format($item->total, 0, ',', '.') }}</td></tr>@endforeach</tbody></table></div></div>
    @endif
    @if($invoice->invoicePayments->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"><div class="px-6 py-4 border-b"><h2 class="text-base font-semibold">Riwayat Pembayaran</h2></div><div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Tanggal</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Metode</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase text-right">Jumlah</th></tr></thead><tbody>@foreach($invoice->invoicePayments as $ip)<tr><td class="px-4 py-3">{{ $ip->created_at->format('d M Y H:i') }}</td><td class="px-4 py-3">{{ $ip->payment?->paymentMethod?->name ?? '-' }}</td><td class="px-4 py-3 text-right font-mono font-semibold text-emerald-700">Rp {{ number_format($ip->amount, 0, ',', '.') }}</td></tr>@endforeach</tbody></table></div></div>
    @endif
</div>
@endsection
