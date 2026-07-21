<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Report Configuration Panel --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-1 bg-white rounded-xl border border-stone-200 p-5">
                <h3 class="font-semibold text-stone-800 mb-4">Konfigurasi Laporan</h3>

                <div class="space-y-4">
                    {{-- Report Name --}}
                    <div>
                        <label class="text-sm font-medium text-stone-700">Nama Laporan</label>
                        <input type="text" wire:model.lazy="reportConfig.name"
                            class="w-full mt-1 px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                    </div>

                    {{-- Report Type --}}
                    <div>
                        <label class="text-sm font-medium text-stone-700">Tipe Laporan</label>
                        <select wire:model.live="reportConfig.type"
                            class="w-full mt-1 px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                            @foreach ($reportTypes as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Table Selector --}}
                    <div>
                        <label class="text-sm font-medium text-stone-700">Tabel Sumber</label>
                        <select wire:model.live="selectedTable"
                            class="w-full mt-1 px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                            <option value="">-- Pilih Tabel --</option>
                            @foreach ($availableTables as $t)
                                <option value="{{ $t['table'] }}">{{ $t['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Available Columns --}}
                    @if ($selectedTable)
                        <div>
                            <label class="text-sm font-medium text-stone-700">Kolom Tersedia</label>
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach ($tableColumns as $col)
                                    <span class="text-xs bg-stone-100 text-stone-600 px-2 py-0.5 rounded">{{ $col }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Row Fields --}}
                    <div>
                        <label class="text-sm font-medium text-stone-700">Field Baris</label>
                        <div class="flex flex-wrap gap-1 mt-1 mb-2">
                            @foreach ($reportConfig['rows'] ?? [] as $rowField)
                                <span class="inline-flex items-center gap-1 text-xs bg-indigo-50 text-indigo-700 px-2 py-1 rounded-full">
                                    {{ $rowField }}
                                    <button wire:click="removeRowField('{{ $rowField }}')" class="text-indigo-400 hover:text-red-500">&times;</button>
                                </span>
                            @endforeach
                        </div>
                        <select wire:change="addRowField($event.target.value)"
                            class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                            <option value="">+ Tambah Field Baris</option>
                            @foreach ($tableColumns as $col)
                                <option value="{{ $col }}">{{ $col }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Column Fields --}}
                    <div>
                        <label class="text-sm font-medium text-stone-700">Field Kolom</label>
                        <div class="flex flex-wrap gap-1 mt-1 mb-2">
                            @foreach ($reportConfig['columns'] ?? [] as $colField)
                                <span class="inline-flex items-center gap-1 text-xs bg-violet-50 text-violet-700 px-2 py-1 rounded-full">
                                    {{ $colField }}
                                    <button wire:click="removeColumnField('{{ $colField }}')" class="text-violet-400 hover:text-red-500">&times;</button>
                                </span>
                            @endforeach
                        </div>
                        <select wire:change="addColumnField($event.target.value)"
                            class="w-full px-3 py-2 border border-stone-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400">
                            <option value="">+ Tambah Field Kolom</option>
                            @foreach ($tableColumns as $col)
                                <option value="{{ $col }}">{{ $col }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Value Fields --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-stone-700">Field Nilai</label>
                            <button wire:click="addValueField" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Tambah</button>
                        </div>
                        @foreach ($reportConfig['values'] ?? [] as $idx => $value)
                            <div class="flex gap-2 mb-2 items-center">
                                <select wire:model.live="reportConfig.values.{{ $idx }}.field"
                                    class="flex-1 px-2 py-1.5 border border-stone-300 rounded-lg text-xs">
                                    <option value="">Field</option>
                                    @foreach ($tableColumns as $col)
                                        <option value="{{ $col }}">{{ $col }}</option>
                                    @endforeach
                                </select>
                                <select wire:model.live="reportConfig.values.{{ $idx }}.aggregate"
                                    class="w-28 px-2 py-1.5 border border-stone-300 rounded-lg text-xs">
                                    @foreach ($aggregateOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button wire:click="removeValueField({{ $idx }})" class="text-red-400 hover:text-red-600">&times;</button>
                            </div>
                        @endforeach
                    </div>

                    {{-- Filters --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-stone-700">Filter</label>
                            <button wire:click="addFilter" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">+ Tambah Filter</button>
                        </div>
                        @foreach ($reportConfig['filters'] ?? [] as $idx => $filter)
                            <div class="flex gap-2 mb-2 items-center">
                                <select wire:model.live="reportConfig.filters.{{ $idx }}.column"
                                    class="flex-1 px-2 py-1.5 border border-stone-300 rounded-lg text-xs">
                                    <option value="">Kolom</option>
                                    @foreach ($tableColumns as $col)
                                        <option value="{{ $col }}">{{ $col }}</option>
                                    @endforeach
                                </select>
                                <select wire:model.live="reportConfig.filters.{{ $idx }}.operator"
                                    class="w-20 px-2 py-1.5 border border-stone-300 rounded-lg text-xs">
                                    <option value="=">=</option>
                                    <option value=">">></option>
                                    <option value="<"><</option>
                                    <option value=">=">>=</option>
                                    <option value="<="><=</option>
                                    <option value="!=">!=</option>
                                    <option value="contains">contains</option>
                                </select>
                                <input type="text" wire:model.live="reportConfig.filters.{{ $idx }}.value"
                                    class="w-32 px-2 py-1.5 border border-stone-300 rounded-lg text-xs" placeholder="Nilai">
                                <button wire:click="removeFilter({{ $idx }})" class="text-red-400 hover:text-red-600">&times;</button>
                            </div>
                        @endforeach
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col gap-2 pt-4 border-t border-stone-100">
                        <button wire:click="runReport"
                            class="w-full px-4 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-semibold hover:bg-indigo-700 transition shadow-sm">
                            Jalankan Laporan
                        </button>
                        <button wire:click="saveReportAction"
                            class="w-full px-4 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition shadow-sm">
                            Simpan Laporan
                        </button>
                        <button wire:click="exportExcel"
                            class="w-full px-4 py-2.5 bg-stone-100 text-stone-700 rounded-lg text-sm font-semibold hover:bg-stone-200 transition">
                            Export Excel
                        </button>
                        <button wire:click="exportPdf"
                            class="w-full px-4 py-2.5 bg-stone-100 text-stone-700 rounded-lg text-sm font-semibold hover:bg-stone-200 transition">
                            Export PDF
                        </button>
                    </div>
                </div>
            </div>

            {{-- Result Panel --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-stone-200 p-5 overflow-auto">
                <h3 class="font-semibold text-stone-800 mb-4">Hasil Laporan</h3>

                @if (!empty($result))
                    @if (isset($result['error']))
                        <div class="bg-red-50 text-red-700 p-4 rounded-lg">{{ $result['error'] }}</div>
                    @elseif (isset($result['matrix']))
                        {{-- Crosstab / Pivot Table --}}
                        <div class="overflow-auto">
                            <table class="w-full text-sm border-collapse">
                                <thead>
                                    <tr class="bg-stone-50">
                                        <th class="px-3 py-2 text-left font-semibold text-stone-700 border border-stone-200">
                                            {{ $result['row_field'] ?? 'Baris' }}</th>
                                        @foreach ($result['col_labels'] ?? [] as $colLabel)
                                            <th class="px-3 py-2 text-right font-semibold text-stone-700 border border-stone-200">{{ $colLabel }}</th>
                                        @endforeach
                                        <th class="px-3 py-2 text-right font-semibold text-stone-700 bg-stone-100 border border-stone-200">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($result['row_labels'] ?? [] as $rowLabel)
                                        <tr class="hover:bg-indigo-50/30">
                                            <td class="px-3 py-2 font-medium text-stone-700 border border-stone-200">{{ $rowLabel }}</td>
                                            @foreach ($result['col_labels'] ?? [] as $colLabel)
                                                <td class="px-3 py-2 text-right border border-stone-200">
                                                    {{ number_format($result['matrix'][$rowLabel][$colLabel] ?? 0, 2) }}</td>
                                            @endforeach
                                            <td class="px-3 py-2 text-right font-semibold bg-stone-50 border border-stone-200">
                                                {{ number_format($result['row_totals'][$rowLabel] ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    @if (!empty($result['col_totals']))
                                        <tr class="bg-stone-50 font-semibold">
                                            <td class="px-3 py-2 text-stone-700 border border-stone-200">Total</td>
                                            @foreach ($result['col_labels'] ?? [] as $colLabel)
                                                <td class="px-3 py-2 text-right border border-stone-200">
                                                    {{ number_format($result['col_totals'][$colLabel] ?? 0, 2) }}</td>
                                            @endforeach
                                            <td class="px-3 py-2 text-right bg-indigo-50 text-indigo-700 border border-stone-200">
                                                {{ number_format($result['grand_total'] ?? 0, 2) }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @elseif (!empty($result['raw']))
                        {{-- Raw Table --}}
                        @php $raw = collect($result['raw']); @endphp
                        @if ($raw->isNotEmpty())
                            <div class="overflow-auto max-h-96">
                                <table class="w-full text-sm border-collapse">
                                    <thead>
                                        <tr class="bg-stone-50">
                                            @foreach (array_keys((array) $raw->first()) as $header)
                                                <th class="px-3 py-2 text-left font-semibold text-stone-700 border border-stone-200 sticky top-0 bg-stone-50">
                                                    {{ ucwords(str_replace('_', ' ', $header)) }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($raw as $row)
                                            <tr class="hover:bg-indigo-50/30">
                                                @foreach ((array) $row as $cell)
                                                    <td class="px-3 py-1.5 border border-stone-200">{{ $cell }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="py-8 text-center text-stone-400">Tidak ada data.</div>
                        @endif
                    @endif
                @else
                    <div class="py-16 text-center text-stone-400">
                        <p class="text-lg">Pilih tabel sumber dan konfigurasikan laporan.</p>
                        <p class="text-sm mt-1">Klik "Jalankan Laporan" untuk melihat hasil.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
