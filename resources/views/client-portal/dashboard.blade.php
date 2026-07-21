@extends('client-portal.layout')

@section('title', 'Dashboard Klien')

@section('content')
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Dashboard</h1><p class="text-sm text-gray-500 mt-1">Selamat datang, {{ $clientUser->name }}</p></div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Invoice</p><p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalInvoices) }}</p></div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Dibayar</p><p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p></div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tunggakan</p><p class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</p></div>
        <div class="bg-white rounded-xl border border-gray-100 p-4 shadow-sm"><p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Overdue</p><p class="text-2xl font-bold {{ $overdueInvoices > 0 ? 'text-red-600' : 'text-gray-900' }} mt-1">{{ $overdueInvoices }}</p></div>
    </div>

    @if($deals->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm"><div class="flex justify-between mb-3"><h3 class="text-sm font-semibold text-gray-700">Deal Aktif</h3><a href="{{ route('client.deals') }}" class="text-xs text-blue-600">Lihat Semua</a></div><div class="space-y-2">@foreach($deals as $deal)<div class="flex justify-between p-2 bg-gray-50 rounded-lg"><div><p class="text-sm font-medium">{{ $deal->title }}</p><p class="text-xs text-gray-400">{{ $deal->stage?->name ?? '-' }}</p></div><p class="text-sm font-mono text-gray-900">Rp {{ number_format($deal->expected_value, 0, ',', '.') }}</p></div>@endforeach</div></div>
    @endif

    @if($tickets->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm"><div class="flex justify-between mb-3"><h3 class="text-sm font-semibold text-gray-700">Tiket Terbaru</h3><a href="{{ route('client.tickets') }}" class="text-xs text-blue-600">Lihat Semua</a></div><div class="space-y-2">@foreach($tickets as $t)<div class="p-2 bg-gray-50 rounded-lg"><p class="text-sm font-medium">{{ $t->subject }}</p><p class="text-xs text-gray-400">{{ strtoupper($t->priority) }} | {{ strtoupper($t->status) }} | {{ $t->updated_at->diffForHumans() }}</p></div>@endforeach</div></div>
    @endif

    @if($invoices->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"><div class="px-6 py-4 border-b border-gray-100 flex justify-between"><h2 class="text-base font-semibold text-gray-900">Invoice Terbaru</h2><a href="{{ route('client.invoices') }}" class="text-xs text-blue-600">Lihat Semua</a></div><div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase">No</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Tanggal</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase text-right">Total</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Status</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase text-center">Detail</th></tr></thead><tbody>@foreach($invoices->take(5) as $inv) @php $sc=['paid'=>'bg-emerald-100 text-emerald-700','sent'=>'bg-blue-100 text-blue-700','overdue'=>'bg-red-100 text-red-700']; @endphp <tr><td class="px-4 py-3 font-mono font-medium">{{ $inv->invoice_number }}</td><td class="px-4 py-3 text-gray-600">{{ $inv->invoice_date->format('d M Y') }}</td><td class="px-4 py-3 text-right font-mono">Rp {{ number_format($inv->total, 0, ',', '.') }}</td><td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc[$inv->status] ?? 'bg-gray-100' }}">{{ strtoupper($inv->status) }}</span></td><td class="px-4 py-3 text-center"><a href="{{ route('client.invoice-detail', $inv->id) }}" class="text-blue-600 text-xs">Detail</a></td></tr>@endforeach</tbody></table></div></div>
    @endif
</div>
@endsection
