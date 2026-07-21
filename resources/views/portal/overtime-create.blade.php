@extends('portal.layout')

@section('title', 'Ajukan Lembur')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Ajukan Lembur</h1><p class="text-sm text-gray-500 mt-1">Isi form untuk mengajukan lembur</p></div>

    @if ($errors->any())<div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif

    <form action="{{ route('portal.overtime.store') }}" method="POST">
        @csrf
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm space-y-4">
            <div><label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal <span class="text-red-500">*</span></label><input type="date" name="date" id="date" required min="{{ date('Y-m-d') }}" value="{{ old('date', date('Y-m-d')) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">Jam Mulai <span class="text-red-500">*</span></label><input type="time" name="start_time" id="start_time" required value="{{ old('start_time', '17:00') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"></div>
                <div><label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">Jam Selesai <span class="text-red-500">*</span></label><input type="time" name="end_time" id="end_time" required value="{{ old('end_time', '20:00') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"></div>
            </div>
            <div><label for="rate_multiplier" class="block text-sm font-medium text-gray-700 mb-1">Rate Multiplier</label><select name="rate_multiplier" id="rate_multiplier" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"><option value="1.5">1.5x (Regular)</option><option value="2">2x (Weekend)</option><option value="3">3x (Hari Libur)</option></select></div>
            <div><label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Alasan <span class="text-red-500">*</span></label><textarea name="reason" id="reason" rows="3" required maxlength="1000" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition" placeholder="Jelaskan alasan lembur">{{ old('reason') }}</textarea></div>
            <div class="flex gap-3 pt-2"><a href="{{ route('portal.overtime.index') }}" class="px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Batal</a><button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition cursor-pointer">Ajukan Lembur</button></div>
        </div>
    </form>
</div>
@endsection
