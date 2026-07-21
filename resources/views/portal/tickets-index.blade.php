@extends('portal.layout')

@section('title', 'Tiket Saya')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tiket Saya</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar tiket dukungan Anda</p>
        </div>
        <a href="{{ route('portal.tickets.create') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Buat Tiket
        </a>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">No. Tiket</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Subjek</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Prioritas</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Diperbarui</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($tickets as $ticket)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 font-mono text-sm font-medium text-gray-900">{{ $ticket->ticket_number }}</td>
                        <td class="px-6 py-3 text-gray-900 font-medium">{{ Str::limit($ticket->subject, 40) }}</td>
                        <td class="px-6 py-3 text-gray-600">{{ $ticket->category?->name ?? '-' }}</td>
                        <td class="px-6 py-3">
                            @php
                                $priorityColors = [
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
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            @php
                                $statusColors = [
                                    'open' => 'bg-blue-100 text-blue-700',
                                    'in_progress' => 'bg-amber-100 text-amber-700',
                                    'waiting_on_customer' => 'bg-gray-100 text-gray-700',
                                    'resolved' => 'bg-emerald-100 text-emerald-700',
                                    'closed' => 'bg-gray-100 text-gray-500',
                                ];
                                $statusLabels = [
                                    'open' => 'Terbuka',
                                    'in_progress' => 'Dalam Proses',
                                    'waiting_on_customer' => 'Menunggu Pelanggan',
                                    'resolved' => 'Terselesaikan',
                                    'closed' => 'Tertutup',
                                ];
                            @endphp
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $ticket->updated_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-3 text-center">
                            <a href="{{ route('portal.tickets.show', $ticket->id) }}"
                                class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-400">
                            Belum ada tiket. <a href="{{ route('portal.tickets.create') }}" class="text-indigo-600 hover:underline">Buat tiket pertama</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($tickets->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
