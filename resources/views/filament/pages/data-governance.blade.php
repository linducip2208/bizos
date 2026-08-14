<x-filament-panels::page>
    <div class="space-y-6">

        {{-- Data Quality Scorecards --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Skor Kualitas Data</h2>
                <x-filament::button
                    wire:click="refreshAll"
                    color="gray"
                    size="sm"
                    icon="heroicon-o-arrow-path"
                >
                    Refresh
                </x-filament::button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach ($qualityScores as $key => $score)
                    @php
                        $qualityColor = match(true) {
                            $score['quality_score'] >= 80 => 'emerald',
                            $score['quality_score'] >= 60 => 'amber',
                            default => 'red',
                        };
                        $icons = [
                            'client' => 'heroicon-o-building-office-2',
                            'supplier' => 'heroicon-o-truck',
                            'product' => 'heroicon-o-cube',
                            'employee' => 'heroicon-o-user-group',
                            'lead' => 'heroicon-o-user-plus',
                        ];
                        $icon = $icons[$key] ?? 'heroicon-o-document-text';
                        $colors = [
                            'client' => 'blue',
                            'supplier' => 'violet',
                            'product' => 'teal',
                            'employee' => 'orange',
                            'lead' => 'pink',
                        ];
                        $cardColor = $colors[$key] ?? 'gray';
                    @endphp
                    <div class="relative flex flex-col p-5 rounded-xl border bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex items-center gap-2.5 mb-3">
                            <div class="w-9 h-9 rounded-lg bg-{{ $cardColor }}-100 dark:bg-{{ $cardColor }}-900/30 flex items-center justify-center">
                                <x-dynamic-component :component="$icon" class="w-5 h-5 text-{{ $cardColor }}-600 dark:text-{{ $cardColor }}-400" />
                            </div>
                            <span class="font-semibold text-sm text-gray-800 dark:text-gray-200">{{ $score['label'] }}</span>
                        </div>

                        <div class="mb-3 text-center">
                            <span class="text-3xl font-extrabold text-{{ $qualityColor }}-600 dark:text-{{ $qualityColor }}-400">
                                {{ $score['quality_score'] }}
                            </span>
                            <span class="text-xs text-gray-400">/100</span>
                        </div>

                        @php
                            $barColor = match(true) {
                                $score['quality_score'] >= 80 => 'bg-emerald-500',
                                $score['quality_score'] >= 60 => 'bg-amber-500',
                                default => 'bg-red-500',
                            };
                        @endphp
                        <div class="w-full h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden mb-3">
                            <div class="h-full {{ $barColor }} rounded-full transition-all duration-500"
                                 style="width: {{ max($score['quality_score'], 2) }}%"></div>
                        </div>

                        <div class="space-y-1 text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex justify-between">
                                <span>Kelengkapan</span>
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $score['completeness_percent'] }}%</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Duplikat</span>
                                <span class="font-medium {{ $score['duplicate_percent'] > 10 ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $score['duplicate_percent'] }}%
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span>Total Data</span>
                                <span class="font-mono font-medium text-gray-700 dark:text-gray-300">{{ number_format($score['total_records']) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Duplicate Detection --}}
        <div class="rounded-xl border bg-white dark:bg-gray-900 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <h2 class="font-semibold text-gray-800 dark:text-gray-200">Deteksi Data Duplikat</h2>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                @php
                    $dupIcons = [
                        'client' => 'heroicon-o-building-office-2',
                        'supplier' => 'heroicon-o-truck',
                        'product' => 'heroicon-o-cube',
                        'employee' => 'heroicon-o-user-group',
                        'lead' => 'heroicon-o-user-plus',
                    ];
                    $dupColors = [
                        'client' => 'blue',
                        'supplier' => 'violet',
                        'product' => 'teal',
                        'employee' => 'orange',
                        'lead' => 'pink',
                    ];
                @endphp
                @foreach ($duplicateReport as $key => $report)
                    @php
                        $hasDuplicates = ($report['duplicate_groups'] ?? 0) > 0;
                    @endphp
                    <div class="relative p-4 rounded-xl border bg-gray-50 dark:bg-gray-800/50">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-{{ $dupColors[$key] ?? 'gray' }}-100 dark:bg-{{ $dupColors[$key] ?? 'gray' }}-900/30 flex items-center justify-center">
                                <x-dynamic-component :component="($dupIcons[$key] ?? 'heroicon-o-document-text')" class="w-4 h-4 text-{{ $dupColors[$key] ?? 'gray' }}-600 dark:text-{{ $dupColors[$key] ?? 'gray' }}-400" />
                            </div>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $report['label'] }}</span>
                        </div>

                        <div class="space-y-1 text-xs mb-3">
                            <div class="flex justify-between text-gray-500">
                                <span>Grup Duplikat</span>
                                <span @class([
                                    'font-bold font-mono',
                                    'text-red-600' => $hasDuplicates,
                                    'text-emerald-600' => !$hasDuplicates,
                                ])>{{ $report['duplicate_groups'] }}</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Record Duplikat</span>
                                <span class="font-mono">{{ $report['duplicate_records'] }}</span>
                            </div>
                        </div>

                        <x-filament::button
                            wire:click="detectDuplicates('{{ $key }}')"
                            :color="$hasDuplicates ? 'warning' : 'gray'"
                            size="sm"
                            class="w-full"
                            icon="heroicon-o-magnifying-glass"
                        >
                            Deteksi Duplikat
                        </x-filament::button>
                    </div>
                @endforeach
            </div>

            {{-- Duplicate Content Area --}}
            @if (!empty($activeEntityType) && !empty($activeDuplicates))
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200">
                            Hasil Deteksi: {{ $duplicateReport[$activeEntityType]['label'] ?? $activeEntityType }}
                            <span class="text-sm font-normal text-gray-400 ml-2">
                                {{ count($activeDuplicates) }} grup duplikat ditemukan
                            </span>
                        </h3>
                    </div>

                    @foreach ($activeDuplicates as $groupIndex => $group)
                        @php
                            $primary = $group['primary_candidate'] ?? $group['records'][0];
                            $primaryName = $primary['name'] ?? $primary['first_name'] ?? 'Unknown';
                            $otherRecords = array_filter($group['records'] ?? [], fn($r) => ($r['id'] ?? '') !== ($primary['id'] ?? ''));
                            $maxScore = collect($group['match_scores'] ?? [])->max('score') ?? 0;
                        @endphp
                        <div class="mb-4 p-4 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-900/10">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                            {{ $primaryName }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400">
                                            {{ round($maxScore) }}% kecocokan
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            +{{ count($otherRecords) }} duplikat
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 space-y-0.5">
                                        @foreach ($group['records'] as $record)
                                            <div class="flex items-center gap-2">
                                                <span class="w-28 truncate font-mono">{{ $record['id'] ?? '-' }}</span>
                                                <span class="truncate">{{ $record['name'] ?? $record['first_name'] ?? '-' }}</span>
                                                @if (!empty($record['email']))
                                                    <span class="text-gray-400">{{ $record['email'] }}</span>
                                                @endif
                                                @if (!empty($record['phone']))
                                                    <span class="text-gray-400">{{ $record['phone'] }}</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <x-filament::button
                                        wire:click="previewMerge({{ $primary['id'] }}, {{ json_encode(collect($otherRecords)->pluck('id')->values()->toArray()) }}, '{{ $activeEntityType }}')"
                                        color="warning"
                                        size="sm"
                                        icon="heroicon-o-arrows-pointing-in"
                                    >
                                        Tinjau
                                    </x-filament::button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if (empty($activeDuplicates))
                        <div class="text-center py-8 text-gray-400">
                            <x-heroicon-o-check-circle class="w-12 h-12 mx-auto mb-2 text-emerald-400" />
                            <p class="text-sm">Tidak ditemukan data duplikat untuk entitas ini.</p>
                        </div>
                    @endif
                </div>
            @elseif (!empty($activeEntityType))
                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 text-center py-8 text-gray-400">
                    <x-heroicon-o-check-circle class="w-12 h-12 mx-auto mb-2 text-emerald-400" />
                    <p class="text-sm">Tidak ditemukan data duplikat untuk entitas ini.</p>
                </div>
            @endif
        </div>

        {{-- Merge Modal --}}
        @if ($showMergeModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
                 x-data
                 x-init="$el.focus()"
                 @keydown.escape.window="$wire.closeMergeModal()">
                <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl max-h-[80vh] overflow-y-auto m-4 border border-gray-200 dark:border-gray-700"
                     @click.outside="$wire.closeMergeModal()">
                    {{-- Modal Header --}}
                    <div class="sticky top-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 px-6 py-4 rounded-t-2xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tinjau Penggabungan Data</h3>
                                <p class="text-sm text-gray-500 mt-0.5">{{ $mergePreview['entity_label'] ?? $activeEntityType }}</p>
                            </div>
                            <button wire:click="closeMergeModal" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400">
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>
                    </div>

                    {{-- Modal Body --}}
                    <div class="px-6 py-4 space-y-4">
                        {{-- Target --}}
                        <div class="p-4 rounded-xl border-2 border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-900/20">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200 text-emerald-800 dark:bg-emerald-800 dark:text-emerald-200">TARGET</span>
                                <span class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">{{ $mergePreview['target_name'] ?? 'Unknown' }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                @foreach ($mergePreview['target'] ?? [] as $field => $value)
                                    <div class="truncate">
                                        <span class="text-gray-500">{{ $field }}:</span>
                                        <span class="ml-1 text-gray-800 dark:text-gray-300">{{ is_scalar($value) ? $value : json_encode($value) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Arrow --}}
                        <div class="flex justify-center">
                            <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                <x-heroicon-o-arrows-pointing-in class="w-5 h-5 text-gray-500" />
                            </div>
                        </div>

                        {{-- Sources --}}
                        @foreach ($mergePreview['sources'] ?? [] as $index => $source)
                            <div class="p-4 rounded-xl border-2 border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-200 text-red-800 dark:bg-red-800 dark:text-red-200">SUMBER #{{ $index + 1 }}</span>
                                    <span class="text-sm font-semibold text-red-900 dark:text-red-100">{{ $mergePreview['source_names'][$index] ?? 'Unknown' }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    @foreach ($source as $field => $value)
                                        <div class="truncate">
                                            <span class="text-gray-500">{{ $field }}:</span>
                                            <span class="ml-1 text-gray-800 dark:text-gray-300">{{ is_scalar($value) ? $value : json_encode($value) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <p class="mt-2 text-xs text-red-600 dark:text-red-400 italic">Data ini akan dihapus setelah penggabungan. Relasi akan dipindahkan ke target.</p>
                            </div>
                        @endforeach>

                        {{-- Warning --}}
                        <div class="flex items-start gap-3 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" />
                            <div class="text-sm text-amber-800 dark:text-amber-200">
                                <p class="font-semibold mb-1">Perhatian: Tindakan ini tidak dapat dibatalkan!</p>
                                <p>Semua relasi dari data sumber akan dipindahkan ke data target. Data sumber akan dihapus secara permanen. Pastikan Anda sudah memeriksa dengan teliti sebelum melanjutkan.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="sticky bottom-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 px-6 py-4 rounded-b-2xl flex items-center justify-end gap-3">
                        <x-filament::button
                            wire:click="closeMergeModal"
                            color="gray"
                            size="sm"
                        >
                            Batal
                        </x-filament::button>
                        <x-filament::button
                            wire:click="executeMerge"
                            color="danger"
                            size="sm"
                            icon="heroicon-o-arrows-pointing-in"
                        >
                            Gabungkan Data
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-filament-panels::page>
