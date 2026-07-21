<div class="p-6 space-y-6">
    @if(!$hasRun)
    <div class="text-center py-12 text-gray-400">
        <p class="text-lg">Pilih perusahaan dan klik "Jalankan MRP" untuk memulai.</p>
    </div>
    @else
    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Pengecualian</div>
            <div class="text-3xl font-bold text-amber-600">{{ $exceptions['total_exceptions'] ?? 0 }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <div class="text-sm text-gray-500 dark:text-gray-400">Late Orders</div>
            <div class="text-3xl font-bold text-red-600">{{ count($exceptions['late_orders'] ?? []) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <div class="text-sm text-gray-500 dark:text-gray-400">Stock < Safety</div>
            <div class="text-3xl font-bold text-orange-600">{{ count($exceptions['stock_below_safety'] ?? []) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
            <div class="text-sm text-gray-500 dark:text-gray-400">Saran Pembelian</div>
            <div class="text-3xl font-bold text-indigo-600">{{ count($purchaseSuggestions) }}</div>
        </div>
    </div>

    {{-- MRP Exceptions --}}
    @if(!empty($exceptions['late_orders']))
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold flex items-center gap-2">
            <span class="text-red-500">⚠️</span> Late Production Orders
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400">
                    <tr>
                        <th class="text-left px-4 py-2">PO Number</th>
                        <th class="text-left px-4 py-2">Produk</th>
                        <th class="text-left px-4 py-2">Planned End</th>
                        <th class="text-left px-4 py-2">Hari Terlambat</th>
                        <th class="text-left px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exceptions['late_orders'] as $lo)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="px-4 py-2 font-mono text-xs">{{ $lo['po_number'] }}</td>
                        <td class="px-4 py-2">{{ $lo['product_name'] }}</td>
                        <td class="px-4 py-2">{{ $lo['planned_end'] }}</td>
                        <td class="px-4 py-2 text-red-600 font-bold">{{ $lo['days_late'] }}</td>
                        <td class="px-4 py-2"><span class="px-2 py-1 rounded text-xs bg-{{ $lo['status'] === 'in_progress' ? 'blue-100 text-blue-700' : 'amber-100 text-amber-700' }}">{{ $lo['status'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(!empty($exceptions['stock_below_safety']))
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold flex items-center gap-2">
            <span class="text-orange-500">📉</span> Stok di Bawah Safety Stock
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400">
                    <tr>
                        <th class="text-left px-4 py-2">Produk</th>
                        <th class="text-left px-4 py-2">Stok Saat Ini</th>
                        <th class="text-left px-4 py-2">Safety Stock</th>
                        <th class="text-left px-4 py-2">Kekurangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exceptions['stock_below_safety'] as $ss)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="px-4 py-2">{{ $ss['product_name'] }} <span class="text-gray-400 text-xs">({{ $ss['product_code'] }})</span></td>
                        <td class="px-4 py-2">{{ number_format($ss['current_stock'], 2) }}</td>
                        <td class="px-4 py-2">{{ number_format($ss['safety_stock'], 2) }}</td>
                        <td class="px-4 py-2 text-red-600 font-bold">{{ number_format($ss['shortage'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(!empty($exceptions['capacity_overload']))
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold flex items-center gap-2">
            <span class="text-purple-500">🏭</span> Kapasitas Overload
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400">
                    <tr>
                        <th class="text-left px-4 py-2">Work Center</th>
                        <th class="text-left px-4 py-2">Utilisasi</th>
                        <th class="text-left px-4 py-2">Kapasitas (jam)</th>
                        <th class="text-left px-4 py-2">Planned (jam)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($exceptions['capacity_overload'] as $co)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="px-4 py-2">{{ $co['work_center'] }}</td>
                        <td class="px-4 py-2 text-red-600 font-bold">{{ $co['utilization_percent'] }}%</td>
                        <td class="px-4 py-2">{{ $co['capacity_hours'] }}</td>
                        <td class="px-4 py-2">{{ $co['planned_hours'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Purchase Suggestions --}}
    @if(!empty($purchaseSuggestions))
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold flex items-center gap-2">
            <span class="text-indigo-500">🛒</span> Saran Pembelian
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400">
                    <tr>
                        <th class="text-left px-4 py-2">Produk</th>
                        <th class="text-left px-4 py-2">Stok</th>
                        <th class="text-left px-4 py-2">Qty Dibutuhkan</th>
                        <th class="text-left px-4 py-2">Supplier</th>
                        <th class="text-left px-4 py-2">Order By</th>
                        <th class="text-left px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseSuggestions as $ps)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="px-4 py-2">{{ $ps['product_name'] }} <span class="text-gray-400 text-xs">({{ $ps['product_code'] }})</span></td>
                        <td class="px-4 py-2">{{ number_format($ps['current_stock'], 2) }}</td>
                        <td class="px-4 py-2 font-bold">{{ number_format($ps['quantity_needed'], 2) }}</td>
                        <td class="px-4 py-2">{{ $ps['suggested_supplier_name'] }}</td>
                        <td class="px-4 py-2">{{ $ps['suggested_order_date'] }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ url('/admin/purchase-requisitions/create') }}" class="text-indigo-600 hover:underline text-xs font-semibold">Buat PR →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Production Suggestions --}}
    @if(!empty($productionSuggestions))
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 font-semibold flex items-center gap-2">
            <span class="text-sky-500">🏭</span> Saran Produksi
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400">
                    <tr>
                        <th class="text-left px-4 py-2">Produk</th>
                        <th class="text-left px-4 py-2">BOM</th>
                        <th class="text-left px-4 py-2">Qty Disarankan</th>
                        <th class="text-left px-4 py-2">Mulai</th>
                        <th class="text-left px-4 py-2">Material Ready</th>
                        <th class="text-left px-4 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productionSuggestions as $prod)
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="px-4 py-2">{{ $prod['product_name'] }} <span class="text-gray-400 text-xs">({{ $prod['product_code'] }})</span></td>
                        <td class="px-4 py-2 text-xs">{{ $prod['bom_name'] }}</td>
                        <td class="px-4 py-2 font-bold">{{ number_format($prod['suggested_quantity'], 2) }}</td>
                        <td class="px-4 py-2">{{ $prod['suggested_start_date'] }}</td>
                        <td class="px-4 py-2">
                            @if($prod['material_availability'])
                            <span class="text-green-600 font-semibold text-xs">✓ Ready</span>
                            @else
                            <span class="text-red-600 font-semibold text-xs">✗ Kurang</span>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <a href="{{ url('/admin/production-orders/create') }}" class="text-indigo-600 hover:underline text-xs font-semibold">Buat PO →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(empty($exceptions['late_orders']) && empty($exceptions['stock_below_safety']) && empty($purchaseSuggestions) && empty($productionSuggestions))
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-8 text-center">
        <p class="text-green-700 dark:text-green-300 text-lg font-semibold">Semua dalam kondisi optimal!</p>
        <p class="text-green-600 dark:text-green-400 text-sm mt-1">Tidak ada shortage, late orders, atau pengecualian MRP.</p>
    </div>
    @endif
    @endif
</div>
