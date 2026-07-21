@extends('portal.layout')

@section('title', 'Lupa Password')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-indigo-50 text-indigo-600 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Lupa Password</h1>
                <p class="text-sm text-gray-500 mt-1">Masukkan email Anda untuk menerima link reset password</p>
            </div>

            @if (session('status'))
                <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200">
                    <p class="text-sm text-emerald-700">{{ session('status') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                    <p class="text-sm text-red-700">{{ $errors->first() }}</p>
                </div>
            @endif

            <form action="{{ route('portal.password.email') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                            placeholder="nama@perusahaan.com">
                    </div>
                    <div>
                        <button type="submit"
                            class="w-full px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition cursor-pointer">
                            Kirim Link Reset
                        </button>
                    </div>
                </div>
            </form>

            <p class="mt-4 text-center text-sm text-gray-500">
                <a href="{{ route('portal.login') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Kembali ke Login</a>
            </p>
        </div>
    </div>
</div>
@endsection
