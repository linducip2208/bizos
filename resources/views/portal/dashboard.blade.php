@extends('portal.layout')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-1">Ringkasan invoice dan pembayaran Anda</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Invoice</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($dashboardInvoices->count() ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Dibayar</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalPaid ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 p-5 shadow-sm">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tunggakan</p>
            <p class="text-2xl font-bold text-red-600 mt-1">Rp {{ number_format($totalOutstanding ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Daftar Invoice</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Nomor Invoice</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Jatuh Tempo</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Total</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Sisa</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($dashboardInvoices as $inv)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 font-mono text-sm font-medium text-gray-900">{{ $inv->invoice_number }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $inv->invoice_date->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $inv->due_date->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-right font-mono text-gray-900">Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                        <td class="px-6 py-3 text-right font-mono text-gray-900">Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}</td>
                        <td class="px-6 py-3">
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
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$inv->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$inv->status] ?? $inv->status }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <a href="{{ route('portal.invoice-detail', $inv->id) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                            Belum ada invoice.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
