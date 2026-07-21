<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
        @if ($unreadCount > 0)
            <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">{{ $unreadCount }}</span>
        @endif
    </button>

    <div x-show="open" @click.outside="open = false" x-transition
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 z-50 max-h-96 overflow-hidden">
        <div class="p-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="font-semibold text-sm text-gray-900 dark:text-white">Papan Pengumuman</h3>
            @if ($unreadCount > 0)
                <span class="text-xs text-red-500 font-medium">{{ $unreadCount }} belum dibaca</span>
            @endif
        </div>
        <div class="overflow-y-auto max-h-80 divide-y divide-gray-100 dark:divide-gray-700">
            @forelse ($notices as $notice)
                <div class="p-3 hover:bg-gray-50 dark:hover:bg-gray-750 cursor-pointer"
                     wire:click="toggleExpand({{ $notice['id'] }})">
                    <div class="flex items-start gap-2">
                        <span @class([
                            'mt-0.5 w-2 h-2 rounded-full shrink-0',
                            'bg-red-500' => ($notice['priority'] ?? 'normal') === 'urgent',
                            'bg-amber-500' => ($notice['priority'] ?? 'normal') === 'important',
                            'bg-gray-300' => ($notice['priority'] ?? 'normal') === 'normal',
                        ])></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $notice['title'] }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ \Carbon\Carbon::parse($notice['created_at'])->diffForHumans() }}
                                @if (!empty($notice['category']))
                                    <span @class([
                                        'ml-1 px-1.5 py-0.5 rounded text-[10px] font-medium',
                                        'bg-red-100 text-red-700' => $notice['category'] === 'urgent',
                                        'bg-blue-100 text-blue-700' => $notice['category'] === 'hr',
                                        'bg-purple-100 text-purple-700' => $notice['category'] === 'it',
                                        'bg-gray-100 text-gray-600' => true,
                                    ])>
                                        {{ match($notice['category']) { 'general' => 'Umum', 'hr' => 'HR', 'it' => 'IT', 'urgent' => 'Darurat', 'event' => 'Acara', default => $notice['category'] } }}
                                    </span>
                                @endif
                            </p>
                            @if ($expandedId === $notice['id'])
                                <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 leading-relaxed">{{ $notice['content'] }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-sm text-gray-500">Tidak ada pengumuman</div>
            @endforelse
        </div>
    </div>
</div>
