<x-filament-panels::page>
    @push('styles')
    <style>
        .anomaly-card {
            transition: all 0.25s ease;
        }
        .anomaly-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -8px rgba(0,0,0,.12);
        }
        .severity-high {
            border-left: 4px solid #ef4444;
        }
        .severity-medium {
            border-left: 4px solid #f59e0b;
        }
        .severity-low {
            border-left: 4px solid #3b82f6;
        }
    </style>
    @endpush

    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Dashboard Anomali</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Pemindaian otomatis anomali di seluruh modul bisnis
                </p>
            </div>
            <x-filament::button wire:click="refreshAnomalies" color="gray" size="sm" outlined>
                <x-filament::icon icon="heroicon-o-arrow-path" class="w-4 h-4" />
                Refresh
            </x-filament::button>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Anomali</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-shield-exclamation" class="w-5 h-5 text-gray-500" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-500 dark:text-red-400">High</p>
                        <p class="text-3xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['high'] ?? 0 }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-exclamation-circle" class="w-5 h-5 text-red-500" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-amber-500 dark:text-amber-400">Medium</p>
                        <p class="text-3xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['medium'] ?? 0 }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-5 h-5 text-amber-500" />
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-500 dark:text-blue-400">Low</p>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ $stats['low'] ?? 0 }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                        <x-filament::icon icon="heroicon-o-information-circle" class="w-5 h-5 text-blue-500" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap gap-2">
            <x-filament::button wire:click="$set('activeFilter', 'all'); $refresh" color="{{ $activeFilter === 'all' ? 'primary' : 'gray' }}" size="xs" outlined>
                Semua ({{ $stats['total'] ?? 0 }})
            </x-filament::button>
            <x-filament::button wire:click="$set('activeFilter', 'high'); $refresh" color="danger" size="xs" outlined>
                High ({{ $stats['high'] ?? 0 }})
            </x-filament::button>
            <x-filament::button wire:click="$set('activeFilter', 'medium'); $refresh" color="warning" size="xs" outlined>
                Medium ({{ $stats['medium'] ?? 0 }})
            </x-filament::button>
            <x-filament::button wire:click="$set('activeFilter', 'low'); $refresh" color="info" size="xs" outlined>
                Low ({{ $stats['low'] ?? 0 }})
            </x-filament::button>
            @foreach(array_keys($anomaliesByModule) as $module)
            <x-filament::button wire:click="$set('activeFilter', '{{ $module }}'); $refresh" color="gray" size="xs" outlined>
                {{ $module }} ({{ count($anomaliesByModule[$module]) }})
            </x-filament::button>
            @endforeach
        </div>

        {{-- Anomaly List --}}
        @php $filteredAnomalies = $this->getFilteredAnomalies(); @endphp

        @if(count($filteredAnomalies) === 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                <x-filament::icon icon="heroicon-o-check-circle" class="w-16 h-16 text-green-400 mx-auto mb-4" />
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tidak Ada Anomali</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Semua sistem berjalan normal.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-3">
                @foreach($filteredAnomalies as $anomaly)
                <a href="{{ url($anomaly['link'] ?? '#') }}"
                   class="anomaly-card severity-{{ $anomaly['severity'] ?? 'low' }} bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-750 transition-all block">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 mt-0.5">
                            @php $icon = $this->getSeverityIcon($anomaly['severity'] ?? 'low'); @endphp
                            <x-filament::icon icon="{{ $icon }}" class="w-5 h-5 text-{{ $this->getSeverityColor($anomaly['severity'] ?? 'low') }}-500" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                    @if(($anomaly['severity'] ?? '') === 'high') bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-300
                                    @elseif(($anomaly['severity'] ?? '') === 'medium') bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300
                                    @else bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300
                                    @endif">
                                    {{ $anomaly['module'] ?? '' }}
                                </span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $anomaly['detected_at'] ?? '' }}</span>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $anomaly['title'] ?? '' }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $anomaly['description'] ?? '' }}</p>
                        </div>
                        <div class="shrink-0">
                            <x-filament::icon icon="heroicon-o-arrow-right" class="w-4 h-4 text-gray-400" />
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        @endif

        {{-- Module Groups --}}
        @if($activeFilter === 'all')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($anomaliesByModule as $module => $items)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $module }} ({{ count($items) }})</h3>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach(array_slice($items, 0, 5) as $anomaly)
                    <a href="{{ url($anomaly['link'] ?? '#') }}" class="block px-4 py-2.5 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors">
                        <div class="flex items-center gap-2">
                            @php $icon = $this->getSeverityIcon($anomaly['severity'] ?? 'low'); @endphp
                            <x-filament::icon icon="{{ $icon }}" class="w-4 h-4 text-{{ $this->getSeverityColor($anomaly['severity'] ?? 'low') }}-500 shrink-0" />
                            <span class="text-xs text-gray-700 dark:text-gray-300 truncate">{{ $anomaly['title'] }}</span>
                        </div>
                    </a>
                    @endforeach
                    @if(count($items) > 5)
                    <div class="px-4 py-2 text-xs text-gray-400">
                        + {{ count($items) - 5 }} lainnya
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-filament::page>
