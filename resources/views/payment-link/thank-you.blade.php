@extends('payment-link.layout')

@section('title', 'Pembayaran Diterima')

@section('brand-initial', strtoupper(substr($invoice->company?->name ?? config('app.name', 'B'), 0, 1)))
@section('brand-name', $invoice->company?->name ?? config('app.name', 'BizOS'))

@section('content')
    @php
        $currencySymbol = $invoice->currency?->symbol ?? 'Rp';
        $fmt = fn ($v) => $currencySymbol . ' ' . number_format((float) $v, 2, ',', '.');
    @endphp

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-12 text-center">
        <div class="mx-auto w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-9 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <h1 class="mt-5 text-2xl font-bold text-slate-900">Terima Kasih!</h1>
        <p class="mt-2 text-slate-500">Pembayaran Anda telah kami terima dan sedang diproses.</p>

        <div class="mt-8 max-w-sm mx-auto rounded-xl border border-slate-200 divide-y divide-slate-100 text-sm">
            <div class="flex justify-between px-5 py-3">
                <span class="text-slate-500">Faktur</span>
                <span class="font-semibold text-slate-900">#{{ $invoice->invoice_number }}</span>
            </div>
            @if ($paymentNumber)
                <div class="flex justify-between px-5 py-3">
                    <span class="text-slate-500">No. Pembayaran</span>
                    <span class="font-mono font-semibold text-slate-900">{{ $paymentNumber }}</span>
                </div>
            @endif
            <div class="flex justify-between px-5 py-3">
                <span class="text-slate-500">Total Tagihan</span>
                <span class="font-mono font-semibold text-slate-900">{{ $fmt($invoice->total) }}</span>
            </div>
            <div class="flex justify-between px-5 py-3">
                <span class="text-slate-500">Sudah Dibayar</span>
                <span class="font-mono font-semibold text-emerald-600">{{ $fmt($invoice->paid_amount) }}</span>
            </div>
            <div class="flex justify-between px-5 py-3">
                <span class="text-slate-500">Sisa Tagihan</span>
                <span class="font-mono font-semibold text-slate-900">{{ $fmt($invoice->remaining_amount) }}</span>
            </div>
        </div>

        <p class="mt-6 text-sm text-slate-400">
            @if ($invoice->remaining_amount > 0)
                Sisa tagihan Anda masih ada. Simpan halaman ini untuk kembali membayar sisa tagihan.
            @else
                Faktur Anda telah lunas. Terima kasih atas kepercayaan Anda.
            @endif
        </p>

        <div class="mt-8">
            <a href="{{ route('pay.show', $invoice->payment_token) }}"
               class="inline-flex items-center justify-center px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-semibold shadow-lg shadow-indigo-500/25 hover:-translate-y-0.5 transition">
                Lihat Faktur
            </a>
        </div>
    </div>
@endsection
