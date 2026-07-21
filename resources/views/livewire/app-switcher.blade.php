<div>
    {{-- App Switcher Trigger Button --}}
    <button
        wire:click="toggleGrid"
        type="button"
        class="fi-icon-btn relative flex items-center justify-center rounded-lg w-9 h-9 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors"
        title="App Launcher"
    >
        <x-heroicon-o-squares-2x2 class="w-5 h-5 text-gray-600 dark:text-gray-300" />
    </button>

    {{-- App Grid Overlay --}}
    @if($showGrid)
        <div
            class="fixed inset-0 z-50 flex items-start justify-center pt-20"
            x-data
            x-init="$el.focus()"
            @click.self="closeGrid"
            @keydown.escape.window="closeGrid"
        >
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="closeGrid"></div>

            {{-- Panel --}}
            <div
                class="relative w-full max-w-2xl mx-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden animate-scale-in"
                @click.stop
            >
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">BizOS Apps</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">8 Super Apps &mdash; satu platform bisnis</p>
                    </div>
                    <button wire:click="closeGrid" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Search --}}
                <div class="px-6 py-3 border-b border-gray-100 dark:border-gray-700">
                    <div class="relative">
                        <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                        <input
                            wire:model.live.debounce.150ms="searchQuery"
                            type="text"
                            placeholder="Cari app atau fitur..."
                            class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-700/50 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400"
                        />
                    </div>
                </div>

                {{-- App Grid --}}
                <div class="p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach($this->filteredApps as $app)
                            <button
                                wire:click="switchApp('{{ $app['key'] }}'); closeGrid()"
                                class="{{ $this->getCardClasses($app['color'], $app['key']) }}"
                            >
                                <div class="{{ $this->getCardIconClasses($app['color']) }}">
                                    @switch($app['icon'])
                                        @case('heroicon-o-home')
                                            <x-heroicon-o-home class="w-5 h-5" />
                                            @break
                                        @case('heroicon-o-users')
                                            <x-heroicon-o-users class="w-5 h-5" />
                                            @break
                                        @case('heroicon-o-banknotes')
                                            <x-heroicon-o-banknotes class="w-5 h-5" />
                                            @break
                                        @case('heroicon-o-chart-bar')
                                            <x-heroicon-o-chart-bar class="w-5 h-5" />
                                            @break
                                        @case('heroicon-o-clipboard-document-check')
                                            <x-heroicon-o-clipboard-document-check class="w-5 h-5" />
                                            @break
                                        @case('heroicon-o-shopping-cart')
                                            <x-heroicon-o-shopping-cart class="w-5 h-5" />
                                            @break
                                        @case('heroicon-o-academic-cap')
                                            <x-heroicon-o-academic-cap class="w-5 h-5" />
                                            @break
                                        @case('heroicon-o-cpu-chip')
                                            <x-heroicon-o-cpu-chip class="w-5 h-5" />
                                            @break
                                        @default
                                            <x-heroicon-o-rectangle-stack class="w-5 h-5" />
                                    @endswitch
                                </div>
                                <span class="text-xs font-semibold text-gray-900 dark:text-white text-center leading-tight">{{ $app['name'] }}</span>
                                @if(!empty($app['groups']))
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 text-center leading-tight">{{ count($app['groups']) }} modul</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Favorites Bar --}}
                @if(!empty($favorites))
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Favorit</h3>
                            <span class="text-[10px] text-gray-400">{{ count($favorites) }} item</span>
                        </div>
                        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-thin">
                            @foreach($favorites as $fav)
                                <a href="{{ $fav['url'] }}"
                                   wire:navigate
                                   class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 hover:border-indigo-300 transition-colors group/fav whitespace-nowrap"
                                >
                                    @if($fav['icon'] === 'heroicon-o-user-group')
                                        <x-heroicon-o-user-group class="w-3.5 h-3.5 text-gray-400 group-hover/fav:text-indigo-500" />
                                    @elseif($fav['icon'] === 'heroicon-o-building-office-2')
                                        <x-heroicon-o-building-office-2 class="w-3.5 h-3.5 text-gray-400 group-hover/fav:text-indigo-500" />
                                    @elseif($fav['icon'] === 'heroicon-o-banknotes')
                                        <x-heroicon-o-banknotes class="w-3.5 h-3.5 text-gray-400 group-hover/fav:text-indigo-500" />
                                    @elseif($fav['icon'] === 'heroicon-o-shopping-cart')
                                        <x-heroicon-o-shopping-cart class="w-3.5 h-3.5 text-gray-400 group-hover/fav:text-indigo-500" />
                                    @elseif($fav['icon'] === 'heroicon-o-chart-bar')
                                        <x-heroicon-o-chart-bar class="w-3.5 h-3.5 text-gray-400 group-hover/fav:text-indigo-500" />
                                    @elseif($fav['icon'] === 'heroicon-o-ticket')
                                        <x-heroicon-o-ticket class="w-3.5 h-3.5 text-gray-400 group-hover/fav:text-indigo-500" />
                                    @elseif($fav['icon'] === 'heroicon-o-cpu-chip')
                                        <x-heroicon-o-cpu-chip class="w-3.5 h-3.5 text-gray-400 group-hover/fav:text-indigo-500" />
                                    @elseif($fav['icon'] === 'heroicon-o-document-plus')
                                        <x-heroicon-o-document-plus class="w-3.5 h-3.5 text-gray-400 group-hover/fav:text-indigo-500" />
                                    @else
                                        <x-heroicon-o-star class="w-3.5 h-3.5 text-gray-400 group-hover/fav:text-indigo-500" />
                                    @endif
                                    {{ $fav['label'] }}
                                    <button
                                        wire:click.stop="removeFavorite('{{ $fav['type'] }}')"
                                        class="ml-1 text-gray-300 hover:text-red-400"
                                        title="Hapus favorit"
                                    >
                                        <x-heroicon-o-x-mark class="w-3 h-3" />
                                    </button>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Recently Viewed --}}
                @if(!empty($recentlyViewed))
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Terbaru Dilihat</h3>
                        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-thin">
                            @foreach($recentlyViewed as $recent)
                                <a href="{{ $recent['url'] }}"
                                   wire:navigate
                                   class="flex-shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-xs font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors whitespace-nowrap"
                                >
                                    <x-heroicon-o-clock class="w-3.5 h-3.5 text-gray-400" />
                                    {{ $recent['label'] }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

@push('styles')
<style>
    @keyframes scale-in {
        0% { transform: scale(0.95); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-scale-in {
        animation: scale-in 0.2s ease-out;
    }
    .scrollbar-thin::-webkit-scrollbar { height: 4px; }
    .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
    .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.3); border-radius: 4px; }
</style>
@endpush
