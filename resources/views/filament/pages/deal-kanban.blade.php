<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($stages as $stage)
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-3 h-3 rounded-full" style="background-color: {{ $stage['color'] ?? '#6366f1' }}"></div>
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $stage['name'] }}</span>
                    </div>
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stage['deal_count'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Rp {{ number_format($stage['total_value'], 0, ',', '.') }}</div>
                </div>
            @endforeach
        </div>

        {{-- Kanban Board --}}
        <div class="kanban-board overflow-x-auto pb-4" style="min-height: 500px;">
            <div class="flex gap-4" style="min-width: max-content;">
                @foreach($stages as $stage)
                    <div class="kanban-column flex-shrink-0 w-72 bg-gray-50 dark:bg-gray-800/50 rounded-xl p-3"
                         data-stage-id="{{ $stage['id'] }}"
                         x-data="{ over: false }"
                         x-on:dragover.prevent="over = true"
                         x-on:dragleave.prevent="over = false"
                         x-on:drop.prevent="over = false; $wire.moveDeal($event.dataTransfer.getData('dealId'), {{ $stage['id'] }})"
                         :class="{ 'ring-2 ring-indigo-400 bg-indigo-50 dark:bg-indigo-900/20': over }">
                        <div class="flex items-center justify-between mb-3 px-1">
                            <div class="flex items-center gap-2">
                                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background-color: {{ $stage['color'] ?? '#6366f1' }}"></div>
                                <h3 class="font-semibold text-sm text-gray-800 dark:text-gray-200">{{ $stage['name'] }}</h3>
                            </div>
                            <span class="text-xs bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full font-medium">
                                {{ $stage['deal_count'] }}
                            </span>
                        </div>

                        <div class="space-y-2 min-h-[100px]">
                            @foreach($stage['deals'] as $deal)
                                <div class="kanban-card bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-3 cursor-grab active:cursor-grabbing shadow-sm hover:shadow-md transition-shadow"
                                     draggable="true"
                                     data-deal-id="{{ $deal['id'] }}"
                                     x-on:dragstart="event.dataTransfer.setData('dealId', '{{ $deal['id'] }}')">
                                    <div class="flex items-start justify-between mb-2">
                                        <a href="{{ DealResource::getUrl('edit', ['record' => $deal['id']]) }}"
                                           class="text-sm font-semibold text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 line-clamp-2 flex-1">
                                            {{ $deal['title'] }}
                                        </a>
                                    </div>

                                    @if(!empty($deal['client']))
                                        <div class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400 mb-2">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/>
                                            </svg>
                                            <span class="truncate">{{ $deal['client']['name'] ?? '' }}</span>
                                        </div>
                                    @endif

                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                                            Rp {{ number_format((float)($deal['expected_value'] ?? 0), 0, ',', '.') }}
                                        </span>
                                        @php
                                            $probability = $stage['probability_percent'] ?? 0;
                                            $probClass = $probability < 30 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' :
                                                ($probability < 70 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400' :
                                                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400');
                                        @endphp
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $probClass }}">
                                            {{ $probability }}%
                                        </span>
                                    </div>

                                    @if(!empty($deal['assigned_to']))
                                        <div class="mt-2 flex items-center gap-1.5">
                                            <div class="w-5 h-5 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-[10px] font-bold text-indigo-600 dark:text-indigo-400 flex-shrink-0">
                                                {{ strtoupper(substr($deal['assigned_to']['first_name'] ?? '?', 0, 1)) }}
                                            </div>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                {{ $deal['assigned_to']['first_name'] ?? '' }} {{ $deal['assigned_to']['last_name'] ?? '' }}
                                            </span>
                                        </div>
                                    @endif

                                    @if(!empty($deal['expected_close_date']))
                                        <div class="mt-2 flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                                            </svg>
                                            <span>{{ \Carbon\Carbon::parse($deal['expected_close_date'])->format('d M Y') }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            @if(count($stage['deals']) === 0)
                                <div class="flex items-center justify-center h-16 text-xs text-gray-400 dark:text-gray-500 italic">
                                    Seret deal ke sini
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Create Deal Modal --}}
    <x-filament::modal id="create-deal-modal" width="lg">
        <x-slot name="heading">Buat Deal Baru</x-slot>
        <x-slot name="description">Isi detail deal baru untuk pipeline.</x-slot>
        {{ $this->form }}
    </x-filament::modal>

    {{-- Drag & Drop Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.kanban-card').forEach(card => {
                card.addEventListener('dragstart', function(e) {
                    this.classList.add('opacity-50', 'scale-95');
                });
                card.addEventListener('dragend', function(e) {
                    this.classList.remove('opacity-50', 'scale-95');
                });
            });

            document.querySelectorAll('.kanban-column').forEach(column => {
                column.addEventListener('dragover', function(e) {
                    e.preventDefault();
                });
            });
        });

        Livewire.on('deal-moved', (data) => {
            // Refresh kanban handled by Livewire reactivity
        });
    </script>

    <style>
        .kanban-board::-webkit-scrollbar { height: 6px; }
        .kanban-board::-webkit-scrollbar-track { background: transparent; }
        .kanban-board::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .dark .kanban-board::-webkit-scrollbar-thumb { background: #475569; }
        .kanban-card { transition: all 0.2s ease; }
    </style>
</x-filament-panels::page>
