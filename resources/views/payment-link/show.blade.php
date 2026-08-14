@extends('payment-link.layout')

@section('title', 'Faktur ' . $invoice->invoice_number)

@section('brand-initial', strtoupper(substr($invoice->company?->name ?? config('app.name', 'B'), 0, 1)))
@section('brand-name', $invoice->company?->name ?? config('app.name', 'BizOS'))

@section('content')
    @php
        $currencySymbol = $invoice->currency?->symbol ?? 'Rp';
        $isPaid = $invoice->status === 'paid' || $invoice->remaining_amount <= 0;
        $fmt = fn ($v) => $currencySymbol . ' ' . number_format((float) $v, 2, ',', '.');
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 sm:px-8 py-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900">Faktur #{{ $invoice->invoice_number }}</h1>
                <p class="text-sm text-slate-500 mt-0.5">
                    Diterbitkan {{ $invoice->invoice_date?->format('d M Y') }} &middot; Jatuh tempo {{ $invoice->due_date?->format('d M Y') }}
                </p>
            </div>
            @if ($isPaid)
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 text-emerald-700 text-sm font-semibold border border-emerald-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Lunas
                </span>
            @elseif ($invoice->paid_amount > 0)
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-amber-50 text-amber-700 text-sm font-semibold border border-amber-200">
                    Dibayar Sebagian
                </span>
            @else
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-rose-50 text-rose-700 text-sm font-semibold border border-rose-200">
                    Belum Dibayar
                </span>
            @endif
        </div>

        <div class="px-6 sm:px-8 py-6 grid sm:grid-cols-2 gap-6">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Ditagihkan Kepada</div>
                <div class="mt-2 text-slate-900 font-semibold">{{ $clientName }}</div>
            </div>
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Diterbitkan Oleh</div>
                <div class="mt-2 text-slate-900 font-semibold">{{ $invoice->company?->name ?? '—' }}</div>
                @if ($invoice->company?->address)
                    <div class="text-sm text-slate-500 mt-0.5">{{ $invoice->company->address }}</div>
                @endif
            </div>
        </div>

        <div class="px-6 sm:px-8 pb-6">
            <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 font-semibold">Deskripsi</th>
                            <th class="px-4 py-3 font-semibold text-right">Qty</th>
                            <th class="px-4 py-3 font-semibold text-right">Harga</th>
                            <th class="px-4 py-3 font-semibold text-right">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($invoice->invoiceItems as $item)
                            <tr>
                                <td class="px-4 py-3 text-slate-800">{{ $item->description }}</td>
                                <td class="px-4 py-3 text-right text-slate-600 font-mono">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right text-slate-600 font-mono">{{ $fmt($item->unit_price) }}</td>
                                <td class="px-4 py-3 text-right text-slate-900 font-semibold font-mono">{{ $fmt($item->amount) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-slate-400">Tidak ada rincian item.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-5 space-y-2 max-w-sm ml-auto">
                <div class="flex justify-between text-sm text-slate-500">
                    <span>Subtotal</span>
                    <span class="font-mono">{{ $fmt($invoice->subtotal) }}</span>
                </div>
                @if ((float) $invoice->discount_amount > 0)
                    <div class="flex justify-between text-sm text-slate-500">
                        <span>Diskon</span>
                        <span class="font-mono">-{{ $fmt($invoice->discount_amount) }}</span>
                    </div>
                @endif
                @if ((float) $invoice->tax_amount > 0)
                    <div class="flex justify-between text-sm text-slate-500">
                        <span>Pajak</span>
                        <span class="font-mono">{{ $fmt($invoice->tax_amount) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-base font-bold text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total Tagihan</span>
                    <span class="font-mono">{{ $fmt($invoice->total) }}</span>
                </div>
                <div class="flex justify-between text-sm text-emerald-600">
                    <span>Sudah Dibayar</span>
                    <span class="font-mono">{{ $fmt($invoice->paid_amount) }}</span>
                </div>
                <div class="flex justify-between text-base font-bold text-rose-600 pt-2 border-t border-slate-200">
                    <span>Sisa Tagihan</span>
                    <span class="font-mono">{{ $fmt($invoice->remaining_amount) }}</span>
                </div>
            </div>
        </div>
    </div>

    @if (! $isPaid)
        <div class="mt-6 flex justify-center">
            <a href="{{ route('pay.form', $invoice->payment_token) }}"
               class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-semibold shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:-translate-y-0.5 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Bayar Sekarang
            </a>
        </div>
    @endif
@endsection
