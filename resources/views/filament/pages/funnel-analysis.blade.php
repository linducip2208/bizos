<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <div></div>
            <div class="flex gap-1">
                @foreach(['week' => 'Mingguan', 'month' => 'Bulanan', 'quarter' => 'Kuartal', 'year' => 'Tahunan'] as $key => $label)
                    <x-filament::button
                        size="xs"
                        :color="$period === $key ? 'primary' : 'gray'"
                        wire:click="setPeriod('{{ $key }}')"
                    >
                        {{ $label }}
                    </x-filament::button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Recruitment Funnel --}}
            <x-filament::section>
                <x-slot name="heading">Funnel Rekrutmen</x-slot>
                <div class="h-64">
                    <canvas id="recruitmentFunnelChart"></canvas>
                </div>
                <div class="mt-4 text-xs space-y-1">
                    <div class="flex justify-between"><span>Apply Rate</span><span class="font-semibold">{{ $recruitmentFunnel['conversion_rates']['apply_rate'] ?? 0 }}%</span></div>
                    <div class="flex justify-between"><span>Screen Rate</span><span class="font-semibold">{{ $recruitmentFunnel['conversion_rates']['screen_rate'] ?? 0 }}%</span></div>
                    <div class="flex justify-between"><span>Interview Rate</span><span class="font-semibold">{{ $recruitmentFunnel['conversion_rates']['interview_rate'] ?? 0 }}%</span></div>
                    <div class="flex justify-between"><span>Offer Rate</span><span class="font-semibold">{{ $recruitmentFunnel['conversion_rates']['offer_rate'] ?? 0 }}%</span></div>
                    <div class="flex justify-between"><span>Hire Rate</span><span class="font-semibold text-emerald-600">{{ $recruitmentFunnel['conversion_rates']['hire_rate'] ?? 0 }}%</span></div>
                    <hr class="my-1">
                    <div class="flex justify-between"><span>Overall</span><span class="font-bold text-emerald-600">{{ $recruitmentFunnel['conversion_rates']['overall_rate'] ?? 0 }}%</span></div>
                </div>
            </x-filament::section>

            {{-- Sales Funnel --}}
            <x-filament::section>
                <x-slot name="heading">Funnel Penjualan</x-slot>
                <div class="h-64">
                    <canvas id="salesFunnelChart"></canvas>
                </div>
                <div class="mt-4 text-xs space-y-1">
                    <div class="flex justify-between"><span>Qualification Rate</span><span class="font-semibold">{{ $salesFunnel['conversion_rates']['qualification_rate'] ?? 0 }}%</span></div>
                    <div class="flex justify-between"><span>Proposal Rate</span><span class="font-semibold">{{ $salesFunnel['conversion_rates']['proposal_rate'] ?? 0 }}%</span></div>
                    <div class="flex justify-between"><span>Negotiation Rate</span><span class="font-semibold">{{ $salesFunnel['conversion_rates']['negotiation_rate'] ?? 0 }}%</span></div>
                    <hr class="my-1">
                    <div class="flex justify-between"><span>Win Rate</span><span class="font-bold text-emerald-600">{{ $salesFunnel['conversion_rates']['win_rate'] ?? 0 }}%</span></div>
                </div>
            </x-filament::section>

            {{-- Purchase Funnel --}}
            <x-filament::section>
                <x-slot name="heading">Funnel Pembelian</x-slot>
                <div class="h-64">
                    <canvas id="purchaseFunnelChart"></canvas>
                </div>
                <div class="mt-4 text-xs space-y-1">
                    <div class="flex justify-between"><span>PR to PO</span><span class="font-semibold">{{ $purchaseFunnel['conversion_rates']['pr_to_po'] ?? 0 }}%</span></div>
                    <div class="flex justify-between"><span>PO to GRN</span><span class="font-semibold">{{ $purchaseFunnel['conversion_rates']['po_to_grn'] ?? 0 }}%</span></div>
                    <div class="flex justify-between"><span>GRN to Invoice</span><span class="font-semibold">{{ $purchaseFunnel['conversion_rates']['grn_to_invoice'] ?? 0 }}%</span></div>
                    <hr class="my-1">
                    <div class="flex justify-between"><span>Invoice to Paid</span><span class="font-bold text-emerald-600">{{ $purchaseFunnel['conversion_rates']['invoice_to_paid'] ?? 0 }}%</span></div>
                </div>
            </x-filament::section>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const funnelPlugin = {
                id: 'funnelLabels',
                afterDatasetsDraw(chart) {
                    const { ctx, data } = chart;
                    const meta = chart.getDatasetMeta(0);
                    const labels = data.labels || [];
                    const values = data.datasets[0].data;
                    ctx.save();
                    ctx.font = 'bold 11px Inter, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.fillStyle = '#6b7280';
                    meta.data.forEach((bar, i) => {
                        if (values[i] > 0) {
                            const x = bar.x;
                            const y = bar.y - 8;
                            ctx.fillText(values[i], x, y);
                        }
                    });
                    ctx.restore();
                }
            };

            ['recruitmentFunnelChart', 'salesFunnelChart', 'purchaseFunnelChart'].forEach(id => {
                const el = document.getElementById(id);
                if (!el) return;
                const type = id.replace('FunnelChart', '') + 'Funnel';
                let data;
                @php
                    $funnels = [
                        'recruitment' => $recruitmentFunnel,
                        'sales' => $salesFunnel,
                        'purchase' => $purchaseFunnel,
                    ];
                @endphp

                const funnelData = @json($funnels);

                if (id === 'recruitmentFunnelChart') data = funnelData.recruitment;
                else if (id === 'salesFunnelChart') data = funnelData.sales;
                else data = funnelData.purchase;

                const stages = data?.stages || [];

                new Chart(el.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: stages.map(s => s.name),
                        datasets: [{
                            data: stages.map(s => s.count),
                            backgroundColor: [
                                'rgba(99,102,241,0.9)',
                                'rgba(99,102,241,0.75)',
                                'rgba(99,102,241,0.6)',
                                'rgba(99,102,241,0.45)',
                                'rgba(16,185,129,0.7)',
                                'rgba(239,68,68,0.7)',
                            ],
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true } }
                    },
                    plugins: [funnelPlugin]
                });
            });
        });
    </script>
    @endpush
</x-filament-panels::page>
