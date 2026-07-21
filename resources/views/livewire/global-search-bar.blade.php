<div>
    @if($isOpen)
        <div
            class="fixed inset-0 z-[70] flex items-start justify-center pt-[10vh]"
            x-data="globalSearch()"
            x-init="init()"
            @keydown.escape.wire="close"
            @click.self="close"
            wire:keydown.arrow-up="incrementIndex"
            wire:keydown.arrow-down="decrementIndex"
            wire:keydown.enter="selectCurrent"
        >
            <div class="absolute inset-0 bg-black/60 backdrop-blur-md"></div>

            <div
                class="relative w-full max-w-2xl mx-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden animate-scale-in"
                @click.stop
            >
                {{-- Header: Search Input --}}
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-200 dark:border-gray-700">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400 flex-shrink-0" />
                    <input
                        wire:model.live.debounce.150ms="query"
                        type="text"
                        placeholder="Cari semua data: karyawan, klien, faktur, tiket, proyek..."
                        class="flex-1 bg-transparent text-gray-900 dark:text-gray-100 placeholder-gray-400 text-base focus:outline-none"
                        autofocus
                        x-ref="searchInput"
                    />
                    @if(!empty($query))
                        <button wire:click="close" class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-400">
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    @endif
                </div>

                {{-- Filter Chips --}}
                <div class="px-5 py-2.5 flex flex-wrap gap-1.5 border-b border-gray-100 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-800/50">
                    @foreach($availableModules as $key => $label)
                        <button
                            wire:click="toggleFilter('{{ $key }}')"
                            class="px-2.5 py-1 rounded-full text-[11px] font-medium transition-all
                                {{ in_array($key, $activeFilters)
                                    ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 ring-1 ring-indigo-300 dark:ring-indigo-700'
                                    : 'bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-600 ring-1 ring-gray-200 dark:ring-gray-600' }}"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                    @if(!empty($activeFilters))
                        <button
                            wire:click="$set('activeFilters', [])"
                            class="px-2.5 py-1 rounded-full text-[11px] font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20"
                        >
                            Hapus Filter
                        </button>
                    @endif
                </div>

                {{-- Results --}}
                <div class="max-h-[55vh] overflow-y-auto scrollbar-thin">
                    @if(mb_strlen(trim($query)) < 2)
                        {{-- Empty State: Popular Searches --}}
                        <div class="px-5 py-4">
                            @if(!empty($popularSearches))
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">Pencarian Populer</div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($popularSearches as $pop)
                                        <button
                                            wire:click="setQuery('{{ $pop['query'] }}')"
                                            class="px-3 py-1 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-indigo-900/30 dark:hover:text-indigo-400 transition-colors"
                                        >
                                            {{ $pop['query'] }}
                                            <span class="ml-1 text-[10px] text-gray-400">({{ $pop['count'] }})</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex flex-col items-center justify-center py-10 text-center">
                                <x-heroicon-o-globe-alt class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
                                <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Enterprise Search</h3>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 max-w-sm">
                                    Cari seluruh data bisnis Anda: karyawan, klien, lead, faktur, tiket, produk, proyek, rapat, chat, wiki, dokumen, kontrak, dan aset.
                                </p>
                                <p class="text-xs text-gray-400 mt-3">
                                    <kbd class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 font-mono">Ctrl+K</kbd>
                                    untuk buka kapan saja
                                </p>
                            </div>
                        </div>
                    @elseif(empty($results))
                        {{-- No Results --}}
                        <div class="flex flex-col items-center justify-center py-12 text-center px-5">
                            <x-heroicon-o-magnifying-glass class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">Tidak ditemukan hasil untuk <strong>"{{ $query }}"</strong></p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Coba kata kunci berbeda atau hapus filter</p>
                        </div>
                    @else
                        {{-- Results List --}}
                        @if($totalHits > 0)
                            <div class="px-5 py-2 flex items-center justify-between">
                                <span class="text-[11px] text-gray-400 dark:text-gray-500">{{ number_format($totalHits, 0, ',', '.') }} hasil · {{ $searchTimeMs }}ms</span>
                            </div>
                        @endif

                        @php $groupedResults = []; @endphp
                        @foreach($results as $i => $result)
                            @php
                                $module = $result['module'] ?? 'lainnya';
                                $groupedResults[$module][] = $result;
                            @endphp
                        @endforeach

                        @foreach($groupedResults as $module => $groupResults)
                            <div class="px-5 py-1.5 mt-1 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full flex-shrink-0
                                    @switch($module)
                                        @case('hrm') bg-indigo-500 @break
                                        @case('crm') bg-blue-500 @break
                                        @case('finance') bg-emerald-500 @break
                                        @case('helpdesk') bg-amber-500 @break
                                        @case('inventory') bg-purple-500 @break
                                        @case('project') bg-rose-500 @break
                                        @case('kolaborasi') bg-teal-500 @break
                                        @default bg-gray-400
                                    @endswitch
                                "></span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                    {{ $availableModules[$module] ?? ucfirst($module) }}
                                </span>
                            </div>

                            @foreach($groupResults as $result)
                                @php $globalIdx = array_search($result, $results); @endphp
                                <a
                                    href="{{ $result['url'] ?? '#' }}"
                                    wire:navigate
                                    wire:click="close"
                                    class="flex items-start gap-3.5 px-5 py-3 mx-2 rounded-lg transition-colors cursor-pointer
                                        {{ $selectedIndex === $globalIdx
                                            ? 'bg-indigo-50 dark:bg-indigo-900/20 ring-1 ring-indigo-200 dark:ring-indigo-800'
                                            : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}"
                                >
                                    {{-- Icon --}}
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5
                                        {{ $selectedIndex === $globalIdx
                                            ? 'bg-indigo-100 dark:bg-indigo-800/40 text-indigo-600'
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                                        @php
                                            $icon = str_replace('heroicon-o-', '', $result['icon'] ?? 'rectangle-stack');
                                        @endphp
                                        <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-4 h-4" />
                                    </div>

                                    {{-- Content --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                                                {!! !empty($result['highlights']['title'])
                                                    ? $result['highlights']['title']
                                                    : e($result['title'] ?? '') !!}
                                            </span>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded font-medium flex-shrink-0
                                                @switch($result['module'] ?? '')
                                                    @case('hrm') bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 @break
                                                    @case('crm') bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 @break
                                                    @case('finance') bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400 @break
                                                    @case('helpdesk') bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400 @break
                                                    @case('inventory') bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400 @break
                                                    @case('project') bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400 @break
                                                    @case('kolaborasi') bg-teal-50 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400 @break
                                                    @default bg-gray-50 text-gray-500 dark:bg-gray-700 dark:text-gray-400
                                                @endswitch
                                            ">
                                                {{ $modelLabel($result['model'] ?? '') }}
                                            </span>
                                        </div>

                                        @if(!empty($result['subtitle']))
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ $result['subtitle'] }}</div>
                                        @endif

                                        @if(!empty($result['highlights']['description']))
                                            <div class="text-xs text-gray-600 dark:text-gray-300 mt-0.5 line-clamp-2">{!! $result['highlights']['description'] !!}</div>
                                        @endif
                                    </div>

                                    @if($selectedIndex === $globalIdx)
                                        <kbd class="hidden sm:flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono text-gray-400 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 mt-0.5">&#8629;</kbd>
                                    @endif
                                </a>
                            @endforeach
                        @endforeach
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between px-5 py-2.5 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-4 text-[10px] text-gray-400 dark:text-gray-500">
                        <span class="flex items-center gap-1">
                            <kbd class="px-1 py-0.5 rounded bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 font-mono">&uarr;&darr;</kbd> Navigasi
                        </span>
                        <span class="flex items-center gap-1">
                            <kbd class="px-1 py-0.5 rounded bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 font-mono">Enter</kbd> Buka
                        </span>
                        <span class="flex items-center gap-1">
                            <kbd class="px-1 py-0.5 rounded bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 font-mono">Esc</kbd> Tutup
                        </span>
                    </div>
                    <span class="text-[10px] text-gray-400">Enterprise Search</span>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function globalSearch() {
        return {
            init() {
                this.$nextTick(() => {
                    const input = this.$refs.searchInput;
                    if (input) input.focus();
                });
            }
        }
    }
</script>

@push('styles')
<style>
    .animate-scale-in {
        animation: gs-scale-in 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes gs-scale-in {
        0% { transform: scale(0.95) translateY(-8px); opacity: 0; }
        100% { transform: scale(1) translateY(0); opacity: 1; }
    }
    .scrollbar-thin::-webkit-scrollbar { width: 6px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.25); border-radius: 6px; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush

{{-- Global Keyboard Shortcut --}}
<script>
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            e.stopPropagation();
            @this.open();
        }
    });
</script>
