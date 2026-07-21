@extends('portal.layout')

@section('title', 'Buat Tiket Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('portal.tickets.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke daftar tiket
        </a>
    </div>

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Buat Tiket Baru</h1>
        <p class="text-sm text-gray-500 mt-1">Jelaskan masalah atau pertanyaan Anda</p>
    </div>

    <form action="{{ route('portal.tickets.store') }}" method="POST" class="mt-8 space-y-5 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Subjek <span class="text-red-500">*</span></label>
            <input type="text" name="subject" value="{{ old('subject') }}" required maxlength="500"
                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition"
                placeholder="Masukkan subjek tiket">
            @error('subject')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kategori</label>
            <select name="category_id" class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Prioritas <span class="text-red-500">*</span></label>
            <select name="priority" required class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition">
                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Rendah</option>
                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Sedang</option>
                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Tinggi</option>
                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
            </select>
            @error('priority')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi <span class="text-red-500">*</span></label>
            <textarea name="description" required rows="6"
                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400 transition"
                placeholder="Jelaskan masalah atau pertanyaan Anda secara detail...">{{ old('description') }}</textarea>
            @error('description')
            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <button type="submit"
                class="w-full bg-indigo-600 text-white font-semibold py-3 px-6 rounded-xl hover:bg-indigo-700 transition shadow-sm">
                Kirim Tiket
            </button>
        </div>
    </form>
</div>
@endsection
