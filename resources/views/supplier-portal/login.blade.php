@extends('supplier-portal.layout')

@section('title', 'Masuk Supplier')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-teal-50 text-teal-600 mb-4"><svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg></div>
                <h1 class="text-2xl font-bold text-gray-900">Portal Supplier</h1>
                <p class="text-sm text-gray-500 mt-1">Masuk dengan kode perusahaan dan akun Anda</p>
            </div>
            @if (session('status'))<div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">{{ session('status') }}</div>@endif
            @if ($errors->any())<div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">{{ $errors->first() }}</div>@endif
            <form action="{{ route('supplier.login') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div><label for="company_code" class="block text-sm font-medium text-gray-700 mb-1">Kode Perusahaan</label><input type="text" name="company_code" id="company_code" value="{{ old('company_code') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition" placeholder="Kode supplier Anda"></div>
                    <div><label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label><input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition" placeholder="nama@perusahaan.com"></div>
                    <div><label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label><input type="password" name="password" id="password" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none transition" placeholder="Password Anda"></div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-teal-600 text-white text-sm font-semibold rounded-lg hover:bg-teal-700 transition cursor-pointer">Masuk</button>
                </div>
            </form>
            <p class="mt-4 text-center text-xs text-gray-400">Belum punya akun? Hubungi perusahaan untuk mendapatkan undangan.</p>
        </div>
    </div>
</div>
@endsection
