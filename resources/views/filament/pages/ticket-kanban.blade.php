<x-filament-panels::page>
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 overflow-x-auto">
        @php
            $ticketsByStatus = $this->getTickets();
            $statusLabels = [
                'open' => 'Terbuka',
                'in_progress' => 'Dalam Proses',
                'waiting_on_customer' => 'Menunggu Pelanggan',
                'resolved' => 'Terselesaikan',
                'closed' => 'Tertutup',
            ];
            $statusColors = [
                'open' => 'border-blue-400',
                'in_progress' => 'border-amber-400',
                'waiting_on_customer' => 'border-gray-400',
                'resolved' => 'border-emerald-400',
                'closed' => 'border-gray-500',
            ];
            $badgeColors = [
                'open' => 'info',
                'in_progress' => 'warning',
                'waiting_on_customer' => 'gray',
                'resolved' => 'success',
                'closed' => 'gray',
            ];
            $priorityBadgeColors = [
                'low' => 'bg-gray-100 text-gray-700',
                'medium' => 'bg-amber-100 text-amber-700',
                'high' => 'bg-red-100 text-red-700',
                'urgent' => 'bg-red-200 text-red-800',
            ];
            $priorityLabels = [
                'low' => 'Rendah',
                'medium' => 'Sedang',
                'high' => 'Tinggi',
                'urgent' => 'Urgent',
            ];
        @endphp

        @foreach ($ticketsByStatus as $status => $tickets)
        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl border-t-4 {{ $statusColors[$status] }} min-w-[260px]">
            <div class="px-4 py-3 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $statusLabels[$status] ?? $status }}</h3>
                <span class="text-xs font-bold text-gray-500 dark:text-gray-400 bg-gray-200 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ $tickets->count() }}</span>
            </div>
            <div class="p-3 space-y-3 max-h-[65vh] overflow-y-auto">
                @forelse ($tickets as $ticket)
                <a href="{{ url('/admin/tickets/' . $ticket->id . '/edit') }}" class="block bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-3 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-mono text-gray-400">{{ $ticket->ticket_number }}</span>
                        <span class="inline-flex text-xs font-semibold px-1.5 py-0.5 rounded {{ $priorityBadgeColors[$ticket->priority] ?? 'bg-gray-100 text-gray-700' }}">
                            {{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}
                        </span>
                    </div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2 mb-2">{{ $ticket->subject }}</p>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>{{ $ticket->client?->name ?? '-' }}</span>
                        <span>{{ $ticket->assignedTo?->first_name ?? 'Belum' }}</span>
                    </div>
                    @if ($ticket->due_date)
                    <div class="mt-2 text-xs {{ $ticket->due_date->isPast() ? 'text-red-500 font-semibold' : 'text-gray-400' }}">
                        Batas: {{ $ticket->due_date->format('d M H:i') }}
                    </div>
                    @endif
                </a>
                @empty
                <div class="text-center py-6 text-sm text-gray-400">Tidak ada tiket</div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</x-filament-panels::page>
