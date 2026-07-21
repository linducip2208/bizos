@extends('pseo._layout')

@section('content')
<header class="border-b border-slate-200 bg-white/80 backdrop-blur-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 font-bold text-slate-800 text-lg no-underline">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-7 h-7 text-indigo-600"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <span>BizOS</span>
            </a>
            <a href="{{ url('/docs') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 no-underline px-4 py-2 rounded-lg hover:bg-indigo-50 transition-colors">
                Dokumentasi
            </a>
        </div>
    </div>
</header>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-10">
        <p class="text-indigo-600 font-semibold text-sm uppercase tracking-wider mb-2">Perbandingan</p>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight mb-4">BizOS vs Excel/Spreadsheet</h1>
        <p class="text-lg text-slate-600 leading-relaxed">Mengapa bisnis modern tidak bisa hanya mengandalkan spreadsheet? Bandingkan BizOS dengan Excel di 10 kategori kritis: integrasi, akurasi, keamanan, kolaborasi, mobile, skalabilitas, pajak, laporan, approval, dan biaya total.</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-10">
        <div class="bg-indigo-600 text-white rounded-xl p-5 text-center">
            <div class="text-3xl font-extrabold mb-1">10/10</div>
            <div class="text-xs text-indigo-200">Kategori dimenangkan</div>
        </div>
        <div class="bg-green-50 text-green-800 rounded-xl p-5 text-center border border-green-200">
            <div class="text-3xl font-extrabold mb-1">100%</div>
            <div class="text-xs text-green-600">Integrasi data</div>
        </div>
        <div class="bg-amber-50 text-amber-800 rounded-xl p-5 text-center border border-amber-200">
            <div class="text-3xl font-extrabold mb-1">0</div>
            <div class="text-xs text-amber-600">Human error risiko</div>
        </div>
        <div class="bg-purple-50 text-purple-800 rounded-xl p-5 text-center border border-purple-200">
            <div class="text-3xl font-extrabold mb-1">24/7</div>
            <div class="text-xs text-purple-600">Akses real-time</div>
        </div>
    </div>

    {{-- Comparison Table --}}
    <div class="space-y-6 mb-12">
        @foreach($comparisons as $c)
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">{{ $loop->iteration }}. {{ $c['category'] }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <span class="font-bold text-green-800 text-sm">BizOS</span>
                    </div>
                    <p class="text-sm text-green-700 leading-relaxed">{{ $c['bizos'] }}</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </div>
                        <span class="font-bold text-red-700 text-sm">Excel / Spreadsheet</span>
                    </div>
                    <p class="text-sm text-red-700 leading-relaxed">{{ $c['spreadsheet'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Verdict --}}
    <div class="p-8 bg-gradient-to-br from-indigo-600 via-purple-600 to-violet-700 rounded-2xl text-white shadow-xl">
        <h2 class="text-2xl font-extrabold mb-4">Verdict: BizOS Menang di Semua Kategori</h2>
        <p class="text-indigo-100 leading-relaxed mb-4">
            Spreadsheet adalah alat yang hebat — untuk kalkulasi sederhana dan analisis ad-hoc. Tapi untuk menjalankan <strong>operasional bisnis sehari-hari</strong>, spreadsheet memiliki keterbatasan fundamental: tidak ada integrasi otomatis, tidak ada keamanan data, tidak ada workflow approval, tidak ada real-time collaboration, dan sangat rawan human error.
        </p>
        <p class="text-indigo-100 leading-relaxed mb-6">
            BizOS mengatasi semua kelemahan itu. Dengan <strong>satu platform terintegrasi</strong> untuk HRM, Accounting, CRM, Project, POS, LMS, dan AI Assistant — data Anda selalu akurat, aman, dan siap untuk keputusan bisnis yang lebih baik.
        </p>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ url('/docs') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-indigo-700 font-bold rounded-xl hover:bg-indigo-50 transition-colors no-underline text-sm">
                Lihat Dokumentasi
            </a>
            <a href="{{ url('/admin/login') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 border-2 border-white/30 text-white font-bold rounded-xl hover:bg-white/10 transition-colors no-underline text-sm">
                Coba Demo Gratis
            </a>
        </div>
    </div>

    {{-- FAQ --}}
    <div class="mt-12 space-y-4">
        <h2 class="text-2xl font-extrabold text-slate-900 mb-4">Pertanyaan Umum</h2>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-bold text-slate-800 mb-2">Apakah BizOS bisa menggantikan Excel sepenuhnya?</h3>
            <p class="text-sm text-slate-600 leading-relaxed">BizOS menggantikan spreadsheet untuk manajemen data bisnis operasional (HR, keuangan, proyek, penjualan). Excel masih bisa digunakan untuk analisis data lanjutan dengan fitur export data dari BizOS.</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-bold text-slate-800 mb-2">Bagaimana dengan data yang sudah ada di Excel?</h3>
            <p class="text-sm text-slate-600 leading-relaxed">BizOS menyediakan fitur import CSV & Excel untuk migrasi data dari spreadsheet yang sudah ada. Proses import dilengkapi validasi dan error report.</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-bold text-slate-800 mb-2">Apakah tim butuh training khusus?</h3>
            <p class="text-sm text-slate-600 leading-relaxed">Tidak. BizOS dirancang dengan UI yang intuitif dan dokumentasi lengkap (35+ langkah tutorial). Rata-rata pengguna bisa produktif dalam 1-2 hari. Tersedia juga LMS internal untuk onboarding karyawan baru.</p>
        </div>
    </div>
</main>

@include('pseo._footer')
@endsection
