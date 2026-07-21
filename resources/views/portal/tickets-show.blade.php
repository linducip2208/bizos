@extends('portal.layout')

@section('title', 'Tiket #' . $ticket->ticket_number)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="mb-4">
        <a href="{{ route('portal.tickets.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke daftar tiket
        </a>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-start justify-between mb-4">
            <div>
                <span class="text-xs font-mono text-gray-400">{{ $ticket->ticket_number }}</span>
                <h1 class="text-xl font-bold text-gray-900 mt-1">{{ $ticket->subject }}</h1>
            </div>
            <div class="flex gap-2">
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
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusColors[$ticket->status] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $statusLabels[$ticket->status] ?? $ticket->status }}
                </span>
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $priorityColors[$ticket->priority] ?? 'bg-gray-100 text-gray-700' }}">
                    {{ $priorityLabels[$ticket->priority] ?? $ticket->priority }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6 text-sm">
            <div>
                <span class="text-xs text-gray-400">Kategori</span>
                <p class="font-medium text-gray-800">{{ $ticket->category?->name ?? '-' }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400">Ditugaskan</span>
                <p class="font-medium text-gray-800">{{ $ticket->assignedTo?->first_name ?? 'Belum di-assign' }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400">Dibuat</span>
                <p class="font-medium text-gray-800">{{ $ticket->created_at->format('d M Y H:i') }}</p>
            </div>
            <div>
                <span class="text-xs text-gray-400">Diperbarui</span>
                <p class="font-medium text-gray-800">{{ $ticket->updated_at->format('d M Y H:i') }}</p>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-4">
            <h3 class="text-sm font-semibold text-gray-500 mb-2">Deskripsi</h3>
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! nl2br(e($ticket->description)) !!}
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-bold text-gray-900">Balasan</h2>

        @forelse ($ticket->replies as $reply)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold">
                    {{ strtoupper(substr($reply->employee?->first_name ?? $reply->user?->name ?? 'S', 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $reply->employee ? $reply->employee->first_name . ' ' . $reply->employee->last_name : ($reply->user?->name ?? 'Sistem') }}
                        @if($reply->employee)
                            <span class="text-xs text-indigo-500 font-normal">(Staf)</span>
                        @endif
                    </p>
                    <p class="text-xs text-gray-400">{{ $reply->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
            <div class="prose prose-sm max-w-none text-gray-700 ml-10">
                {!! nl2br(e($reply->message)) !!}
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-sm text-gray-400">
            Belum ada balasan.
        </div>
        @endforelse
    </div>

    @if (!in_array($ticket->status, ['closed']))
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4">Kirim Balasan</h3>
        <form action="{{ route('portal.tickets.reply', $ticket->id) }}" method="POST" class="space-y-4">
            @csrf
            <textarea name="message" required rows="4"
                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition"
                placeholder="Tulis balasan Anda..."></textarea>
            @error('message')
            <p class="text-xs text-red-500">{{ $message }}</p>
            @enderror
            <button type="submit"
                class="inline-flex items-center px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                Kirim Balasan
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
