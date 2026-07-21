<div>
    <div class="rounded-xl bg-white dark:bg-gray-900 shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-4 py-3 border-b flex items-center justify-between">
            <h3 class="font-semibold text-sm">Papan Peringkat</h3>
            <div class="flex gap-1">
                <button wire:click="setPeriod('weekly')"
                    class="px-2 py-0.5 rounded text-xs font-medium transition {{ $period === 'weekly' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-100' }}">
                    Minggu
                </button>
                <button wire:click="setPeriod('monthly')"
                    class="px-2 py-0.5 rounded text-xs font-medium transition {{ $period === 'monthly' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-100' }}">
                    Bulan
                </button>
                <button wire:click="setPeriod('all_time')"
                    class="px-2 py-0.5 rounded text-xs font-medium transition {{ $period === 'all_time' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-100' }}">
                    Semua
                </button>
            </div>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($leaderboard as $entry)
                <div class="px-4 py-2.5 flex items-center gap-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <div class="w-6 text-center">
                        @if($entry['rank'] === 1)
                            <span class="text-amber-500 text-sm">1</span>
                        @elseif($entry['rank'] === 2)
                            <span class="text-gray-400 text-sm">2</span>
                        @elseif($entry['rank'] === 3)
                            <span class="text-amber-700 text-sm">3</span>
                        @else
                            <span class="text-gray-400 text-xs">{{ $entry['rank'] }}</span>
                        @endif
                    </div>
                    <div class="flex-shrink-0 w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-xs font-bold text-indigo-600 dark:text-indigo-300">
                        {{ strtoupper(substr($entry['name'], 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium truncate">{{ $entry['name'] }}</div>
                        <div class="text-xs text-gray-400 truncate">{{ $entry['department'] }}</div>
                    </div>
                    <div class="text-sm font-bold text-indigo-600">{{ $entry['points'] }}</div>
                </div>
            @endforeach
            @if(empty($leaderboard))
                <div class="px-4 py-8 text-center text-sm text-gray-400">Belum ada poin.</div>
            @endif
        </div>
    </div>
</div>
