@extends('client-portal.layout')

@section('title', 'Masuk Klien')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-md"><div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <div class="text-center mb-8"><div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-blue-50 text-blue-600 mb-4"><svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div><h1 class="text-2xl font-bold text-gray-900">Portal Klien</h1><p class="text-sm text-gray-500 mt-1">Masuk untuk melihat invoice, deal, dan tiket</p></div>
        @if (session('status'))<div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ $errors->first() }}</div>@endif
        <form action="{{ route('client.login') }}" method="POST">@csrf
            <div class="space-y-4"><div><label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition" placeholder="nama@perusahaan.com"></div>
            <div><label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label><input type="password" name="password" id="password" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"></div>
            <button type="submit" class="w-full px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition cursor-pointer">Masuk</button></div>
        </form>
        <p class="mt-4 text-center text-xs text-gray-400">Belum punya akun? <a href="{{ route('client.register') }}" class="text-blue-600 hover:text-blue-800 font-medium">Daftar</a></p>
    </div></div>
</div>
@endsection
