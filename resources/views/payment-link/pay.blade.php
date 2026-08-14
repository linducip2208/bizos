@extends('payment-link.layout')

@section('title', 'Bayar Faktur ' . $invoice->invoice_number)

@section('brand-initial', strtoupper(substr($invoice->company?->name ?? config('app.name', 'B'), 0, 1)))
@section('brand-name', $invoice->company?->name ?? config('app.name', 'BizOS'))

@section('head')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection

@section('content')
    @php
        $currencySymbol = $invoice->currency?->symbol ?? 'Rp';
        $fmt = fn ($v) => $currencySymbol . ' ' . number_format((float) $v, 2, ',', '.');
        $remaining = (float) $invoice->remaining_amount;
    @endphp

    <div x-data="{ amount: {{ $remaining }}, methodCode: '', isTransfer: false }" class="space-y-6">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
            <h1 class="text-xl font-bold text-slate-900">Pembayaran Faktur #{{ $invoice->invoice_number }}</h1>
            <div class="mt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-sm">
                <span class="text-slate-500">Sisa tagihan yang harus dibayar</span>
                <span class="text-2xl font-bold text-slate-900 font-mono">{{ $fmt($remaining) }}</span>
            </div>
        </div>

        @if (isset($errors) && $errors->any())
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-sm text-rose-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('pay.submit', $invoice->payment_token) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                <h2 class="font-bold text-slate-900 mb-4">1. Pilih Metode Pembayaran</h2>
                <div class="grid sm:grid-cols-2 gap-3">
                    @foreach ($paymentMethods as $method)
                        @php
                            $code = strtolower($method->code ?? '');
                            $isTransferMethod = str_contains($code, 'transfer');
                        @endphp
                        <label class="cursor-pointer">
                            <input type="radio" name="payment_method_id" value="{{ $method->id }}"
                                   class="peer sr-only"
                                   x-on:change="methodCode = '{{ $code }}'; isTransfer = {{ $isTransferMethod ? 'true' : 'false' }}; if (isTransfer) amount = {{ $remaining }}">
                            <div class="flex items-center gap-3 p-4 rounded-xl border-2 border-slate-200 peer-checked:border-indigo-600 peer-checked:bg-indigo-50/50 transition">
                                <div class="w-4 h-4 rounded-full border-2 border-slate-300 peer-checked:border-indigo-600 flex items-center justify-center">
                                    <div class="w-2 h-2 rounded-full bg-indigo-600 opacity-0 peer-checked:opacity-100"></div>
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900 text-sm">{{ $method->name }}</div>
                                    <div class="text-xs text-slate-400">
                                        {{ $isTransferMethod ? 'Perlu upload bukti transfer' : 'Pembayaran instan' }}
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                <h2 class="font-bold text-slate-900 mb-4">2. Jumlah Pembayaran</h2>
                <div class="flex items-center gap-3">
                    <button type="button" @click="amount = {{ $remaining }}"
                            class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-700 hover:border-indigo-500 hover:text-indigo-600 transition"
                            x-bind:class="amount == {{ $remaining }} ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : ''">
                        Bayar Penuh
                    </button>
                    <button type="button" @click="amount = 0"
                            class="px-4 py-2 rounded-lg border border-slate-200 text-sm font-semibold text-slate-700 hover:border-indigo-500 hover:text-indigo-600 transition">
                        Bayar Sebagian
                    </button>
                </div>
                <div class="mt-4">
                    <label for="amount" class="block text-sm font-medium text-slate-700 mb-1.5">Nominal ({{ $currencySymbol }})</label>
                    <input type="number" name="amount" id="amount" x-model.number="amount"
                           step="0.01" min="1" max="{{ $remaining }}" required
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none font-mono">
                </div>
            </div>

            <div x-show="isTransfer" x-cloak class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                <h2 class="font-bold text-slate-900 mb-4">3. Transfer ke Rekening Berikut</h2>
                @forelse ($bankAccounts as $bank)
                    <div class="flex items-center justify-between p-4 rounded-xl bg-slate-50 border border-slate-200 mb-3">
                        <div>
                            <div class="font-semibold text-slate-900">{{ $bank->bank_name }}</div>
                            <div class="text-sm text-slate-500">{{ $bank->account_name }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-mono font-bold text-slate-900">{{ $bank->account_number }}</div>
                            <div class="text-xs text-slate-400">Nomor Rekening</div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Rekening tujuan akan dikirimkan oleh penagih. Silakan hubungi kontak pada faktur.</p>
                @endforelse

                <div class="mt-5">
                    <label for="proof" class="block text-sm font-medium text-slate-700 mb-1.5">Unggah Bukti Transfer</label>
                    <input type="file" name="proof" id="proof" accept="image/*"
                           class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 file:font-semibold hover:file:bg-indigo-100 cursor-pointer">
                    <p class="text-xs text-slate-400 mt-1.5">Format gambar (JPG/PNG), maksimal 5 MB.</p>
                </div>

                <div class="mt-4">
                    <label for="reference_number" class="block text-sm font-medium text-slate-700 mb-1.5">Nomor Referensi (opsional)</label>
                    <input type="text" name="reference_number" id="reference_number" maxlength="100"
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
                <label for="notes" class="block text-sm font-medium text-slate-700 mb-1.5">Catatan (opsional)</label>
                <textarea name="notes" id="notes" rows="2" maxlength="500"
                          class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></textarea>
            </div>

            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-semibold shadow-lg shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:-translate-y-0.5 transition">
                Konfirmasi Pembayaran
            </button>
        </form>

        <div class="text-center">
            <a href="{{ route('pay.show', $invoice->payment_token) }}" class="text-sm text-slate-500 hover:text-indigo-600 transition">Kembali ke detail faktur</a>
        </div>
    </div>
@endsection
