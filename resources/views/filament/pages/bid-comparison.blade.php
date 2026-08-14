@php
    use App\Models\Rfq;
@endphp

<div>
    <div class="px-4 sm:px-6 lg:px-8">
        <form wire:submit="loadComparison" class="mb-6 max-w-lg">
            {{ $this->form }}
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="rfqId">
                            <option value="">-- Pilih RFQ --</option>
                            @foreach($this->getRfqOptions() as $id => $number)
                                <option value="{{ $id }}">{{ $number }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <x-filament::button wire:click="loadComparison" color="primary">
                    Tampilkan
                </x-filament::button>
            </div>
        </form>

        @if($rfq)
            <div class="mb-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ $rfq->rfq_number }} — {{ $rfq->title }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Status: 
                    <x-filament::badge :color="match($rfq->status) {
                        'draft' => 'gray', 'sent' => 'info', 'open' => 'success',
                        'closed' => 'warning', 'awarded' => 'primary', 'cancelled' => 'danger',
                        default => 'gray'
                    }">
                        {{ match($rfq->status) {
                            'draft' => 'Draft', 'sent' => 'Terkirim', 'open' => 'Terbuka',
                            'closed' => 'Tertutup', 'awarded' => 'Diberikan', 'cancelled' => 'Dibatalkan',
                            default => $rfq->status
                        } }}
                    </x-filament::badge>
                    &nbsp;·&nbsp;
                    Batas: {{ $rfq->submission_deadline->format('d M Y H:i') }}
                    &nbsp;·&nbsp;
                    {{ count($bids) }} Penawaran
                </p>
            </div>

            @if(count($comparisonMatrix) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                                    Perbandingan
                                </th>
                                @foreach($comparisonMatrix as $bid)
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 {{ $bid['status'] === 'accepted' ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                        <div class="font-bold text-base">{{ $bid['supplier_name'] }}</div>
                                        <div class="text-xs text-gray-400">{{ $bid['bid_number'] }}</div>
                                        @if($bid['status'] === 'accepted')
                                            <x-filament::badge color="success" class="mt-1">Diterima</x-filament::badge>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rfqItems as $rfqItem)
                                <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-200 font-medium bg-gray-50/50 dark:bg-gray-700/30">
                                        <div>{{ $rfqItem['description'] }}</div>
                                        <div class="text-xs text-gray-400">Qty: {{ $rfqItem['quantity'] }}</div>
                                    </td>
                                    @foreach($comparisonMatrix as $bid)
                                        @php $item = $bid['items'][$rfqItem['id']] ?? null; @endphp
                                        <td class="px-4 py-3 text-center {{ $bid['status'] === 'accepted' ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                            @if($item && $item['unit_price'])
                                                <div class="font-semibold text-gray-800 dark:text-gray-100">
                                                    {{ number_format($item['unit_price'], 0, ',', '.') }}
                                                </div>
                                                <div class="text-xs text-gray-400">
                                                    Total: {{ number_format($item['total_price'], 0, ',', '.') }}
                                                </div>
                                                @if($item['delivery_days'])
                                                    <div class="text-xs text-gray-400">
                                                        {{ $item['delivery_days'] }} hari
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            {{-- Summary row --}}
                            <tr class="border-t-2 border-gray-300 dark:border-gray-600 font-bold">
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100 text-sm uppercase">
                                    Total
                                </td>
                                @foreach($comparisonMatrix as $bid)
                                    <td class="px-4 py-3 text-center {{ $bid['status'] === 'accepted' ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                        <span class="text-lg text-gray-900 dark:text-white">
                                            {{ number_format($bid['total_amount'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Delivery time --}}
                            <tr>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-medium bg-gray-50/50 dark:bg-gray-700/30">
                                    Estimasi Pengiriman
                                </td>
                                @foreach($comparisonMatrix as $bid)
                                    <td class="px-4 py-3 text-center {{ $bid['status'] === 'accepted' ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                        {{ $bid['delivery_lead_time_days'] ? $bid['delivery_lead_time_days'] . ' hari' : '—' }}
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Evaluation score --}}
                            <tr>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-medium bg-gray-50/50 dark:bg-gray-700/30">
                                    Skor Evaluasi
                                </td>
                                @foreach($comparisonMatrix as $bid)
                                    <td class="px-4 py-3 text-center {{ $bid['status'] === 'accepted' ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                        @if($bid['evaluation_score'])
                                            <x-filament::badge :color="$bid['evaluation_score'] >= 80 ? 'success' : ($bid['evaluation_score'] >= 60 ? 'warning' : 'danger')">
                                                {{ $bid['evaluation_score'] }}
                                            </x-filament::badge>
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>

                            {{-- Notes --}}
                            <tr>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-medium bg-gray-50/50 dark:bg-gray-700/30">
                                    Catatan
                                </td>
                                @foreach($comparisonMatrix as $bid)
                                    <td class="px-4 py-3 text-center text-xs text-gray-500 dark:text-gray-400 {{ $bid['status'] === 'accepted' ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                                        {{ $bid['notes'] ? Str::limit($bid['notes'], 100) : '—' }}
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Best bid highlight --}}
                @php
                    $bestBid = collect($comparisonMatrix)->sortBy(function($bid) {
                        return ($bid['evaluation_score'] ? -$bid['evaluation_score'] : 0) + ($bid['total_amount'] / 1000000);
                    })->first();
                @endphp

                @if($bestBid)
                    <div class="mt-6 p-4 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-700 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">🏆</span>
                            <div>
                                <p class="font-bold text-primary-700 dark:text-primary-300">
                                    Penawaran Terbaik: {{ $bestBid['supplier_name'] }} ({{ $bestBid['bid_number'] }})
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Total: {{ number_format($bestBid['total_amount'], 0, ',', '.') }}
                                    · Skor: {{ $bestBid['evaluation_score'] ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <div class="text-center py-12 text-gray-400 dark:text-gray-500">
                    <p class="text-lg">Belum ada penawaran yang dikirimkan untuk RFQ ini.</p>
                </div>
            @endif
        @else
            <div class="text-center py-16 text-gray-400 dark:text-gray-500">
                <div class="text-5xl mb-4">📊</div>
                <p class="text-lg font-medium text-gray-600 dark:text-gray-300">Pilih RFQ untuk melihat perbandingan penawaran</p>
                <p class="text-sm mt-2">Gunakan dropdown di atas untuk memilih RFQ yang memiliki penawaran masuk.</p>
            </div>
        @endif
    </div>
</div>
