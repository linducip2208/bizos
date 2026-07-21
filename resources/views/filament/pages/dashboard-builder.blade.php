<x-filament-panels::page>
    <div class="dashboard-builder" x-data="dashboardBuilder()">
        {{-- Top Toolbar --}}
        <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-stone-900 dark:text-white">Dashboard Builder</h1>
                <p class="text-sm text-stone-500 mt-1">Drag & drop widget untuk membangun dashboard kustom Anda.</p>
            </div>
            <div class="flex items-center gap-3">
                <x-filament::button
                    color="gray"
                    icon="heroicon-o-squares-plus"
                    x-on:click="$dispatch('open-modal', { id: 'add-widget-modal' })"
                >
                    Tambah Widget
                </x-filament::button>
                <x-filament::button
                    color="primary"
                    icon="heroicon-o-check"
                    wire:click="saveLayout"
                >
                    Simpan Layout
                </x-filament::button>
            </div>
        </div>

        {{-- Widget Grid --}}
        <div class="widget-grid" style="display:grid; grid-template-columns:repeat(12, 1fr); gap:1rem;">
            @foreach($this->widgets as $widget)
                <div
                    class="widget-card"
                    style="grid-column:span {{ ($widget['position']['width'] ?? 6) }}; grid-row:span {{ ($widget['position']['height'] ?? 3) }};"
                    data-widget-id="{{ $widget['id'] }}"
                >
                    {{-- Widget Header --}}
                    <div class="flex items-center justify-between px-4 py-3 border-b border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 rounded-t-xl">
                        <div class="flex items-center gap-2">
                            <span class="text-xs uppercase tracking-wider font-semibold text-stone-500">
                                @switch($widget['widget_type'])
                                    @case('chart')
                                        <x-filament::icon icon="heroicon-o-chart-bar" class="w-4 h-4 inline" />
                                        @break
                                    @case('stats')
                                        <x-filament::icon icon="heroicon-o-presentation-chart-line" class="w-4 h-4 inline" />
                                        @break
                                    @case('table')
                                        <x-filament::icon icon="heroicon-o-table-cells" class="w-4 h-4 inline" />
                                        @break
                                    @case('kpi')
                                        <x-filament::icon icon="heroicon-o-arrow-trending-up" class="w-4 h-4 inline" />
                                        @break
                                    @case('metric')
                                        <x-filament::icon icon="heroicon-o-calculator" class="w-4 h-4 inline" />
                                        @break
                                @endswitch
                            </span>
                            <span class="font-semibold text-stone-800 dark:text-stone-200">{{ $widget['title'] }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            @if($widget['is_pinned'])
                                <x-filament::icon icon="heroicon-s-star" class="w-4 h-4 text-amber-500" />
                            @endif
                            <x-filament::icon-button
                                icon="heroicon-o-x-mark"
                                color="danger"
                                size="xs"
                                wire:click="removeWidget({{ $widget['id'] }})"
                                wire:confirm="Hapus widget ini?"
                            />
                        </div>
                    </div>

                    {{-- Widget Content --}}
                    <div class="p-4">
                        @php $wd = $this->widgetData[$widget['id']] ?? null; @endphp

                        @if(!$wd)
                            <div class="flex items-center justify-center h-32 text-stone-400">
                                <p>Konfigurasi widget untuk memuat data.</p>
                            </div>
                        @elseif(($wd['type'] ?? '') === 'error')
                            <div class="flex items-center justify-center h-32">
                                <p class="text-red-500 text-sm">{{ $wd['message'] }}</p>
                            </div>
                        @elseif(($wd['type'] ?? '') === 'stats')
                            <div class="grid grid-cols-2 gap-3">
                                @foreach(($wd['stats'] ?? []) as $label => $value)
                                    <div class="bg-stone-50 dark:bg-stone-800 rounded-lg p-3">
                                        <p class="text-xs text-stone-500 uppercase tracking-wide">{{ ucwords(str_replace('_', ' ', $label)) }}</p>
                                        <p class="text-xl font-bold text-stone-900 dark:text-white">
                                            @if(is_numeric($value))
                                                {{ number_format($value, str_contains((string)$label, 'rate') || str_contains((string)$label, 'margin') ? 1 : 0) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @elseif(($wd['type'] ?? '') === 'chart')
                            <div class="chart-container" style="height: {{ ($widget['position']['height'] ?? 3) * 100 - 80 }}px;">
                                <canvas id="widget-chart-{{ $widget['id'] }}"></canvas>
                            </div>
                            @push('scripts')
                                <script>
                                    document.addEventListener('DOMContentLoaded', function() {
                                        const ctx = document.getElementById('widget-chart-{{ $widget['id'] }}');
                                        if (!ctx) return;
                                        const chartData = @json($wd['chartData'] ?? null);
                                        if (!chartData) return;
                                        new Chart(ctx, {
                                            type: chartData.type || 'bar',
                                            data: {
                                                labels: chartData.labels || [],
                                                datasets: chartData.datasets || [],
                                            },
                                            options: {
                                                responsive: true,
                                                maintainAspectRatio: false,
                                                plugins: {
                                                    legend: { display: true, position: 'bottom' },
                                                },
                                                scales: {
                                                    y: { beginAtZero: true },
                                                },
                                            },
                                        });
                                    });
                                </script>
                            @endpush
                        @elseif(($wd['type'] ?? '') === 'kpi')
                            @php $pct = min($wd['percentage'] ?? 0, 100); @endphp
                            <div class="text-center py-4">
                                <div class="relative w-32 h-32 mx-auto mb-3">
                                    <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 120 120">
                                        <circle cx="60" cy="60" r="54" fill="none" stroke="#e5e7eb" stroke-width="10"/>
                                        <circle cx="60" cy="60" r="54" fill="none"
                                            stroke="{{ $pct >= 80 ? '#059669' : ($pct >= 50 ? '#d97706' : '#dc2626') }}"
                                            stroke-width="10"
                                            stroke-dasharray="{{ 2 * M_PI * 54 }}"
                                            stroke-dashoffset="{{ 2 * M_PI * 54 * (1 - $pct / 100) }}"
                                            stroke-linecap="round"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-2xl font-bold text-stone-900 dark:text-white">{{ $pct }}%</span>
                                    </div>
                                </div>
                                <p class="text-sm text-stone-500">{{ number_format($wd['value'] ?? 0) }} / {{ number_format($wd['target'] ?? 100) }}</p>
                            </div>
                        @elseif(($wd['type'] ?? '') === 'metric')
                            <div class="flex items-center justify-center py-6">
                                <div class="text-center">
                                    <p class="text-sm text-stone-500 mb-1">{{ $wd['label'] }}</p>
                                    <p class="text-4xl font-extrabold" style="color: {{ $wd['color'] ?? '#4f46e5' }}">
                                        {{ $wd['prefix'] ?? '' }}{{ is_numeric($wd['value']) ? number_format($wd['value']) : $wd['value'] }}{{ $wd['suffix'] ?? '' }}
                                    </p>
                                </div>
                            </div>
                        @else
                            {{-- Table type --}}
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-xs">
                                    <thead>
                                        <tr class="bg-stone-100 dark:bg-stone-800">
                                            @foreach(($wd['headers'] ?? []) as $header)
                                                <th class="px-3 py-2 text-left font-semibold text-stone-700 dark:text-stone-300">
                                                    {{ ucwords(str_replace('_', ' ', $header)) }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(array_slice(($wd['rows'] ?? []), 0, 10) as $row)
                                            <tr class="border-t border-stone-200 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800/50">
                                                @foreach((array) $row as $value)
                                                    <td class="px-3 py-1.5 text-stone-600 dark:text-stone-400 whitespace-nowrap">
                                                        @if(is_numeric($value))
                                                            {{ number_format($value, 2) }}
                                                        @else
                                                            {{ $value }}
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if(empty($this->widgets))
            <div class="text-center py-16">
                <x-filament::icon icon="heroicon-o-squares-plus" class="w-16 h-16 mx-auto text-stone-300 dark:text-stone-600" />
                <h3 class="mt-4 text-lg font-semibold text-stone-500 dark:text-stone-400">Belum ada widget</h3>
                <p class="text-sm text-stone-400 mt-1">Klik "Tambah Widget" untuk memulai.</p>
            </div>
        @endif

        {{-- Add Widget Modal --}}
        <x-filament::modal id="add-widget-modal" width="2xl">
            <x-slot name="heading">Tambah Widget</x-slot>

            <form wire:submit="addWidget" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    {{ $this->form }}
                </div>

                <x-filament::button type="submit" color="primary" class="w-full">
                    Tambahkan
                </x-filament::button>
            </form>

            <x-slot name="footer">
                <x-filament::button color="gray" x-on:click="$dispatch('close-modal', { id: 'add-widget-modal' })">
                    Batal
                </x-filament::button>
            </x-slot>
        </x-filament::modal>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <script>
            function dashboardBuilder() {
                return {
                    init() {
                        this.initDragDrop();
                    },
                    initDragDrop() {
                        const grid = this.$el.querySelector('.widget-grid');
                        if (!grid) return;

                        let draggedEl = null;
                        let placeholder = null;

                        grid.addEventListener('dragstart', (e) => {
                            const card = e.target.closest('.widget-card');
                            if (!card) return;
                            draggedEl = card;
                            card.classList.add('opacity-50');
                            e.dataTransfer.effectAllowed = 'move';
                        });

                        grid.addEventListener('dragend', (e) => {
                            if (draggedEl) {
                                draggedEl.classList.remove('opacity-50');
                                draggedEl = null;
                            }
                        });

                        grid.addEventListener('dragover', (e) => {
                            e.preventDefault();
                            e.dataTransfer.dropEffect = 'move';
                        });

                        grid.addEventListener('drop', (e) => {
                            e.preventDefault();
                            const card = e.target.closest('.widget-card');
                            if (!card || !draggedEl || card === draggedEl) return;

                            grid.insertBefore(draggedEl, card);

                            const positions = [];
                            grid.querySelectorAll('.widget-card').forEach((el, idx) => {
                                positions.push({
                                    widget_id: parseInt(el.dataset.widgetId),
                                    sort_order: idx,
                                    x: 0,
                                    y: idx * 3,
                                    width: parseInt(el.style.gridColumn.replace('span ', '')) || 6,
                                    height: parseInt(el.style.gridRow.replace('span ', '')) || 3,
                                });
                            });

                            @this.call('saveLayoutInternal', positions);
                        });
                    }
                }
            }
        </script>
    @endpush

    <style>
        .widget-card {
            background: white;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
            transition: box-shadow .2s, transform .2s;
            cursor: grab;
            min-height: 200px;
        }
        .widget-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
            transform: translateY(-1px);
        }
        .widget-card:active {
            cursor: grabbing;
        }
        .widget-card.opacity-50 {
            opacity: 0.5;
        }
        .dark .widget-card {
            background: #1e293b;
            border-color: #334155;
        }
        @media (max-width: 1024px) {
            .widget-grid {
                grid-template-columns: repeat(6, 1fr) !important;
            }
        }
        @media (max-width: 640px) {
            .widget-grid {
                grid-template-columns: repeat(1, 1fr) !important;
            }
            .widget-card {
                grid-column: span 1 !important;
                min-height: 150px;
            }
        }
    </style>
</x-filament-panels::page>
