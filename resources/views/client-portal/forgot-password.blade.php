@extends('client-portal.layout')

@section('title', 'Lupa Password')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center"><div class="w-full max-w-md"><div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center"><h1 class="text-2xl font-bold text-gray-900 mb-4">Lupa Password</h1><p class="text-sm text-gray-500">Hubungi perusahaan atau administrator Anda untuk mereset password.</p><a href="{{ route('client.login') }}" class="inline-block mt-4 text-sm text-blue-600 hover:text-blue-800 font-medium">Kembali ke Login</a></div></div></div>
@endsection
