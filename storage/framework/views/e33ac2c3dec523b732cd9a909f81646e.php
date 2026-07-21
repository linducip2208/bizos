<?php $__env->startSection('content'); ?>
<?php
$seo = $seo ?? [];
$seo['title'] = $seo['title'] ?? 'BizOS vs Jurnal.id — Perbandingan Accounting Software';
$seo['description'] = 'Bandingkan BizOS vs Jurnal.id di 10 kategori: kelengkapan modul, integrasi, double-entry, perpajakan, invoice, budget, multi-perusahaan, pricing, laporan, dan support.';
$seo['canonical'] = url('/compare/bizos-vs-jurnal');
$seo['og_title'] = 'BizOS vs Jurnal.id — Perbandingan Accounting 2026';
$seo['og_description'] = '10 kategori head-to-head: modul bisnis, integrasi penuh, perpajakan PPN/PPh, multi-company, pricing UKM. Mana yang lebih baik?';
$seo['jsonld'] = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'Apa perbedaan utama BizOS dan Jurnal.id?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'BizOS adalah platform bisnis all-in-one (Accounting + HRM + CRM + Project + POS + LMS + AI), sedangkan Jurnal.id adalah software akuntansi spesifik dari ekosistem Mekari. BizOS menawarkan integrasi native dengan modul bisnis lain, sementara Jurnal.id hanya fokus pada akuntansi dan perlu produk Mekari lain untuk CRM, HR, atau POS.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Mana yang lebih cocok untuk UKM, BizOS atau Jurnal.id?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'BizOS lebih cocok untuk UKM yang ingin satu platform untuk semua kebutuhan bisnis (akuntansi + HR + CRM + project) dengan harga mulai Rp 0. Jurnal.id lebih cocok untuk UKM yang hanya butuh software akuntansi dan sudah menggunakan produk Mekari lain.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Apakah BizOS mendukung perpajakan selengkap Jurnal.id?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'BizOS mendukung PPN 11%, PPh21, PPh22, PPh23, PPh25, dan PPh Final dengan tax transaction tracking. Jurnal.id terintegrasi langsung dengan KlikPajak untuk e-Faktur dan e-SPT — ini adalah keunggulan Jurnal.id di area perpajakan. Namun untuk perhitungan pajak internal dan tracking, BizOS setara.'],
        ],
        [
            '@type' => 'Question',
            'name' => 'Berapa biaya BizOS dibandingkan Jurnal.id?',
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'BizOS: mulai Rp 0 (Starter) hingga Rp 1.5jt/bulan (Growth). Jurnal.id: mulai ~Rp 200rb/bulan untuk paket dasar. Namun BizOS memberikan 7+ modul bisnis dalam satu harga, sementara Jurnal.id hanya akuntansi — untuk HRM, CRM, atau POS Anda perlu membeli produk Mekari lain dengan biaya tambahan.'],
        ],
    ],
];
?>

<header class="border-b border-slate-200 bg-white/80 backdrop-blur-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-2.5 font-bold text-slate-800 text-lg no-underline">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-7 h-7 text-indigo-600"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <span>BizOS</span>
            </a>
            <a href="<?php echo e(url('/docs')); ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 no-underline px-4 py-2 rounded-lg hover:bg-indigo-50 transition-colors">
                Dokumentasi
            </a>
        </div>
    </div>
</header>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-10">
        <p class="text-indigo-600 font-semibold text-sm uppercase tracking-wider mb-2">Perbandingan Accounting</p>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight mb-4">BizOS vs Jurnal.id: Perbandingan Accounting Software</h1>
        <p class="text-lg text-slate-600 leading-relaxed">BizOS dan Jurnal.id adalah dua software akuntansi terkemuka di Indonesia. Mana yang lebih baik untuk bisnis Anda? Kami membandingkan 10 kategori: kelengkapan modul bisnis, integrasi data, double-entry accounting, perpajakan, invoice &amp; pembayaran, budget &amp; variance, multi-perusahaan, pricing, laporan keuangan, dan dukungan pengguna.</p>
    </div>

    <div class="prose prose-slate max-w-none mb-10">
        <p class="text-slate-600 leading-relaxed">
            Software akuntansi adalah investasi jangka panjang — data keuangan Anda akan tersimpan di dalamnya selama bertahun-tahun. Memilih platform yang tepat sejak awal sangat kritis. BizOS dan Jurnal.id sama-sama menyediakan <strong>double-entry accounting sesuai standar PSAK Indonesia</strong>, namun pendekatan keduanya sangat berbeda.
        </p>
        <p class="text-slate-600 leading-relaxed mt-4">
            <strong>Jurnal.id</strong> adalah produk unggulan Mekari untuk akuntansi. Jurnal.id fokus pada pembukuan digital, invoice, rekonsiliasi bank, dan integrasi dengan KlikPajak untuk e-Faktur dan e-SPT. Keunggulan Jurnal.id terletak pada <strong>ekosistem perpajakan Mekari</strong> — Jurnal.id + KlikPajak adalah combo kuat untuk compliance perpajakan. Namun Jurnal.id tidak memiliki modul HRM, CRM, Project Management, atau POS — Anda harus membeli Talenta, Qontak, atau produk Mekari lain secara terpisah.
        </p>
        <p class="text-slate-600 leading-relaxed mt-4">
            <strong>BizOS</strong> mengambil pendekatan <strong>unified platform</strong>: Accounting adalah salah satu dari 7+ modul yang terintegrasi native. Transaksi dari POS, invoice dari CRM, penggajian dari Payroll — semuanya <strong>auto-posting ke jurnal akuntansi</strong> tanpa input manual. BizOS tidak terpisah antara accounting dan operasional — semua data keuangan tercatat otomatis dari aktivitas bisnis sehari-hari.
        </p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-10">
        <div class="bg-indigo-600 text-white rounded-xl p-5 text-center">
            <div class="text-3xl font-extrabold mb-1">6/10</div>
            <div class="text-xs text-indigo-200">Kategori dimenangkan BizOS</div>
        </div>
        <div class="bg-green-50 text-green-800 rounded-xl p-5 text-center border border-green-200">
            <div class="text-3xl font-extrabold mb-1">100%</div>
            <div class="text-xs text-green-600">Integrasi data bisnis</div>
        </div>
        <div class="bg-amber-50 text-amber-800 rounded-xl p-5 text-center border border-amber-200">
            <div class="text-3xl font-extrabold mb-1">2/10</div>
            <div class="text-xs text-amber-600">Kategori dimenangkan Jurnal</div>
        </div>
        <div class="bg-purple-50 text-purple-800 rounded-xl p-5 text-center border border-purple-200">
            <div class="text-3xl font-extrabold mb-1">2</div>
            <div class="text-xs text-purple-600">Kategori DRAW</div>
        </div>
    </div>

    <div class="space-y-6 mb-12">
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">1. Kelengkapan Modul Bisnis</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Platform bisnis all-in-one: Accounting + HRM + CRM + Project + POS + LMS + AI Assistant. Semua transaksi bisnis auto-posting ke jurnal. Tidak perlu membeli dan mengintegrasikan software terpisah.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Jurnal.id</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">Fokus eksklusif pada akuntansi. Untuk payroll perlu Talenta, untuk CRM perlu Qontak, untuk POS perlu produk lain — semua produk terpisah dengan biaya masing-masing dan integrasi via API.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">2. Integrasi Data Bisnis</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Native integration 100%. POS → jurnal penjualan otomatis. Payroll → jurnal beban gaji otomatis. CRM invoice → jurnal piutang otomatis. Project expense → jurnal biaya otomatis. Semua dalam satu database.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Jurnal.id</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">Integrasi via API dengan produk Mekari lain (Talenta, Qontak). Tidak native — data tidak real-time sync. Perlu setup dan konfigurasi tambahan untuk menghubungkan akuntansi dengan operasional.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">3. Double-Entry Accounting</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">COA hierarkis sesuai PSAK. Jurnal otomatis dari setiap transaksi bisnis. General Ledger, Trial Balance, Income Statement, Balance Sheet, Cash Flow. Semua laporan keuangan standar.</p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">Jurnal.id</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Double-entry solid — salah satu yang terbaik di Indonesia. COA fleksibel, jurnal manual & otomatis, laporan keuangan lengkap. Rekonsiliasi bank otomatis. Kategori DRAW.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">4. Perpajakan (PPN & PPh)</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">BizOS</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">PPN 11%, PPh21, PPh22, PPh23, PPh25, PPh Final — semua dikonfigurasi dan ditracking. Namun belum ada integrasi langsung dengan DJP untuk e-Faktur atau e-SPT. Anda masih perlu export dan upload manual.</p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">Jurnal.id</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Keunggulan kompetitif: integrasi native dengan <strong>KlikPajak</strong> untuk e-Faktur, e-SPT, dan e-Bupot. Pajak langsung terhubung ke DJP. Ini adalah fitur unggulan Jurnal.id yang belum ada di BizOS.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">5. Invoice & Pembayaran</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Invoice sales & purchase dengan PPN, multi-item, diskon. Status tracking: draft → sent → partial → paid → overdue. CRM deal auto-generate invoice. Multi-metode pembayaran: cash, transfer, QRIS. Invoice dari CRM langsung tercatat di piutang.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Jurnal.id</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">Invoice maker dengan template kustom. Namun invoice tidak terintegrasi dengan CRM atau sales pipeline — input manual dari deal yang sudah closed.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">6. Budget & Variance Analysis</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Budget per departemen, proyek, atau COA. Real-time tracking actual vs planned. Variance analysis untuk kontrol keuangan. Terintegrasi dengan Project Management — biaya proyek otomatis tercatat di budget.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Jurnal.id</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">Budget tracking tersedia namun lebih terbatas. Tidak terintegrasi dengan project management. Budget module basic — tidak ada otomatisasi tracking biaya dari project.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">7. Multi-Perusahaan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Multi-company native. Satu instalasi kelola akuntansi banyak perusahaan. Data terisolasi. Konsolidasi laporan keuangan grup usaha dengan eliminasi intra-grup. Tanpa biaya tambahan.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Jurnal.id</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">Satu akun untuk satu perusahaan. Untuk multi-company harus buat akun terpisah dan bayar subscription terpisah per perusahaan. Tidak ada fitur konsolidasi laporan grup.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">8. Pricing & Biaya Total</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Mulai Rp 0 (Starter) untuk SEMUA modul termasuk accounting. Growth Rp 1.5jt/bulan. Satu harga untuk Accounting + HRM + CRM + Project + POS + LMS — value jauh lebih besar.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Jurnal.id</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">Mulai ~Rp 200rb/bulan hanya untuk accounting. Untuk HRM (Talenta), CRM (Qontak), pajak (KlikPajak) — masing-masing biaya tambahan. Total biaya untuk 3-4 produk Mekari bisa mencapai Rp 10-15jt/bulan.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">9. Laporan Keuangan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Laporan standar lengkap: GL, Trial Balance, Income Statement, Balance Sheet, Cash Flow, AR/AP Aging. Dashboard chart interaktif. Export PDF & Excel. Terintegrasi dengan data HR & Sales — bisa drill-down dari laporan ke transaksi.</p>
                </div>
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">Jurnal.id</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Laporan keuangan solid dan mature. Format profesional. Keunggulan: laporan pajak terintegrasi dengan KlikPajak, siap untuk SPT. Hasil DRAW — keduanya memiliki laporan keuangan yang baik.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 mb-4">10. Dukungan & Ekosistem</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg></div><span class="font-bold text-green-800 text-sm">BizOS</span></div>
                    <p class="text-sm text-green-700 leading-relaxed">Dokumentasi 35+ langkah tutorial, demo account, LMS internal. Single vendor support untuk semua modul. Satu kontak untuk semua masalah — tidak perlu koordinasi multi-vendor.</p>
                </div>
                <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center gap-2 mb-2"><div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-4 h-4"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg></div><span class="font-bold text-red-700 text-sm">Jurnal.id</span></div>
                    <p class="text-sm text-red-700 leading-relaxed">Ekosistem Mekari besar. Knowledge base & help center. Namun jika pakai multi-produk Mekari, support terpisah per produk — bisa ribet saat ada masalah lintas modul.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="p-8 bg-gradient-to-br from-indigo-600 via-purple-600 to-violet-700 rounded-2xl text-white shadow-xl mb-12">
        <h2 class="text-2xl font-extrabold mb-4">Verdict: BizOS Menang 6-2-2</h2>
        <p class="text-indigo-100 leading-relaxed mb-4">
            BizOS unggul di 6 kategori, Jurnal.id unggul di 2 (perpajakan via KlikPajak dan ekosistem pajak), dan 2 kategori hasil DRAW. <strong>Jurnal.id adalah software akuntansi yang sangat solid</strong> — terutama jika Anda sudah menggunakan ekosistem Mekari (Talenta, Qontak, KlikPajak) dan hanya membutuhkan modul akuntansi. Integrasi Jurnal.id dengan KlikPajak untuk e-Faktur dan e-SPT adalah keunggulan yang belum tersedia di BizOS.
        </p>
        <p class="text-indigo-100 leading-relaxed mb-4">
            Namun <strong>BizOS menawarkan nilai yang jauh lebih besar untuk bisnis secara keseluruhan</strong>. Dengan satu platform, Anda mendapatkan Accounting + HRM + CRM + Project + POS + LMS + AI Assistant yang semuanya terintegrasi native. Transaksi dari setiap modul auto-posting ke jurnal tanpa input manual — ini adalah efisiensi yang tidak bisa ditandingi Jurnal.id yang hanya fokus pada akuntansi.
        </p>
        <p class="text-indigo-100 leading-relaxed mb-6">
            Pilih <strong>Jurnal.id</strong> jika: akuntansi adalah satu-satunya kebutuhan Anda, Anda sudah menggunakan produk Mekari lain, dan integrasi e-Faktur/e-SPT via KlikPajak adalah fitur wajib. Pilih <strong>BizOS</strong> jika: Anda ingin platform bisnis terintegrasi, membutuhkan multi-company support, dan menginginkan value maksimal dengan biaya total yang lebih rendah.
        </p>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="<?php echo e(url('/docs')); ?>" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-indigo-700 font-bold rounded-xl hover:bg-indigo-50 transition-colors no-underline text-sm">Lihat Dokumentasi</a>
            <a href="<?php echo e(url('/admin/login')); ?>" class="inline-flex items-center justify-center gap-2 px-6 py-3 border-2 border-white/30 text-white font-bold rounded-xl hover:bg-white/10 transition-colors no-underline text-sm">Coba Demo Gratis</a>
        </div>
    </div>

    <div class="space-y-4">
        <h2 class="text-2xl font-extrabold text-slate-900 mb-4">Pertanyaan Umum</h2>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-bold text-slate-800 mb-2">Apakah BizOS bisa menggantikan Jurnal.id?</h3>
            <p class="text-sm text-slate-600 leading-relaxed">Ya, untuk sebagian besar kebutuhan akuntansi bisnis. BizOS menyediakan double-entry accounting, COA PSAK, invoice, pajak, laporan keuangan, dan AR/AP aging yang setara dengan Jurnal.id. Satu-satunya area di mana Jurnal.id unggul adalah integrasi langsung dengan KlikPajak untuk e-Faktur dan e-SPT.</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-bold text-slate-800 mb-2">Bagaimana dengan data akuntansi yang sudah ada di Jurnal.id?</h3>
            <p class="text-sm text-slate-600 leading-relaxed">BizOS menyediakan fitur import CSV & Excel. Anda bisa mengekspor COA, jurnal, dan master data dari Jurnal.id, lalu mengimpornya ke BizOS. Proses migrasi didokumentasikan dalam tutorial BizOS.</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="font-bold text-slate-800 mb-2">Apakah BizOS cocok untuk perusahaan yang sudah pakai ekosistem Mekari?</h3>
            <p class="text-sm text-slate-600 leading-relaxed">BizOS bisa menjadi alternatif jika Anda ingin mengkonsolidasi semua modul bisnis dalam satu platform. Jika Anda sudah terlanjur investasi di ekosistem Mekari dan hanya butuh akuntansi, Jurnal.id tetap pilihan solid. Namun untuk bisnis yang sedang tumbuh dan ingin efisiensi, BizOS menawarkan integrasi yang lebih mulus dengan biaya lebih rendah.</p>
        </div>
    </div>

    <div class="mt-12 p-6 bg-amber-50 rounded-xl border border-amber-200">
        <h3 class="font-bold text-amber-800 mb-2 text-sm uppercase tracking-wider">Disclaimer</h3>
        <p class="text-sm text-amber-700 leading-relaxed">Perbandingan ini berdasarkan fitur dan pricing yang tersedia secara publik per Mei 2026. Fitur dan harga dapat berubah. Selalu cek website resmi masing-masing software. Perhitungan pajak sebaiknya dikonfirmasi dengan konsultan pajak profesional.</p>
    </div>
</main>

<?php echo $__env->make('pseo._footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('pseo._layout', ['seo' => $seo], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\bizos\resources\views/pseo/compare-jurnal.blade.php ENDPATH**/ ?>