<div>
    @if($isOpen)
        <div
            class="fixed inset-0 z-[60] flex items-start justify-center pt-[15vh]"
            x-data="commandPalette()"
            x-init="init()"
            @keydown.escape.window="$wire.close()"
            @click.self="$wire.close()"
            wire:keydown.arrow-up="decrementIndex"
            wire:keydown.arrow-down="incrementIndex"
            wire:keydown.enter="selectCurrent"
        >
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

            {{-- Panel --}}
            <div
                class="relative w-full max-w-xl mx-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden animate-scale-in"
                @click.stop
            >
                {{-- Search Input --}}
                <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                    <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400 flex-shrink-0" />
                    <input
                        wire:model.live.debounce.100ms="query"
                        type="text"
                        placeholder="Cari menu, resource, atau aksi..."
                        class="flex-1 bg-transparent text-gray-900 dark:text-gray-100 placeholder-gray-400 text-sm focus:outline-none"
                        autofocus
                        x-ref="searchInput"
                    />
                    <kbd class="hidden sm:inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-[10px] font-medium text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
                        <span>ESC</span>
                    </kbd>
                </div>

                {{-- Results --}}
                <div class="max-h-[360px] overflow-y-auto scrollbar-thin py-2">
                    @php
                        $itemIndex = 0;
                    @endphp

                    @forelse($results as $result)
                        @if($result['type'] === 'header')
                            <div class="px-4 py-1.5 mt-1">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $result['label'] }}</span>
                            </div>
                        @else
                            @php $currentIndex = $itemIndex; $itemIndex++; @endphp
                            <a
                                href="{{ $result['url'] ?? '#' }}"
                                wire:navigate
                                wire:click="close"
                                class="flex items-center gap-3 px-4 py-2.5 mx-2 rounded-lg text-sm transition-colors cursor-pointer
                                    {{ $selectedIndex === $currentIndex
                                        ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300'
                                        : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}"
                                data-result-index="{{ $currentIndex }}"
                            >
                                {{-- Icon --}}
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0
                                    {{ $selectedIndex === $currentIndex ? 'bg-indigo-100 dark:bg-indigo-800/40 text-indigo-600' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400' }}">
                                    @if(str_contains($result['icon'] ?? '', 'heroicon'))
                                        @php
                                            $iconName = str_replace('heroicon-o-', '', $result['icon'] ?? '');
                                            $iconName = str_replace('heroicon-m-', '', $iconName);
                                        @endphp
                                        <x-dynamic-component :component="'heroicon-o-' . $iconName" class="w-4 h-4" />
                                    @else
                                        <x-heroicon-o-rectangle-stack class="w-4 h-4" />
                                    @endif
                                </div>

                                {{-- Label + Group --}}
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium truncate">{{ $result['label'] }}</div>
                                    @if(!empty($result['group']))
                                        <div class="text-[11px] text-gray-400 dark:text-gray-500 truncate">{{ $result['group'] }}</div>
                                    @endif
                                </div>

                                {{-- Badge --}}
                                <span class="text-[10px] px-1.5 py-0.5 rounded-md font-medium flex-shrink-0
                                    @switch($result['type'] ?? '')
                                        @case('navigation')
                                            bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400
                                            @break
                                        @case('action')
                                            bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400
                                            @break
                                        @case('recent')
                                            bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400
                                            @break
                                        @case('favorite')
                                            bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400
                                            @break
                                        @default
                                            bg-gray-50 text-gray-500 dark:bg-gray-700 dark:text-gray-400
                                    @endswitch
                                ">
                                    @switch($result['type'] ?? '')
                                        @case('navigation') Menu @break
                                        @case('action') Aksi @break
                                        @case('recent') Baru @break
                                        @case('favorite') Favorit @break
                                        @default {{ $result['type'] ?? '' }}
                                    @endswitch
                                </span>

                                {{-- Enter Hint --}}
                                @if($selectedIndex === $currentIndex)
                                    <kbd class="hidden sm:inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono text-gray-400 bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">&#8629;</kbd>
                                @endif
                            </a>
                        @endif
                    @empty
                        <div class="px-4 py-8 text-center">
                            <x-heroicon-o-magnifying-glass class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                            <p class="text-sm text-gray-500 dark:text-gray-400">Ketik untuk mencari menu, resource, atau aksi</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Gunakan <kbd class="px-1 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-[10px] font-mono border border-gray-200 dark:border-gray-600">&uarr;</kbd> <kbd class="px-1 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-[10px] font-mono border border-gray-200 dark:border-gray-600">&darr;</kbd> untuk navigasi, <kbd class="px-1 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-[10px] font-mono border border-gray-200 dark:border-gray-600">Enter</kbd> untuk pilih</p>
                        </div>
                    @endforelse
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between px-4 py-2 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-3 text-[10px] text-gray-400 dark:text-gray-500">
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
                    <span class="text-[10px] text-gray-400">Command Palette</span>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    function commandPalette() {
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
        animation: cmd-scale-in 0.15s ease-out;
    }
    @keyframes cmd-scale-in {
        0% { transform: scale(0.97); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .scrollbar-thin::-webkit-scrollbar { width: 5px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.25); border-radius: 5px; }
</style>
@endpush

{{-- Global Keyboard Shortcut Listener --}}
<script>
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            e.stopPropagation();
            Livewire.dispatch('openCommandPalette');
        }

        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            e.preventDefault();
            e.stopPropagation();
            Livewire.dispatch('toggleAppGrid');
        }
    });
</script>
