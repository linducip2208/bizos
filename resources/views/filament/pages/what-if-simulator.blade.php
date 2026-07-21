<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Scenario Selector --}}
        <x-filament::section>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <x-filament::select
                    wire:model.live="simType"
                    label="Jenis Simulasi"
                    :options="[
                        'salary' => 'Kenaikan Gaji',
                        'machine' => 'Tambah Kapasitas Mesin',
                        'price' => 'Perubahan Harga Produk',
                    ]"
                />

                @if($simType === 'salary')
                    <x-filament::input.wrapper label="Persentase Kenaikan (%)">
                        <x-filament::input type="number" wire:model="increasePercent" min="1" max="100" step="0.5" />
                    </x-filament::input.wrapper>
                    <div></div>
                @elseif($simType === 'machine')
                    @php $workCenters = \App\Models\WorkCenter::pluck('name', 'id')->toArray(); @endphp
                    <x-filament::select wire:model="workCenterId" label="Work Center" :options="$workCenters" />
                    <x-filament::input.wrapper label="Tambahan Kapasitas (unit/jam)">
                        <x-filament::input type="number" wire:model="additionalCapacity" min="1" step="1" />
                    </x-filament::input.wrapper>
                @elseif($simType === 'price')
                    @php $products = \App\Models\Product::pluck('name', 'id')->toArray(); @endphp
                    <x-filament::select wire:model="productId" label="Produk" :options="$products" />
                    <x-filament::input.wrapper label="Harga Baru (Rp)">
                        <x-filament::input type="number" wire:model="newPrice" min="0" step="1000" />
                    </x-filament::input.wrapper>
                @endif

                <x-filament::button wire:click="simulate" color="primary" icon="heroicon-o-play" class="w-full">
                    Jalankan Simulasi
                </x-filament::button>
            </div>
        </x-filament::section>

        @if($hasSimulated && $result)
            @if($simType === 'salary')
                {{-- Salary Simulation Results --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-filament::section class="p-4 !rounded-xl">
                        <div class="text-center">
                            <div class="text-sm text-gray-500">Total Gaji Saat Ini</div>
                            <div class="text-2xl font-extrabold text-gray-700">{{ Number::currency($result['current_payroll_total'], 'IDR', 'id') }}</div>
                        </div>
                    </x-filament::section>
                    <x-filament::section class="p-4 !rounded-xl">
                        <div class="text-center">
                            <div class="text-sm text-gray-500">Total Gaji Baru</div>
                            <div class="text-2xl font-extrabold text-indigo-600">{{ Number::currency($result['new_payroll_total'], 'IDR', 'id') }}</div>
                        </div>
                    </x-filament::section>
                    <x-filament::section class="p-4 !rounded-xl">
                        <div class="text-center">
                            <div class="text-sm text-gray-500">Kenaikan Bulanan</div>
                            <div class="text-2xl font-extrabold text-rose-600">{{ Number::currency($result['increase_amount'], 'IDR', 'id') }}</div>
                        </div>
                    </x-filament::section>
                </div>
                <x-filament::section>
                    <x-slot name="heading">Detail Simulasi Kenaikan Gaji {{ $result['increase_percent'] }}%</x-slot>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div><span class="text-gray-500">Karyawan Terdampak:</span> <span class="font-bold">{{ Number::format($result['affected_employees']) }}</span></div>
                        <div><span class="text-gray-500">Rata-rata Gaji Saat Ini:</span> <span class="font-bold">{{ Number::currency($result['avg_current_salary'], 'IDR', 'id') }}</span></div>
                        <div><span class="text-gray-500">Rata-rata Gaji Baru:</span> <span class="font-bold">{{ Number::currency($result['avg_new_salary'], 'IDR', 'id') }}</span></div>
                        <div><span class="text-gray-500">Dampak PPh 21:</span> <span class="font-bold text-rose-600">{{ Number::currency($result['pph21_impact'], 'IDR', 'id') }}</span></div>
                        <div><span class="text-gray-500">Kenaikan Budget Bulanan:</span> <span class="font-bold">{{ Number::currency($result['monthly_budget_increase'], 'IDR', 'id') }}</span></div>
                        <div><span class="text-gray-500">Kenaikan Budget Tahunan:</span> <span class="font-bold">{{ Number::currency($result['annual_budget_increase'], 'IDR', 'id') }}</span></div>
                        <div><span class="text-gray-500">Dampak Net Profit:</span> <span class="font-bold {{ $result['impact_on_net_profit_percent'] > 10 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $result['impact_on_net_profit_percent'] }}%</span></div>
                    </div>
                </x-filament::section>

            @elseif($simType === 'machine')
                {{-- Machine Simulation Results --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-filament::section class="p-4 !rounded-xl">
                        <div class="text-center">
                            <div class="text-sm text-gray-500">Kapasitas Saat Ini</div>
                            <div class="text-2xl font-extrabold text-gray-700">{{ Number::format($result['current_capacity_per_hour']) }} unit/jam</div>
                        </div>
                    </x-filament::section>
                    <x-filament::section class="p-4 !rounded-xl">
                        <div class="text-center">
                            <div class="text-sm text-gray-500">Kapasitas Baru</div>
                            <div class="text-2xl font-extrabold text-indigo-600">{{ Number::format($result['new_capacity_per_hour']) }} unit/jam</div>
                        </div>
                    </x-filament::section>
                    <x-filament::section class="p-4 !rounded-xl">
                        <div class="text-center">
                            <div class="text-sm text-gray-500">ROI</div>
                            <div class="text-2xl font-extrabold {{ $result['roi_months'] <= 24 ? 'text-emerald-600' : 'text-rose-600' }}">{{ Number::format($result['roi_months']) }} bulan</div>
                        </div>
                    </x-filament::section>
                </div>
                <x-filament::section>
                    <x-slot name="heading">Detail Simulasi Tambahan Kapasitas {{ $result['work_center'] }}</x-slot>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div><span class="text-gray-500">Kenaikan Kapasitas:</span> <span class="font-bold text-emerald-600">+{{ $result['capacity_increase_percent'] }}%</span></div>
                        <div><span class="text-gray-500">Tambahan Harian:</span> <span class="font-bold">{{ Number::format($result['additional_daily_production']) }} unit</span></div>
                        <div><span class="text-gray-500">Tambahan Bulanan:</span> <span class="font-bold">{{ Number::format($result['additional_monthly_production']) }} unit</span></div>
                        <div><span class="text-gray-500">Tambahan Tahunan:</span> <span class="font-bold">{{ Number::format($result['additional_yearly_production']) }} unit</span></div>
                        <div><span class="text-gray-500">Estimasi Investasi:</span> <span class="font-bold">{{ Number::currency($result['estimated_investment'], 'IDR', 'id') }}</span></div>
                        <div><span class="text-gray-500">Estimasi Revenue/Bulan:</span> <span class="font-bold text-emerald-600">{{ Number::currency($result['estimated_monthly_revenue'], 'IDR', 'id') }}</span></div>
                        <div><span class="text-gray-500">Break-Even Units:</span> <span class="font-bold">{{ Number::format($result['break_even_units']) }} unit</span></div>
                    </div>
                </x-filament::section>

            @elseif($simType === 'price')
                {{-- Price Change Results --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-filament::section class="p-4 !rounded-xl">
                        <div class="text-center">
                            <div class="text-sm text-gray-500">Harga Saat Ini</div>
                            <div class="text-2xl font-extrabold text-gray-700">{{ Number::currency($result['current_price'], 'IDR', 'id') }}</div>
                        </div>
                    </x-filament::section>
                    <x-filament::section class="p-4 !rounded-xl">
                        <div class="text-center">
                            <div class="text-sm text-gray-500">Harga Baru</div>
                            <div class="text-2xl font-extrabold text-indigo-600">{{ Number::currency($result['new_price'], 'IDR', 'id') }}</div>
                        </div>
                    </x-filament::section>
                    <x-filament::section class="p-4 !rounded-xl">
                        <div class="text-center">
                            <div class="text-sm text-gray-500">Perubahan Laba</div>
                            <div class="text-2xl font-extrabold {{ $result['profit_change'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ Number::currency($result['profit_change'], 'IDR', 'id') }}</div>
                        </div>
                    </x-filament::section>
                </div>
                <x-filament::section>
                    <x-slot name="heading">Detail Simulasi Perubahan Harga — {{ $result['product_name'] }}</x-slot>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div><span class="text-gray-500">Perubahan Harga:</span> <span class="font-bold {{ $result['price_change_percent'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $result['price_change_percent'] > 0 ? '+' : '' }}{{ $result['price_change_percent'] }}%</span></div>
                        <div><span class="text-gray-500">Volume Saat Ini/bulan:</span> <span class="font-bold">{{ Number::format($result['current_monthly_volume']) }}</span></div>
                        <div><span class="text-gray-500">Estimasi Volume Baru:</span> <span class="font-bold">{{ Number::format($result['estimated_monthly_volume']) }}</span></div>
                        <div><span class="text-gray-500">Perubahan Volume:</span> <span class="font-bold {{ $result['volume_change_percent'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $result['volume_change_percent'] > 0 ? '+' : '' }}{{ $result['volume_change_percent'] }}%</span></div>
                        <div><span class="text-gray-500">Revenue Saat Ini:</span> <span class="font-bold">{{ Number::currency($result['current_revenue'], 'IDR', 'id') }}</span></div>
                        <div><span class="text-gray-500">Revenue Baru:</span> <span class="font-bold">{{ Number::currency($result['new_revenue'], 'IDR', 'id') }}</span></div>
                        <div><span class="text-gray-500">Perubahan Revenue:</span> <span class="font-bold {{ $result['revenue_change'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ Number::currency($result['revenue_change'], 'IDR', 'id') }}</span></div>
                        <div><span class="text-gray-500">Margin Saat Ini:</span> <span class="font-bold">{{ $result['current_margin_percent'] }}%</span></div>
                        <div><span class="text-gray-500">Margin Baru:</span> <span class="font-bold {{ $result['new_margin_percent'] > $result['current_margin_percent'] ? 'text-emerald-600' : 'text-rose-600' }}">{{ $result['new_margin_percent'] }}%</span></div>
                        <div><span class="text-gray-500">Break-Even Volume:</span> <span class="font-bold">{{ Number::format($result['break_even_volume']) }} unit</span></div>
                    </div>
                </x-filament::section>
            @endif

            {{-- Bar Chart Impact --}}
            <x-filament::section>
                <x-slot name="heading">Visualisasi Perbandingan</x-slot>
                <div class="h-64">
                    <canvas id="impactChart"></canvas>
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="p-12 text-center">
                    <x-filament::icon icon="heroicon-o-calculator" class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                    <p class="text-gray-500">Pilih jenis simulasi, isi parameter, lalu klik "Jalankan Simulasi".</p>
                </div>
            </x-filament::section>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    @if($hasSimulated && $result)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('impactChart')?.getContext('2d');
            if (!ctx) return;

            let labels = [], beforeData = [], afterData = [];
            @if($simType === 'salary')
                labels = ['Total Gaji', 'Budget Bulanan', 'Rata-rata Gaji'];
                beforeData = [{{ $result['current_payroll_total'] }}, {{ $result['current_payroll_total'] }}, {{ $result['avg_current_salary'] }}];
                afterData = [{{ $result['new_payroll_total'] }}, {{ $result['new_payroll_total'] }}, {{ $result['avg_new_salary'] }}];
            @elseif($simType === 'machine')
                labels = ['Kapasitas/jam', 'Produksi Bulanan', 'Revenue Bulanan'];
                beforeData = [{{ $result['current_capacity_per_hour'] }}, {{ $result['current_capacity_per_hour'] * 8 * 22 }}, {{ $result['current_capacity_per_hour'] * 8 * 22 * 100000 }}];
                afterData = [{{ $result['new_capacity_per_hour'] }}, {{ $result['additional_monthly_production'] + $result['current_capacity_per_hour'] * 8 * 22 }}, {{ $result['estimated_monthly_revenue'] }}];
            @elseif($simType === 'price')
                labels = ['Harga', 'Volume/bulan', 'Revenue', 'Laba'];
                beforeData = [{{ $result['current_price'] }}, {{ $result['current_monthly_volume'] }}, {{ $result['current_revenue'] }}, {{ $result['current_profit'] }}];
                afterData = [{{ $result['new_price'] }}, {{ $result['estimated_monthly_volume'] }}, {{ $result['new_revenue'] }}, {{ $result['new_profit'] }}];
            @endif

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Sebelum', data: beforeData, backgroundColor: 'rgba(156,163,175,0.8)', borderRadius: 6 },
                        { label: 'Sesudah', data: afterData, backgroundColor: 'rgba(99,102,241,0.8)', borderRadius: 6 },
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        });
    </script>
    @endif
    @endpush
</x-filament-panels::page>
