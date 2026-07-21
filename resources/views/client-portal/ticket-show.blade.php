@extends('client-portal.layout')

@section('title', 'Detail Tiket')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3"><a href="{{ route('client.tickets') }}" class="text-gray-400 hover:text-gray-600"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a><h1 class="text-2xl font-bold text-gray-900">#{{ $ticket->ticket_number }}</h1></div>

    @if(session('success'))<div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>@endif

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b flex justify-between"><h2 class="text-base font-semibold">{{ $ticket->subject }}</h2>@php $sc=['open'=>'bg-blue-100 text-blue-700','in_progress'=>'bg-amber-100 text-amber-700','resolved'=>'bg-emerald-100 text-emerald-700','closed'=>'bg-gray-100 text-gray-700']; @endphp<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $sc[$ticket->status] ?? '' }}">{{ strtoupper(str_replace('_',' ',$ticket->status)) }}</span></div>
        <div class="p-6 space-y-4">
            <div class="flex gap-4 text-xs text-gray-500"><span>Prioritas: <strong>{{ strtoupper($ticket->priority) }}</strong></span><span>Kategori: <strong>{{ $ticket->category?->name ?? '-' }}</strong></span><span>Dibuat: {{ $ticket->created_at->format('d M Y H:i') }}</span></div>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $ticket->description }}</p>
            @if($ticket->attachments->isNotEmpty())<div class="flex gap-2">@foreach($ticket->attachments as $att)<a href="{{ asset('storage/'.$att->file_path) }}" target="_blank" class="text-xs text-blue-600 underline">{{ $att->file_name }}</a>@endforeach</div>@endif
        </div>
    </div>

    @if($ticket->replies->isNotEmpty())
    <div class="space-y-3">
        @foreach($ticket->replies as $reply)
        <div class="bg-white rounded-xl border {{ $reply->employee_id ? 'border-blue-100 bg-blue-50/30' : 'border-gray-100' }} p-4">
            <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 rounded-full {{ $reply->employee_id ? 'bg-blue-500' : 'bg-gray-400' }} flex items-center justify-center text-white text-xs font-bold">{{ $reply->employee_id ? 'S' : strtoupper(substr($reply->user?->name ?? 'C', 0, 1)) }}</div><span class="text-xs font-medium">{{ $reply->employee_id ? ($reply->employee?->first_name ?? 'Support') : 'Anda' }}</span><span class="text-xs text-gray-400">{{ $reply->created_at->format('d M H:i') }}</span></div>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $reply->message }}</p>
        </div>
        @endforeach
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Balas Tiket</h3>
        <form action="{{ route('client.tickets.reply', $ticket->id) }}" method="POST">@csrf
            <div class="space-y-3"><textarea name="message" rows="3" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Tulis balasan Anda"></textarea><button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition cursor-pointer">Kirim Balasan</button></div>
        </form>
    </div>
</div>
@endsection
