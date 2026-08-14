@php
use App\Models\Budget;
$budgets = \App\Models\Budget::where('company_id', auth()->user()->company_id)
    ->orderByDesc('fiscal_year')
    ->orderBy('name')
    ->get();
$selectedBudgetId = request('budget_id') ?? $budgets->first()?->id;
$budget = $selectedBudgetId ? Budget::find($selectedBudgetId) : null;
$items = $budget ? \App\Models\BudgetItem::where('budget_id', $selectedBudgetId)->with('coa')->orderBy('coa_id')->get() : collect();
$totalPlanned = $items->sum('planned_amount');
$totalActual = $items->sum('actual_amount');
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header: Budget Selector --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-bold">Budget vs Actual</h1>
            <div>
                <select id="budgetSelector" onchange="window.location.href='?budget_id='+this.value"
                    class="fi-input rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm">
                    <option value="">-- Pilih Anggaran --</option>
                    @foreach($budgets as $b)
                        <option value="{{ $b->id }}" @selected($selectedBudgetId == $b->id)>
                            {{ $b->name }} (FY {{ $b->fiscal_year }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($budget)
            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Anggaran</p>
                    <p class="text-2xl font-bold text-blue-600">Rp {{ number_format($totalPlanned, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Total Realisasi</p>
                    <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalActual, 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Selisih</p>
                    <p class="text-2xl font-bold {{ $totalPlanned - $totalActual >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        Rp {{ number_format(abs($totalPlanned - $totalActual), 0, ',', '.') }}
                    </p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">Persentase Terserap</p>
                    <p class="text-2xl font-bold {{ $totalPlanned > 0 && ($totalActual / $totalPlanned) <= 1 ? 'text-indigo-600' : 'text-red-600' }}">
                        {{ $totalPlanned > 0 ? round(($totalActual / $totalPlanned) * 100, 1) : 0 }}%
                    </p>
                </div>
            </div>

            {{-- Bar Chart --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold">Perbandingan Anggaran vs Realisasi</h2>
                <div class="relative w-full" style="height: 400px;">
                    <canvas id="budgetVsActualChart"></canvas>
                </div>
            </div>

            {{-- Detail Table --}}
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="px-4 py-3 font-semibold">Kode COA</th>
                                <th class="px-4 py-3 font-semibold">Nama COA</th>
                                <th class="px-4 py-3 font-semibold">Deskripsi</th>
                                <th class="px-4 py-3 font-semibold text-right">Anggaran</th>
                                <th class="px-4 py-3 font-semibold text-right">Realisasi</th>
                                <th class="px-4 py-3 font-semibold text-right">Selisih</th>
                                <th class="px-4 py-3 font-semibold text-right">% Terserap</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($items as $item)
                            @php
                                $pct = $item->planned_amount > 0 ? round(($item->actual_amount / $item->planned_amount) * 100, 1) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-mono text-xs">{{ $item->coa?->code }}</td>
                                <td class="px-4 py-2.5">{{ $item->coa?->name }}</td>
                                <td class="px-4 py-2.5">{{ $item->description }}</td>
                                <td class="px-4 py-2.5 text-right">Rp {{ number_format($item->planned_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-2.5 text-right">Rp {{ number_format($item->actual_amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-2.5 text-right {{ $item->variance >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    Rp {{ number_format(abs($item->variance), 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2.5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-20 h-2 rounded-full bg-gray-200 overflow-hidden">
                                            <div class="h-full rounded-full {{ $pct <= 100 ? 'bg-indigo-500' : 'bg-red-500' }}"
                                                style="width: {{ min($pct, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs w-12 text-right">{{ $pct }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t bg-gray-100 font-semibold">
                            <tr>
                                <td class="px-4 py-3" colspan="3">Total</td>
                                <td class="px-4 py-3 text-right">Rp {{ number_format($totalPlanned, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">Rp {{ number_format($totalActual, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right {{ $totalPlanned - $totalActual >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    Rp {{ number_format(abs($totalPlanned - $totalActual), 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ $totalPlanned > 0 ? round(($totalActual / $totalPlanned) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Chart.js --}}
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const ctx = document.getElementById('budgetVsActualChart');
                    if (!ctx) return;

                    const labels = @json($items->pluck('description'));
                    const plannedData = @json($items->pluck('planned_amount'));
                    const actualData = @json($items->pluck('actual_amount'));

                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [
                                {
                                    label: 'Anggaran',
                                    data: plannedData,
                                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                                    borderColor: 'rgb(99, 102, 241)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                },
                                {
                                    label: 'Realisasi',
                                    data: actualData,
                                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                                    borderColor: 'rgb(16, 185, 129)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) label += ': ';
                                            label += 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return 'Rp ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                                        }
                                    }
                                },
                                x: {
                                    ticks: {
                                        maxRotation: 45,
                                        minRotation: 0,
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        @else
            <div class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm">
                <p class="text-gray-500">Pilih anggaran untuk melihat perbandingan budget vs actual.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
