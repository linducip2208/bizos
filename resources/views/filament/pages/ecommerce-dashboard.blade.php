<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Period Filter --}}
        <div class="flex justify-end">
            <x-filament::input.wrapper>
                <x-filament::input.select wire:model.live="period" class="text-sm">
                    <option value="today">Hari Ini</option>
                    <option value="yesterday">Kemarin</option>
                    <option value="this_week">Minggu Ini</option>
                    <option value="this_month">Bulan Ini</option>
                    <option value="last_month">Bulan Lalu</option>
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Pesanan</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $performance['total_orders'] ?? 0 }}</div>
                <div class="text-xs text-gray-400 mt-1">Semua channel</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Revenue</div>
                <div class="text-2xl font-bold text-green-600">Rp {{ number_format($performance['total_revenue'] ?? 0, 0, ',', '.') }}</div>
                <div class="text-xs text-gray-400 mt-1">Pendapatan channel</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Tersinkron ke POS</div>
                <div class="text-2xl font-bold text-blue-600">{{ $syncStatusSummary['synced'] ?? 0 }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $syncStatusSummary['pending'] ?? 0 }} menunggu sinkron</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-sm text-gray-500 dark:text-gray-400">Channel Aktif</div>
                <div class="text-2xl font-bold text-purple-600">{{ count($channels) }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ count(array_filter($channels, fn($c) => $c['is_active'])) }} channel online</div>
            </div>
        </div>

        {{-- Per Channel Performance --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performa per Channel</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Channel</th>
                            <th class="px-4 py-3">Pesanan</th>
                            <th class="px-4 py-3">Revenue</th>
                            <th class="px-4 py-3">Tersinkron</th>
                            <th class="px-4 py-3">Menunggu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($performance['per_channel'] ?? []) as $ch)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium capitalize">{{ $ch['channel_name'] }}</td>
                                <td class="px-4 py-3">{{ $ch['total_orders'] }}</td>
                                <td class="px-4 py-3">Rp {{ number_format($ch['total_revenue'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="text-green-600 font-semibold">{{ $ch['synced'] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-yellow-600 font-semibold">{{ $ch['pending_sync'] }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Products --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top 10 Produk</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Qty Terjual</th>
                            <th class="px-4 py-3">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($performance['top_products'] ?? []) as $product)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium">{{ $product['product_name'] }}</td>
                                <td class="px-4 py-3">{{ $product['total_qty'] }}</td>
                                <td class="px-4 py-3">Rp {{ number_format($product['total_revenue'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SKU Matching Status --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">SKU Matching</h3>
            @if(count($unmatchedSkus) > 0)
                <div class="mb-3 text-sm text-yellow-600 dark:text-yellow-400">
                    {{ count($unmatchedSkus) }} SKU belum dicocokkan
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Channel SKU</th>
                                <th class="px-4 py-3">Produk BizOS (Suggestion)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unmatchedSkus as $item)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-4 py-3">{{ $item['channel_sku'] }}</td>
                                    <td class="px-4 py-3">
                                        @if($item['suggested_product'])
                                            <span class="text-blue-600">{{ $item['suggested_product']->name }}</span>
                                        @else
                                            <span class="text-red-400">Tidak ditemukan</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-green-600 text-sm">Semua SKU sudah cocok!</div>
            @endif
        </div>

        {{-- Channel List --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Channel Terhubung</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($channels as $ch)
                    <div class="border rounded-lg p-4 {{ $ch['is_active'] ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/20' : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800' }}">
                        <div class="flex items-center justify-between">
                            <span class="font-semibold capitalize">{{ $ch['channel_name'] }}</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $ch['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $ch['is_active'] ? 'Online' : 'Offline' }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 mt-2">
                            {{ $ch['orders_count'] ?? 0 }} pesanan
                        </div>
                        <div class="text-xs text-gray-400">
                            Sinkron terakhir: {{ $ch['last_sync_at'] ?? 'Belum pernah' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-panels::page>
