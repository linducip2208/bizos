<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex justify-end gap-2">
            <x-filament::button wire:click="loadData" size="sm" color="gray" icon="heroicon-o-arrow-path">
                Refresh
            </x-filament::button>
        </div>

        {{-- Segment Summary Cards --}}
        @if(!empty($segmentSummary))
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            @foreach($segmentSummary as $seg)
                <x-filament::section class="p-4 !rounded-xl">
                    <div class="text-center">
                        <div class="text-xs text-gray-500 uppercase">{{ $seg['segment'] }}</div>
                        <div class="text-2xl font-extrabold mt-1 text-indigo-600">{{ Number::format($seg['count']) }}</div>
                        <div class="text-xs text-gray-400 mt-0.5">{{ Number::currency($seg['total_revenue'], 'IDR', 'id') }}</div>
                    </div>
                </x-filament::section>
            @endforeach
        </div>
        @endif

        {{-- RFM Scatter + Table --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-filament::section>
                <x-slot name="heading">Scatter Plot RFM</x-slot>
                <div class="h-80">
                    <canvas id="rfmScatterChart"></canvas>
                </div>
            </x-filament::section>
            <x-filament::section>
                <x-slot name="heading">Tabel Segmen RFM</x-slot>
                <div class="overflow-x-auto max-h-80">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b text-left uppercase text-gray-500 sticky top-0 bg-white dark:bg-gray-900">
                                <th class="p-2">Klien</th>
                                <th class="p-2 text-right">R</th>
                                <th class="p-2 text-right">F</th>
                                <th class="p-2 text-right">M</th>
                                <th class="p-2">Segmen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rfmData as $row)
                                <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <td class="p-2">{{ $row['client_name'] }}</td>
                                    <td class="p-2 text-right">
                                        <span class="px-1.5 py-0.5 rounded text-xs font-semibold" style="background: {{ $this->rfmColor($row['r_score']) }}">
                                            {{ $row['r_score'] }}
                                        </span>
                                    </td>
                                    <td class="p-2 text-right">
                                        <span class="px-1.5 py-0.5 rounded text-xs font-semibold" style="background: {{ $this->rfmColor($row['f_score']) }}">
                                            {{ $row['f_score'] }}
                                        </span>
                                    </td>
                                    <td class="p-2 text-right">
                                        <span class="px-1.5 py-0.5 rounded text-xs font-semibold" style="background: {{ $this->rfmColor($row['m_score']) }}">
                                            {{ $row['m_score'] }}
                                        </span>
                                    </td>
                                    <td class="p-2">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $this->segmentBadge($row['segment']) }}">
                                            {{ $row['segment'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('rfmScatterChart')?.getContext('2d');
            if (!ctx) return;

            const data = @json($rfmData);
            const segments = [...new Set(data.map(d => d.segment))];
            const colors = {
                'Champions': 'rgba(16,185,129,0.7)',
                'Loyal': 'rgba(59,130,246,0.7)',
                'Potential': 'rgba(245,158,11,0.7)',
                'At Risk': 'rgba(249,115,22,0.7)',
                'New': 'rgba(139,92,246,0.7)',
                'Lost': 'rgba(239,68,68,0.7)',
            };

            const datasets = segments.map(seg => ({
                label: seg,
                data: data.filter(d => d.segment === seg).map(d => ({ x: d.frequency, y: d.monetary_total })),
                backgroundColor: colors[seg] || 'rgba(128,128,128,0.7)',
                pointRadius: 6,
                pointHoverRadius: 9,
            }));

            new Chart(ctx, {
                type: 'scatter',
                data: { datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const d = data.find(r => r.frequency === ctx.parsed.x && r.monetary_total === ctx.parsed.y);
                                    return d ? d.client_name + ': ' + d.frequency + ' transaksi, Rp' + d.monetary_total.toLocaleString() : '';
                                }
                            }
                        }
                    },
                    scales: {
                        x: { title: { display: true, text: 'Frequency (Jumlah Transaksi)' } },
                        y: { title: { display: true, text: 'Monetary (Total Belanja)' } }
                    }
                }
            });
        });
    </script>
    @endpush
</x-filament-panels::page>
