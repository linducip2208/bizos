@extends('client-portal.layout')

@section('title', 'Buat Tiket')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Buat Tiket Baru</h1><p class="text-sm text-gray-500 mt-1">Laporkan masalah atau ajukan pertanyaan</p></div>
    @if($errors->any())<div class="p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ $errors->first() }}</div>@endif
    <form action="{{ route('client.tickets.store') }}" method="POST">@csrf
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm space-y-4">
            <div><label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subjek <span class="text-red-500">*</span></label><input type="text" name="subject" id="subject" required maxlength="500" value="{{ old('subject') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Subjek singkat"></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label><select name="category_id" id="category_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"><option value="">Pilih kategori</option>@foreach($categories as $c)<option value="{{ $c->id }}" {{ old('category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select></div>
                <div><label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Prioritas <span class="text-red-500">*</span></label><select name="priority" id="priority" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"><option value="low">Rendah</option><option value="medium" selected>Sedang</option><option value="high">Tinggi</option><option value="urgent">Urgent</option></select></div>
            </div>
            <div><label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi <span class="text-red-500">*</span></label><textarea name="description" id="description" rows="5" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="Jelaskan masalah atau pertanyaan Anda">{{ old('description') }}</textarea></div>
            <div class="flex gap-3 pt-2"><a href="{{ route('client.tickets') }}" class="px-4 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">Batal</a><button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition cursor-pointer">Buat Tiket</button></div>
        </div>
    </form>
</div>
@endsection
