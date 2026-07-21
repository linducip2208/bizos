<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center">
                        <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2M12 3a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $client['name'] ?? '' }}</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $client['client_type'] ?? 'Perusahaan' }} · {{ $client['industry'] ?? 'Industri tidak diketahui' }}
                            @if($client['city'])
                                · {{ $client['city'] }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ \App\Filament\Resources\Clients\ClientResource::getUrl('edit', ['record' => $client['id']]) }}"
                       class="fi-btn relative inline-flex items-center justify-center font-semibold outline-none transition duration-75 px-3 py-2 rounded-lg text-sm bg-primary-600 text-white hover:bg-primary-500">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-5m-1.414-9.414a2 2 0 1 1 2.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                <div>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Email</span>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $client['email'] ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Telepon</span>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $client['phone'] ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-400 dark:text-gray-500">Website</span>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $client['website'] ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-xs text-gray-400 dark:text-gray-500">NPWP</span>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $client['tax_id'] ?? '-' }}</p>
                </div>
            </div>

            @if(!empty($client['address']))
                <div class="mt-4 flex items-start gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                    </svg>
                    <span>{{ $client['address'] }}{{ !empty($client['city']) ? ', ' . $client['city'] : '' }}{{ !empty($client['province']) ? ', ' . $client['province'] : '' }}</span>
                </div>
            @endif
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Total Deal</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_deals'] }}</div>
                <div class="text-xs {{ $stats['won_deals'] > 0 ? 'text-green-600' : 'text-gray-400' }} mt-0.5">{{ $stats['won_deals'] }} won</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Nilai Deal</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($stats['total_deal_value'], 0, ',', '.') }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Invoice</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_invoices'] }}</div>
                <div class="text-xs {{ $stats['paid_invoices'] > 0 ? 'text-green-600' : 'text-gray-400' }} mt-0.5">{{ $stats['paid_invoices'] }} lunas</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Tiket</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_tickets'] }}</div>
                <div class="text-xs {{ $stats['open_tickets'] > 0 ? 'text-yellow-600' : 'text-gray-400' }} mt-0.5">{{ $stats['open_tickets'] }} terbuka</div>
            </div>
        </div>

        {{-- Deals Timeline --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                Pipeline Deal
            </h2>

            @if(count($deals) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Judul</th>
                                <th class="text-left py-2 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tahap</th>
                                <th class="text-right py-2 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nilai</th>
                                <th class="text-center py-2 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deals as $deal)
                                <tr class="border-b border-gray-100 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="py-2.5 px-3">
                                        <a href="{{ \App\Filament\Resources\Deals\DealResource::getUrl('edit', ['record' => $deal['id']]) }}" class="text-gray-900 dark:text-white font-medium hover:text-indigo-600 dark:hover:text-indigo-400">
                                            {{ $deal['title'] ?? 'Tanpa Judul' }}
                                        </a>
                                    </td>
                                    <td class="py-2.5 px-3 text-gray-600 dark:text-gray-400">{{ $deal['stage']['name'] ?? '-' }}</td>
                                    <td class="py-2.5 px-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format((float)($deal['expected_value'] ?? 0), 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-3 text-center">
                                        @php
                                            $statusClass = match($deal['status'] ?? '') {
                                                'won' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                'lost' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                'open' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                            };
                                        @endphp
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $statusClass }}">
                                            {{ ucfirst($deal['status'] ?? 'open') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-400 dark:text-gray-500 italic">Belum ada deal untuk klien ini.</p>
            @endif
        </div>

        {{-- Invoices --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg>
                Invoice
            </h2>

            @if(count($invoices) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">No Invoice</th>
                                <th class="text-left py-2 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                                <th class="text-right py-2 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                                <th class="text-center py-2 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($invoices, 0, 10) as $invoice)
                                <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                    <td class="py-2.5 px-3 font-medium text-gray-900 dark:text-white">{{ $invoice['invoice_number'] ?? '-' }}</td>
                                    <td class="py-2.5 px-3 text-gray-600 dark:text-gray-400">{{ isset($invoice['invoice_date']) ? \Carbon\Carbon::parse($invoice['invoice_date'])->format('d M Y') : '-' }}</td>
                                    <td class="py-2.5 px-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format((float)($invoice['grand_total'] ?? 0), 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-3 text-center">
                                        @php
                                            $invStatusClass = match($invoice['status'] ?? '') {
                                                'paid' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                'overdue' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                                'partial' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                            };
                                        @endphp
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $invStatusClass }}">
                                            {{ ucfirst($invoice['status'] ?? 'unpaid') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-400 dark:text-gray-500 italic">Belum ada invoice untuk klien ini.</p>
            @endif
        </div>

        {{-- Tickets --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 0 0 2-2V9.414a1 1 0 0 0-.293-.707l-5.414-5.414A1 1 0 0 0 12.586 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2z"/></svg>
                Tiket
            </h2>
            @if(count($tickets) > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">No Tiket</th>
                                <th class="text-left py-2 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Subjek</th>
                                <th class="text-left py-2 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Prioritas</th>
                                <th class="text-center py-2 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(array_slice($tickets, 0, 10) as $ticket)
                                <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                    <td class="py-2.5 px-3 text-gray-600 dark:text-gray-400">{{ $ticket['ticket_number'] ?? '-' }}</td>
                                    <td class="py-2.5 px-3 font-medium text-gray-900 dark:text-white">{{ $ticket['subject'] ?? '-' }}</td>
                                    <td class="py-2.5 px-3">
                                        @php
                                            $prioClass = match($ticket['priority'] ?? '') {
                                                'urgent' => 'text-red-600 dark:text-red-400 font-bold',
                                                'high' => 'text-orange-600 dark:text-orange-400 font-semibold',
                                                default => 'text-gray-600 dark:text-gray-400'
                                            };
                                        @endphp
                                        <span class="{{ $prioClass }}">{{ ucfirst($ticket['priority'] ?? 'medium') }}</span>
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        @php
                                            $ticketStatusClass = match($ticket['status'] ?? '') {
                                                'resolved', 'closed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                                'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                                default => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400'
                                            };
                                        @endphp
                                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $ticketStatusClass }}">
                                            {{ $ticket['status'] ?? 'open' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-400 dark:text-gray-500 italic">Belum ada tiket untuk klien ini.</p>
            @endif
        </div>

        {{-- Activity Timeline --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg>
                Timeline Aktivitas
            </h2>

            @if(count($timeline) > 0)
                <div class="space-y-0 ml-2">
                    @foreach(array_slice($timeline, 0, 30) as $event)
                        <div class="flex gap-3 pb-4 border-l-2 border-gray-200 dark:border-gray-700 pl-4 relative">
                            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full border-2 border-white dark:border-gray-800 {{ match($event['color']) {'green' => 'bg-green-500', 'red' => 'bg-red-500', 'blue' => 'bg-blue-500', 'yellow' => 'bg-yellow-500', 'indigo' => 'bg-indigo-500', default => 'bg-gray-400'} }}"></div>
                            <div class="flex-1 -mt-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $event['title'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $event['description'] }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ is_numeric($event['date'] ?? null) || strtotime($event['date'] ?? '') ? \Carbon\Carbon::parse($event['date'])->setTimezone('Asia/Jakarta')->format('d M Y, H:i') : ($event['date'] ?? '').' WIB' }}</p>
                            </div>
                            @if(!empty($event['status']))
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 self-start">
                                    {{ $event['status'] }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400 dark:text-gray-500 italic">Belum ada aktivitas.</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
