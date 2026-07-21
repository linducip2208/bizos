@extends('client-portal.layout')

@section('title', 'Profil')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Profil</h1></div>
    @if(session('success'))<div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('success') }}</div>@endif
    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
        <form method="POST">
            @csrf
            <div class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Nama</label><input type="text" name="name" value="{{ old('name', $clientUser->name) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Email</label><p class="text-sm text-gray-500 bg-gray-50 px-4 py-2.5 border border-gray-200 rounded-lg">{{ $clientUser->email }}</p></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label><input type="text" name="phone" value="{{ old('phone', $clientUser->phone) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></div>
                <hr class="my-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Password Baru (kosongkan jika tidak diubah)</label><input type="password" name="password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label><input type="password" name="password_confirmation" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></div>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition cursor-pointer">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
