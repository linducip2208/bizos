@extends('portal.layout')

@section('title', 'Pengaturan PIN')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Pengaturan PIN Mobile</h1>
        <p class="text-sm text-gray-500 mt-1">Atur PIN untuk login cepat di aplikasi mobile</p>
    </div>

    @if (session('status'))
        <div class="p-4 rounded-lg bg-emerald-50 border border-emerald-200">
            <p class="text-sm text-emerald-700">{{ session('status') }}</p>
        </div>
    @endif

    @if ($hasPin)
        <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <p class="font-semibold text-gray-900">PIN Terpasang</p>
                    <p class="text-xs text-gray-500">PIN Anda sudah aktif untuk login mobile</p>
                </div>
            </div>
            <form action="{{ route('portal.pin.remove') }}" method="POST" onsubmit="return confirm('Hapus PIN? Anda perlu password penuh untuk login mobile.')">
                @csrf
                @method('DELETE')
                <div class="space-y-3">
                    <div>
                        <label for="password_remove" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                        <input type="password" name="password" id="password_remove" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                    <button type="submit"
                        class="px-4 py-2 bg-red-50 text-red-700 text-sm font-semibold rounded-lg border border-red-200 hover:bg-red-100 transition cursor-pointer">
                        Hapus PIN
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $hasPin ? 'Ubah PIN' : 'Buat PIN Baru' }}</h2>
        <form action="{{ route('portal.pin.store') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="pin" class="block text-sm font-medium text-gray-700 mb-1">PIN (6 digit)</label>
                    <input type="text" name="pin" id="pin" required maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm text-center tracking-[0.5em] text-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                        placeholder="000000">
                </div>
                <div>
                    <label for="pin_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi PIN</label>
                    <input type="text" name="pin_confirmation" id="pin_confirmation" required maxlength="6" pattern="[0-9]{6}" inputmode="numeric"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm text-center tracking-[0.5em] text-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                        placeholder="000000">
                </div>
                <div>
                    <label for="password_setup" class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                    <input type="password" name="password" id="password_setup" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>
                <button type="submit"
                    class="w-full px-4 py-2.5 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition cursor-pointer">
                    {{ $hasPin ? 'Perbarui PIN' : 'Aktifkan PIN' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
