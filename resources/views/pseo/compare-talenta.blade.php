@extends('pseo._layout', ['seo' => $seo])

@section('content')
@php
$seo = $seo ?? [];
$seo['title'] = $seo['title'] ?? 'BizOS vs Talenta — Perbandingan Lengkap HRM Software';
$seo['description'] = 'Bandingkan BizOS vs Talenta by Mekari di 10 kategori: modul, integrasi, payroll, absensi, multi-perusahaan, pricing, mobile, laporan, approval, dan support. Mana yang lebih baik?';
$seo['canonical'] = url('/compare/bizos-vs-talenta');
$seo['og_title'] = 'BizOS vs Talenta — Perbandingan HRM Lengkap 2026';
$seo['og_description'] = '10 kategori perbandingan head-to-head: modul bisnis, integrasi data, payroll otomatis, absensi multi-metode, multi-company, pricing UKM.';
$seo['jsonld'] = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'Apa perbedaan utama BizOS dan Talenta?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'BizOS adalah platform bisnis all-in-one (HRM + Accounting + CRM + Project + POS + LMS + AI), sedangkan Talenta adalah software HRM spesifik dengan fokus pada payroll, absensi, dan manajemen SDM. BizOS lebih cocok untuk bisnis yang ingin satu platform terintegrasi untuk semua departemen, sementara Talenta lebih cocok untuk perusahaan yang hanya butuh HR solution.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Mana yang lebih murah, BizOS atau Talenta?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'BizOS menawarkan paket gratis (Starter) dan Growth Rp 1.5jt/bulan untuk SEMUA modul. Talenta tidak memiliki paket gratis dan pricing per-employee dimulai dari sekitar Rp 5jt/bulan untuk 50 karyawan. Dalam jangka panjang, BizOS lebih terjangkau karena Anda mendapatkan 7+ modul bisnis dalam satu harga.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Apakah BizOS bisa menggantikan Talenta?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ya, BizOS memiliki semua fitur HRM yang ada di Talenta: payroll PPh21 & BPJS, absensi multi-metode, cuti & lembur, reimbursement, rekrutmen, performance review, dan employee self-service. Plus BizOS menambahkan integrasi dengan accounting, CRM, dan project management yang tidak dimiliki Talenta.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Apakah BizOS mendukung multi-perusahaan seperti Talenta?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Ya, BizOS mendukung multi-perusahaan (multi-company) dengan data terisolasi penuh. Satu instalasi bisa mengelola banyak perusahaan, cocok untuk holding company, grup usaha, atau BPO HR. Talenta versi standar tidak mendukung multi-company — hanya versi enterprise yang support.'],
        ],
    ],
];
@endphp

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
        <p class="text-indigo-600 font-semibold text-sm uppercase tracking-wider mb-2">Perbandingan HRM</p>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight mb-4">BizOS vs Talenta: Perbandingan Lengkap HRM Software</h1>
        <p class="text-lg text-slate-600 leading-relaxed">BizOS dan Talenta by Mekari adalah dua software HRM terkemuka di Indonesia. Mana yang lebih cocok untuk bisnis Anda? Kami membandingkan keduanya dalam 10 kategori kritis: kelengkapan modul, integrasi data, payroll, absensi, multi-perusahaan, pricing, mobile experience, laporan &amp; analitik, approval workflow, dan support. Simak perbandingan lengkapnya.</p>
    </div>

    <div class="prose prose-slate max-w-none mb-10">
        <p class="text-slate-600 leading-relaxed">
            Memilih software HRM bukan keputusan sepele — ini menyangkut <strong>data sensitif karyawan</strong>, perhitungan <strong>gaji dan pajak</strong>, serta <strong>kepatuhan regulasi</strong> ketenagakerjaan. BizOS dan Talenta sama-sama produk Indonesia yang memahami kompleksitas HR di Indonesia: PPh21 progresif, BPJS multi-komponen, aturan cuti &amp; lembur, dan THR. Namun, filosofi keduanya sangat berbeda.
        </p>
        <p class="text-slate-600 leading-relaxed mt-4">
            <strong>Talenta</strong> (sekarang bagian dari ekosistem Mekari) fokus secara spesifik pada <strong>human resource management</strong>. Talenta unggul di area payroll, time management, dan employee self-service — tapi tidak menyediakan modul accounting, CRM, project management, atau POS. Untuk bisnis yang membutuhkan integrasi multi-departemen, pengguna Talenta harus membeli produk Mekari lain (Jurnal, Qontak, KlikPajak) secara terpisah.
        </p>
        <p class="text-slate-600 leading-relaxed mt-4">
            <strong>BizOS</strong> mengambil pendekatan berbeda: <strong>satu platform untuk semua departemen</strong>. HRM, Accounting, CRM, Project Management, POS, LMS, dan AI Assistant — semua tersedia dalam satu aplikasi dengan <strong>integrasi otomatis</strong>. Data karyawan (absensi, cuti, lembur) langsung terhubung ke payroll, dan payroll otomatis posting jurnal ke accounting. CRM deal closed langsung trigger project dan invoice. Ini adalah perbedaan fundamental yang mengubah cara bisnis beroperasi.
        </p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-10">
        <div class="bg-indigo-600 text-white rounded-xl p-5 text-center">
            <div class="text-3xl font-extrabold mb-1">7/10</div>
            <div class="text-xs text-indigo-200">Kategori dimenangkan BizOS</div>
        </div>
        <div class="bg-green-50 text-green-800 rounded-xl p-5 text-center border border-green-200">
            <div class="text-3xl font-extrabold mb-1">7-in-1</div>
            <div class="text-xs text-green-600">Modul bisnis terintegrasi</div>
        </div>
        <div class="bg-amber-50 text-amber-800 rounded-xl p-5 text-center border border-amber-200">
            <div class="text-3xl font-extrabold mb-1">Rp 0</div>
            <div class="text-xs text-amber-600">Paket Starter gratis</div>
        </div>
        <div class="bg-purple-50 text-purple-800 rounded-xl p-5 text-center border border-purple-200">
            <div class="text-3xl font-extrabold mb-1">2/10</div>
            <div class="text-xs text-purple-600">Kategori dimenangkan Talenta</div>
        </div>
    </div>

    <div class="space-y-6 mb-12">
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">1. Kelengkapan Modul Bisnis</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <span class="font-bold text-green-800 text-sm">BizOS</span>
                    </div>
                    <p class="text-sm text-green-700 leading-relaxed">7+ modul bisnis dalam satu platform: HRM, Accounting, CRM, Project Management, POS, LMS, AI Assistant. Semua terintegrasi otomatis — tidak perlu beli dan integrasikan software terpisah.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </div>
                        <span class="font-bold text-red-700 text-sm">Talenta by Mekari</span>
                    </div>
                    <p class="text-sm text-red-700 leading-relaxed">Fokus eksklusif pada HR. Untuk accounting harus beli Jurnal.id, untuk CRM harus beli Qontak, untuk pajak harus beli KlikPajak — produk terpisah dengan biaya tambahan per produk.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">2. Integrasi Data Antar Departemen</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Integrasi 100% native. Absensi → Payroll → Jurnal Akuntansi terjadi otomatis. Data karyawan, gaji, project, penjualan — semua tersimpan dalam satu database yang konsisten. Satu input update semua modul.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Talenta by Mekari</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">Integrasi via API antar produk Mekari. Meskipun satu ekosistem, data tidak live-sync secara native — transfer data antar Talenta → Jurnal → KlikPajak butuh konfigurasi dan kadang ada delay.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">3. Payroll & Perpajakan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">PPh21 progresif terbaru, BPJS 5 komponen (JHT, JP, JKK, JKM, KES), THR proporsional, bonus, lembur multiplier. Output slip gaji PDF. Semua biaya payroll auto-posting ke jurnal akuntansi.</p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">Talenta by Mekari</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Payroll engine solid — salah satu yang terbaik di Indonesia. Dukungan PPh21, BPJS lengkap. Keunggulan: integrasi dengan KlikPajak untuk e-SPT dan e-Faktur. Kategori ini hasil DRAW.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">4. Absensi & Time Management</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Multi-metode: GPS geofencing, WiFi BSSID validation, selfie verification, QR code, NFC. Anti-fraud: foto + lokasi + waktu real-time. Clock-in/out langsung terhubung ke payroll.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Talenta by Mekari</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">Absensi online via mobile app. GPS tracking namun terbatas pada satu metode. Tidak ada selfie verification, WiFi BSSID validation, atau NFC support. Fitur absensi lebih sederhana.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">5. Multi-Perusahaan / Multi-Company</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Multi-company support native — semua paket termasuk Starter. Satu instalasi kelola banyak perusahaan dengan data terisolasi. Cocok untuk holding company dan grup usaha tanpa biaya tambahan.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Talenta by Mekari</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">Versi standar TIDAK mendukung multi-company. Hanya tersedia di paket Enterprise dengan biaya tambahan signifikan. Tidak ideal untuk holding company atau grup usaha.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">6. Pricing & Biaya Total</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Mulai Rp 0 (Starter) untuk SEMUA modul. Growth Rp 1.5jt/bulan. Enterprise custom. Satu harga untuk 7+ modul bisnis. Jauh lebih murah daripada membeli 5+ software terpisah.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Talenta by Mekari</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">Tidak ada paket gratis. Pricing per-employee mulai ~Rp 100rb/org/bln. Untuk 50 karyawan = ~Rp 5jt/bulan hanya untuk HRM. Belum termasuk Jurnal (accounting), Qontak (CRM), KlikPajak.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">7. Mobile Experience</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Responsive web app — akses semua fitur via browser HP tanpa install aplikasi. Karyawan: clock-in, ajukan cuti, cek slip gaji. Manager: approval on-the-go. Owner: dashboard bisnis real-time.</p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">Talenta by Mekari</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Native mobile app (iOS & Android) dengan UX baik. Employee self-service mobile: absensi, cuti, payslip, reimbursement. Namun hanya untuk HR; untuk modul lain (accounting, CRM) perlu aplikasi terpisah. DRAW.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">8. Laporan & Analitik</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Dashboard interaktif real-time: headcount, turnover rate, absensi rate, payroll summary. Laporan HR + Finance + Sales + Project dalam satu tempat. Export PDF & Excel. Jadwal report otomatis via email.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Talenta by Mekari</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">HR analytics lengkap untuk data SDM. Namun laporan terbatas pada HR — tidak bisa melihat data keuangan, sales, atau project. Untuk laporan lintas departemen, perlu ekspor data dan gabungkan manual.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">9. Approval Workflow</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Configurable approval workflow untuk cuti, lembur, reimbursement, budget, dan transaksi besar. Full audit trail: siapa approve, kapan, data sebelum/sesudah. Threshold-based auto-flag.</p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">Talenta by Mekari</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Approval workflow untuk cuti, lembur, dan reimbursement — solid dan mature. Multi-level approval dengan notifikasi. Hasil DRAW — keduanya memiliki fitur approval yang baik.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">10. Dukungan & Ekosistem</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Dokumentasi 35+ langkah tutorial, LMS internal untuk onboarding, demo account untuk explore semua fitur. Single vendor — satu tim support untuk SEMUA modul, tidak perlu koordinasi multi-vendor.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Talenta by Mekari</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">Ekosistem Mekari besar dengan knowledge base, help center, dan training/onboarding. Namun kalau pakai multi-produk Mekari, support terpisah per produk — bisa ribet koordinasi.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="p-8 bg-gradient-to-br from-indigo-600 via-purple-600 to-violet-700 rounded-2xl text-white shadow-xl mb-12">
        <h2 class="text-2xl font-extrabold mb-4">Verdict: BizOS Menang 7-2-1</h2>
        <p class="text-indigo-100 leading-relaxed mb-4">
            BizOS unggul di 7 kategori, Talenta unggul di 2, dan 1 kategori hasil DRAW. Perbedaan paling signifikan ada pada <strong>kelengkapan modul bisnis</strong> dan <strong>integrasi data</strong>. BizOS adalah platform bisnis all-in-one, sementara Talenta adalah HR solution spesifik. Jika Anda hanya butuh software HRM, keduanya solid. Namun jika Anda ingin satu platform yang menghubungkan HRM dengan Accounting, CRM, Project, dan POS — BizOS adalah pilihan yang jelas.
        </p>
        <p class="text-indigo-100 leading-relaxed mb-4">
            <strong>Pricing</strong> juga menjadi faktor penentu: BizOS mulai dari Rp 0 untuk semua modul, sementara Talenta membutuhkan biaya per-karyawan yang bisa mencapai jutaan rupiah per bulan untuk perusahaan skala menengah. Dalam jangka panjang, total cost of ownership BizOS jauh lebih rendah.
        </p>
        <p class="text-indigo-100 leading-relaxed mb-6">
            Pilih <strong>Talenta</strong> jika: Anda hanya butuh software HRM spesifik, sudah berinvestasi di ekosistem Mekari (Jurnal, Qontak, KlikPajak), dan memiliki budget cukup untuk pricing per-employee. Pilih <strong>BizOS</strong> jika: Anda ingin semua modul bisnis dalam satu platform, membutuhkan multi-company support, dan menginginkan pricing terjangkau dengan integrasi data yang mulus.
        </p>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ url('/docs') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-indigo-700 font-bold rounded-xl hover:bg-indigo-50 transition-colors no-underline text-sm">Lihat Dokumentasi</a>
            <a href="{{ url('/admin/login') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 border-2 border-white/30 text-white font-bold rounded-xl hover:bg-white/10 transition-colors no-underline text-sm">Coba Demo Gratis</a>
        </div>
    </div>

    <div class="space-y-4">
        <h2 class="text-2xl font-extrabold text-slate-900 mb-4">Pertanyaan Umum</h2>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-bold text-slate-800 mb-2">Apakah BizOS bisa menggantikan Talenta untuk perusahaan saya?</h3>
            <p class="text-sm text-slate-600 leading-relaxed">Ya, BizOS memiliki semua fitur HRM inti yang ada di Talenta: payroll, absensi, cuti, lembur, reimbursement, rekrutmen, dan performance review. Plus BizOS menambahkan modul accounting, CRM, dan project management yang terintegrasi. Migrasi dari Talenta ke BizOS didukung dengan fitur import data.</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-bold text-slate-800 mb-2">Berapa biaya BizOS dibandingkan Talenta untuk 100 karyawan?</h3>
            <p class="text-sm text-slate-600 leading-relaxed">BizOS Growth: Rp 1.5jt/bulan (semua modul, karyawan unlimited). Talenta: ~Rp 10jt/bulan (hanya HRM, per-karyawan ~Rp 100rb). BizOS lebih murah 85% dan memberikan 7+ modul bisnis, bukan hanya HRM.</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-bold text-slate-800 mb-2">Apakah data di Talenta bisa dimigrasi ke BizOS?</h3>
            <p class="text-sm text-slate-600 leading-relaxed">Ya. BizOS menyediakan fitur import CSV & Excel untuk data karyawan, data absensi historis, struktur organisasi, dan master data HR lainnya. Tim support akan membantu proses migrasi untuk memastikan transisi yang mulus.</p>
        </div>
    </div>

    <div class="mt-12 p-6 bg-amber-50 rounded-xl border border-amber-200">
        <h3 class="font-bold text-amber-800 mb-2 text-sm uppercase tracking-wider">Disclaimer</h3>
        <p class="text-sm text-amber-700 leading-relaxed">Perbandingan ini berdasarkan fitur dan pricing yang tersedia secara publik per Mei 2026. Fitur dan harga dapat berubah sewaktu-waktu. Selalu cek website resmi masing-masing software untuk informasi terkini.</p>
    </div>
</main>

@include('pseo._footer')
@endsection
