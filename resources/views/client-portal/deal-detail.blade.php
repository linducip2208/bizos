@extends('client-portal.layout')

@section('title', 'Detail Deal')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3"><a href="{{ route('client.deals') }}" class="text-gray-400 hover:text-gray-600"><svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></a><h1 class="text-2xl font-bold text-gray-900">{{ $deal->title }}</h1></div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"><div class="px-6 py-4 border-b flex justify-between"><h2 class="text-base font-semibold">Informasi Deal</h2><span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">{{ strtoupper($deal->status) }}</span></div><div class="p-6 grid grid-cols-2 gap-4"><div><p class="text-xs text-gray-400 uppercase">Stage</p><p class="text-sm font-medium">{{ $deal->stage?->name ?? '-' }}</p></div><div><p class="text-xs text-gray-400 uppercase">Nilai</p><p class="text-lg font-bold">Rp {{ number_format($deal->expected_value, 0, ',', '.') }}</p></div><div><p class="text-xs text-gray-400 uppercase">Estimasi Close</p><p class="text-sm">{{ $deal->expected_close_date?->format('d M Y') ?? '-' }}</p></div><div><p class="text-xs text-gray-400 uppercase">Aktual Close</p><p class="text-sm">{{ $deal->actual_close_date?->format('d M Y') ?? '-' }}</p></div>@if($deal->assignedTo)<div><p class="text-xs text-gray-400 uppercase">PIC</p><p class="text-sm">{{ $deal->assignedTo->first_name }} {{ $deal->assignedTo->last_name }}</p></div>@endif</div>@if($deal->notes)<div class="px-6 pb-4"><p class="text-xs text-gray-400 uppercase mb-1">Catatan</p><p class="text-sm text-gray-700">{{ $deal->notes }}</p></div>@endif</div>
</div>
@endsection
