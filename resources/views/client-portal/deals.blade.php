@extends('client-portal.layout')

@section('title', 'Deal')

@section('content')
<div class="space-y-6"><div><h1 class="text-2xl font-bold text-gray-900">Deal</h1><p class="text-sm text-gray-500 mt-1">Daftar deal/proposal Anda</p></div>
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"><div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr class="bg-gray-50 text-left"><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Judul</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Stage</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase text-right">Nilai</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Tgl Tutup</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Status</th><th class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase text-center">Detail</th></tr></thead><tbody class="divide-y divide-gray-100">
@forelse($deals as $deal) @php $sc=['won'=>'bg-emerald-100 text-emerald-700','lost'=>'bg-red-100 text-red-700']; @endphp
<tr class="hover:bg-gray-50"><td class="px-4 py-3 font-medium">{{ $deal->title }}</td><td class="px-4 py-3 text-gray-600">{{ $deal->stage?->name ?? '-' }}</td><td class="px-4 py-3 text-right font-mono">Rp {{ number_format($deal->expected_value, 0, ',', '.') }}</td><td class="px-4 py-3 text-gray-600">{{ $deal->expected_close_date?->format('d M Y') ?? '-' }}</td><td class="px-4 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc[$deal->status] ?? 'bg-blue-100 text-blue-700' }}">{{ strtoupper($deal->status) }}</span></td><td class="px-4 py-3 text-center"><a href="{{ route('client.deals.show', $deal->id) }}" class="text-blue-600 text-xs">Detail</a></td></tr>
@empty <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada deal.</td></tr> @endforelse
</tbody></table></div>@if($deals->hasPages())<div class="px-6 py-3 border-t">{{ $deals->links() }}</div>@endif</div></div>
@endsection
