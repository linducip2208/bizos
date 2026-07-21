@extends('portal.layout')

@section('title', 'Detail Invoice #' . $invoice->invoice_number)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ url()->previous() }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">&larr; Kembali</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Invoice #{{ $invoice->invoice_number }}</h1>
        </div>
        @php
            $statusColors = [
                'draft' => 'bg-gray-100 text-gray-700',
                'sent' => 'bg-blue-100 text-blue-700',
                'partial' => 'bg-amber-100 text-amber-700',
                'paid' => 'bg-emerald-100 text-emerald-700',
                'overdue' => 'bg-red-100 text-red-700',
                'cancelled' => 'bg-gray-100 text-gray-500',
            ];
            $statusLabels = [
                'draft' => 'Draft',
                'sent' => 'Terkirim',
                'partial' => 'Sebagian',
                'paid' => 'Lunas',
                'overdue' => 'Terlambat',
                'cancelled' => 'Dibatalkan',
            ];
        @endphp
        <span class="inline-flex px-3 py-1 rounded-full text-sm font-semibold {{ $statusColors[$invoice->status] ?? 'bg-gray-100 text-gray-700' }}">
            {{ $statusLabels[$invoice->status] ?? $invoice->status }}
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal Invoice</p>
            <p class="text-base font-semibold text-gray-900 mt-1">{{ $invoice->invoice_date->format('d M Y') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Jatuh Tempo</p>
            <p class="text-base font-semibold text-gray-900 mt-1">{{ $invoice->due_date->format('d M Y') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total</p>
            <p class="text-xl font-bold text-gray-900 mt-1">Rp {{ number_format($invoice->total, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Sisa Pembayaran</p>
            <p class="text-xl font-bold text-red-600 mt-1">Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}</p>
        </div>
    </div>

    @if ($invoice->invoiceItems->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Item Invoice</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Qty</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Harga</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($invoice->invoiceItems as $item)
                    <tr>
                        <td class="px-6 py-3 text-gray-900">{{ $item->description }}</td>
                        <td class="px-6 py-3 text-right text-gray-600">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-right font-mono text-gray-900">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-3 text-right font-mono text-gray-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-200">
                        <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Subtotal</td>
                        <td class="px-6 py-3 text-right font-mono text-gray-900">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if ($invoice->discount_amount > 0)
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right text-sm text-gray-600">Diskon</td>
                        <td class="px-6 py-3 text-right font-mono text-gray-900">Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    @if ($invoice->tax_amount > 0)
                    <tr>
                        <td colspan="3" class="px-6 py-3 text-right text-sm text-gray-600">Pajak</td>
                        <td class="px-6 py-3 text-right font-mono text-gray-900">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr class="border-t-2 border-gray-200">
                        <td colspan="3" class="px-6 py-3 text-right text-sm font-bold text-gray-900">Total</td>
                        <td class="px-6 py-3 text-right font-mono font-bold text-gray-900">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    @if ($invoice->invoicePayments->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Riwayat Pembayaran</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">No. Pembayaran</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Metode</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Jumlah</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($invoice->invoicePayments as $ip)
                    <tr>
                        <td class="px-6 py-3 font-mono text-sm font-medium text-gray-900">{{ $ip->payment->payment_number ?? '-' }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $ip->payment->payment_date ? $ip->payment->payment_date->format('d M Y') : '-' }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $ip->payment->paymentMethod->name ?? '-' }}</td>
                        <td class="px-6 py-3 text-right font-mono text-gray-900">Rp {{ number_format($ip->amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                {{ $ip->payment->status ?? '-' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
