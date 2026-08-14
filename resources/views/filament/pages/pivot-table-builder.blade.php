<x-filament-panels::page>
    <div class="pivot-builder" x-data="pivotBuilder()">
        {{-- Top Toolbar --}}
        <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold text-stone-900 dark:text-white">Pivot Table Builder</h1>
                <p class="text-sm text-stone-500 dark:text-stone-400 mt-1">Bangun laporan analitik ad-hoc dengan drag & drop dimensi dan ukuran.</p>
            </div>
            <div class="flex items-center gap-3">
                <x-filament::button
                    color="gray"
                    icon="heroicon-o-arrow-down-tray"
                    wire:click="exportCsv"
                >
                    Export CSV
                </x-filament::button>
                <x-filament::button
                    color="primary"
                    icon="heroicon-o-play"
                    wire:click="preview"
                >
                    Pratinjau
                </x-filament::button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- ============ LEFT: Configuration ============ --}}
            <div class="lg:col-span-1 space-y-4">
                {{-- Saved Reports --}}
                <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
                    <h3 class="font-semibold text-stone-800 dark:text-stone-200 mb-3">Laporan Tersimpan</h3>
                    <select
                        wire:change="loadReport($event.target.value)"
                        class="w-full px-3 py-2 border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 rounded-lg text-sm text-stone-800 dark:text-stone-200 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400"
                    >
                        <option value="">-- Muat laporan --</option>
                        @foreach ($savedReports as $saved)
                            <option value="{{ $saved['id'] }}">{{ $saved['name'] }}</option>
                        @endforeach
                    </select>
                    @if (empty($savedReports))
                        <p class="text-xs text-stone-400 mt-2">Belum ada laporan pivot tersimpan.</p>
                    @endif
                </div>

                {{-- Data Source --}}
                <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
                    <h3 class="font-semibold text-stone-800 dark:text-stone-200 mb-3">Sumber Data</h3>
                    <select wire:model.live="source"
                        class="w-full px-3 py-2 border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 rounded-lg text-sm text-stone-800 dark:text-stone-200 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                        <option value="">-- Pilih sumber data --</option>
                        @foreach ($dataSources as $ds)
                            <option value="{{ $ds['key'] }}">{{ $ds['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($source)
                    {{-- Dimensions --}}
                    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-stone-800 dark:text-stone-200">Dimensi (Baris)</h3>
                            <span class="text-xs text-stone-400">seret untuk mengurutkan</span>
                        </div>

                        <div class="flex flex-wrap gap-1.5 mt-1 mb-3 min-h-8" data-sortable="dimensions">
                            @foreach ($dimensions as $idx => $dim)
                                @php $dimLabel = collect($fields['dimensions'])->firstWhere('name', $dim)['label'] ?? $dim; @endphp
                                <span
                                    data-sortable-item
                                    data-drop-index="{{ $idx }}"
                                    data-value="{{ $dim }}"
                                    draggable="true"
                                    x-on:dragstart="dragStart($event, 'dimensions', '{{ $dim }}')"
                                    x-on:dragover="dragOver($event)"
                                    x-on:drop="drop($event, 'dimensions', {{ $idx }})"
                                    x-on:dragend="dragEnd($event)"
                                    class="inline-flex items-center gap-1.5 text-xs bg-indigo-50 dark:bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 px-2.5 py-1 rounded-full cursor-grab select-none transition hover:shadow-sm">
                                    <x-filament::icon icon="heroicon-o-bars-3" class="w-3 h-3 opacity-50" />
                                    {{ $dimLabel }}
                                    <button type="button" wire:click="removeDimension('{{ $dim }}')" class="text-indigo-400 hover:text-red-500">&times;</button>
                                </span>
                            @endforeach
                        </div>

                        <select wire:change="addDimension($event.target.value)"
                            class="w-full px-3 py-2 border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 rounded-lg text-sm text-stone-800 dark:text-stone-200 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                            <option value="">+ Tambah Dimensi</option>
                            @foreach ($fields['dimensions'] as $field)
                                @if (!in_array($field['name'], $dimensions))
                                    <option value="{{ $field['name'] }}">{{ $field['label'] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Measures --}}
                    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-stone-800 dark:text-stone-200">Ukuran (Nilai)</h3>
                            <button type="button" wire:click="addMeasure" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">+ Tambah Ukuran</button>
                        </div>

                        <div class="space-y-2" data-sortable="measures">
                            @foreach ($measures as $idx => $measure)
                                <div
                                    data-sortable-item
                                    data-drop-index="{{ $idx }}"
                                    data-value="{{ $idx }}"
                                    class="flex gap-2 items-center bg-stone-50 dark:bg-stone-800/60 rounded-lg p-2"
                                >
                                    <button type="button"
                                        draggable="true"
                                        x-on:dragstart="dragStart($event, 'measures', '{{ $idx }}')"
                                        x-on:dragover="dragOver($event)"
                                        x-on:drop="drop($event, 'measures', {{ $idx }})"
                                        x-on:dragend="dragEnd($event)"
                                        class="cursor-grab text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 p-1">
                                        <x-filament::icon icon="heroicon-o-bars-3" class="w-4 h-4" />
                                    </button>

                                    <select wire:model.live="measures.{{ $idx }}.field"
                                        class="flex-1 min-w-0 px-2 py-1.5 border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 rounded-lg text-xs text-stone-800 dark:text-stone-200">
                                        <option value="">Field</option>
                                        @foreach ($fields['measures'] as $field)
                                            <option value="{{ $field['name'] }}">{{ $field['label'] }}</option>
                                        @endforeach
                                    </select>

                                    <select wire:model.live="measures.{{ $idx }}.aggregate"
                                        class="w-28 px-2 py-1.5 border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 rounded-lg text-xs text-stone-800 dark:text-stone-200">
                                        @foreach ($aggregateOptions as $key => $label)
                                            <option value="{{ $key }}">{{ strtoupper($key) }}</option>
                                        @endforeach
                                    </select>

                                    <button type="button" wire:click="removeMeasure({{ $idx }})" class="text-red-400 hover:text-red-600 p-1">&times;</button>
                                </div>
                            @endforeach
                        </div>

                        @if (empty($measures))
                            <p class="text-xs text-stone-400 mt-2">Belum ada ukuran. Tambahkan untuk menghitung SUM/AVG/COUNT.</p>
                        @endif
                    </div>

                    {{-- Filters --}}
                    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-semibold text-stone-800 dark:text-stone-200">Filter</h3>
                            <button type="button" wire:click="addFilter" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">+ Tambah Filter</button>
                        </div>

                        <div class="space-y-2">
                            @foreach ($filters as $idx => $filter)
                                <div class="border border-stone-200 dark:border-stone-700 rounded-lg p-2 space-y-1.5">
                                    <div class="flex gap-2 items-center">
                                        <select wire:model.live="filters.{{ $idx }}.column"
                                            class="flex-1 min-w-0 px-2 py-1.5 border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 rounded-lg text-xs text-stone-800 dark:text-stone-200">
                                            <option value="">Kolom</option>
                                            @foreach ($fields['dimensions'] as $field)
                                                <option value="{{ $field['name'] }}">{{ $field['label'] }}</option>
                                            @endforeach
                                            @foreach ($fields['measures'] as $field)
                                                @if ($field['name'] !== '*')
                                                    <option value="{{ $field['name'] }}">{{ $field['label'] }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <select wire:model.live="filters.{{ $idx }}.operator"
                                            class="w-24 px-2 py-1.5 border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 rounded-lg text-xs text-stone-800 dark:text-stone-200">
                                            @foreach ($filterOperators as $key => $label)
                                                <option value="{{ $key }}">{{ $key }}</option>
                                            @endforeach
                                        </select>
                                        <button type="button" wire:click="removeFilter({{ $idx }})" class="text-red-400 hover:text-red-600 p-1">&times;</button>
                                    </div>

                                    @if (($filters[$idx]['operator'] ?? '=') === 'between')
                                        <div class="flex gap-2 items-center">
                                            <input type="text" wire:model.live="filters.{{ $idx }}.value"
                                                placeholder="Dari (YYYY-MM-DD)"
                                                class="flex-1 px-2 py-1.5 border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 rounded-lg text-xs text-stone-800 dark:text-stone-200">
                                            <span class="text-xs text-stone-400">s/d</span>
                                            <input type="text" wire:model.live="filters.{{ $idx }}.value_end"
                                                placeholder="Sampai"
                                                class="flex-1 px-2 py-1.5 border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 rounded-lg text-xs text-stone-800 dark:text-stone-200">
                                        </div>
                                    @else
                                        <input type="text" wire:model.live="filters.{{ $idx }}.value"
                                            placeholder="Nilai"
                                            class="w-full px-2 py-1.5 border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 rounded-lg text-xs text-stone-800 dark:text-stone-200">
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if (empty($filters))
                            <p class="text-xs text-stone-400 mt-2">Belum ada filter. Gunakan filter untuk membatasi rentang tanggal, nilai, atau nilai tertentu.</p>
                        @endif
                    </div>

                    {{-- Save --}}
                    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
                        <h3 class="font-semibold text-stone-800 dark:text-stone-200 mb-3">Simpan Sebagai Laporan</h3>
                        <input type="text" wire:model="reportName" placeholder="Nama laporan"
                            class="w-full px-3 py-2 border border-stone-300 dark:border-stone-600 bg-white dark:bg-stone-800 rounded-lg text-sm text-stone-800 dark:text-stone-200 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                        @error('reportName')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                        <x-filament::button
                            color="success"
                            icon="heroicon-o-bookmark"
                            wire:click="saveReport"
                            class="w-full mt-3"
                        >
                            Simpan Laporan
                        </x-filament::button>
                    </div>
                @else
                    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-8 text-center">
                        <x-filament::icon icon="heroicon-o-table-cells" class="w-12 h-12 mx-auto text-stone-300 dark:text-stone-600" />
                        <p class="text-sm text-stone-400 mt-3">Pilih sumber data untuk mulai membangun pivot table.</p>
                    </div>
                @endif
            </div>

            {{-- ============ RIGHT: Preview ============ --}}
            <div class="lg:col-span-2 bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5 overflow-hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-stone-800 dark:text-stone-200">Pratinjau Pivot Table</h3>
                    @if (!empty($result) && empty($result['error']))
                        <span class="text-xs text-stone-400">{{ $result['row_count'] }} baris</span>
                    @endif
                </div>

                @if (empty($result))
                    <div class="py-16 text-center text-stone-400 dark:text-stone-500">
                        <p class="text-lg">Belum ada hasil.</p>
                        <p class="text-sm mt-1">Konfigurasi sumber, dimensi, dan ukuran, lalu klik "Pratinjau".</p>
                    </div>
                @elseif (isset($result['error']))
                    <div class="bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 p-4 rounded-lg text-sm">{{ $result['error'] }}</div>
                @elseif (empty($result['rows']))
                    <div class="py-12 text-center text-stone-400 dark:text-stone-500">
                        <p>Tidak ada data yang cocok dengan konfigurasi saat ini.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="bg-stone-50 dark:bg-stone-800">
                                    @foreach ($result['headers'] as $header)
                                        <th class="px-3 py-2.5 text-left font-semibold text-stone-700 dark:text-stone-300 border border-stone-200 dark:border-stone-700 whitespace-nowrap {{ $header['type'] === 'measure' ? 'text-right' : '' }}">
                                            {{ $header['label'] }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($result['rows'] as $row)
                                    <tr class="hover:bg-indigo-50/30 dark:hover:bg-stone-800/50">
                                        @foreach ($result['headers'] as $header)
                                            @php $cell = $row[$header['key']] ?? null; @endphp
                                            <td class="px-3 py-2 border border-stone-200 dark:border-stone-700 {{ $header['type'] === 'measure' ? 'text-right font-medium text-stone-700 dark:text-stone-300' : 'text-stone-600 dark:text-stone-400' }}">
                                                @if ($header['type'] === 'measure')
                                                    {{ number_format((float) $cell, 2, ',', '.') }}
                                                @elseif ($header['data_type'] === 'date' && $cell)
                                                    {{ \Illuminate\Support\Carbon::parse($cell)->format('d M Y') }}
                                                @else
                                                    {{ $cell !== null && $cell !== '' ? $cell : '—' }}
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                @if (!empty($result['totals']))
                                    <tr class="bg-stone-50 dark:bg-stone-800 font-semibold">
                                        @foreach ($result['headers'] as $header)
                                            @if ($header['type'] === 'dimension')
                                                <td class="px-3 py-2 text-stone-700 dark:text-stone-300 border border-stone-200 dark:border-stone-700">Total</td>
                                            @else
                                                <td class="px-3 py-2 text-right text-indigo-700 dark:text-indigo-400 border border-stone-200 dark:border-stone-700">
                                                    {{ number_format((float) ($result['totals'][$header['key']] ?? 0), 2, ',', '.') }}
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function pivotBuilder() {
                return {
                    dragged: null,

                    dragStart(e, type, value) {
                        this.dragged = { type, value };
                        e.dataTransfer.effectAllowed = 'move';
                        try { e.dataTransfer.setData('text/plain', value); } catch (err) {}
                        const el = e.target.closest('[data-sortable-item]');
                        if (el) el.classList.add('opacity-40');
                    },

                    dragOver(e) {
                        e.preventDefault();
                        e.dataTransfer.dropEffect = 'move';
                    },

                    drop(e, type, targetIndex) {
                        e.preventDefault();
                        if (!this.dragged || this.dragged.type !== type) return;

                        const container = e.target.closest('[data-sortable]');
                        if (!container) return;

                        const sourceEl = container.querySelector('[data-value="' + this.dragged.value + '"]');
                        const targetEl = e.target.closest('[data-sortable-item]');
                        if (!sourceEl || !targetEl || sourceEl === targetEl) return;

                        container.insertBefore(sourceEl, targetEl);

                        const order = Array.from(container.querySelectorAll('[data-sortable-item]'))
                            .map(el => el.dataset.value);

                        if (type === 'dimensions') {
                            @this.call('reorderDimensions', order);
                        } else if (type === 'measures') {
                            @this.call('reorderMeasures', order.map(Number));
                        }
                    },

                    dragEnd(e) {
                        const el = e.target.closest('[data-sortable-item]');
                        if (el) el.classList.remove('opacity-40');
                        this.dragged = null;
                    },
                }
            }
        </script>
    @endpush

    <style>
        .pivot-builder [data-sortable-item].opacity-40 {
            opacity: 0.4;
        }
        .pivot-builder [data-sortable-item] {
            transition: opacity .15s;
        }
        @media (max-width: 1023px) {
            .pivot-builder table { font-size: 12px; }
            .pivot-builder th, .pivot-builder td { padding: 8px 10px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .pivot-builder [data-sortable-item] { transition: none; }
        }
    </style>
</x-filament-panels::page>
