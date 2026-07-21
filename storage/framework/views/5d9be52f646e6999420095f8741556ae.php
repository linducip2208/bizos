<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BizOS — Business Operating System | HRM, Accounting, CRM, POS, Project All-in-One</title>
    <meta name="description" content="BizOS adalah platform bisnis all-in-one: HRM, Akuntansi, CRM, Project Management, POS, LMS, AI Assistant — 150+ fitur dalam satu sistem. Dibuat untuk bisnis Indonesia.">
    <link rel="canonical" href="<?php echo e(url('/')); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="BizOS — Business Operating System untuk Bisnis Indonesia">
    <meta property="og:description" content="Platform bisnis all-in-one: kelola karyawan, keuangan, pelanggan, proyek, dan penjualan dalam satu sistem terintegrasi. 150+ fitur.">
    <meta property="og:url" content="<?php echo e(url('/')); ?>">
    <meta property="og:image" content="<?php echo e(url('/marketing/screens/bizos-hero.png')); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="BizOS — Business Operating System">
    <meta name="twitter:description" content="Platform bisnis all-in-one: HRM, Accounting, CRM, POS, Project, LMS, AI. 150+ fitur untuk bisnis Indonesia.">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "BizOS — Business Operating System",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "All-in-one business OS: HRM, Accounting, CRM, Project Management, POS, AI Assistant, Collaboration — 150+ fitur untuk bisnis Indonesia.",
        "offers": [
            {
                "@type": "Offer",
                "name": "Starter",
                "price": "0",
                "priceCurrency": "IDR"
            },
            {
                "@type": "Offer",
                "name": "Growth",
                "price": "1500000",
                "priceCurrency": "IDR"
            }
        ]
    }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        pre, code, .font-mono { font-family: 'JetBrains Mono', monospace; }
        html { scroll-behavior: smooth; }
        .browser-mock {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0,0,0,0.2), 0 1px 3px rgba(0,0,0,0.08);
            background: #1e1e2e;
        }
        .browser-mock-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: #1e1e2e;
        }
        .browser-mock-dot { width: 12px; height: 12px; border-radius: 50%; }
        .browser-mock-url {
            flex: 1;
            background: #2d2d3f;
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 11px;
            color: #94a3b8;
            font-family: 'JetBrains Mono', monospace;
        }
        .browser-mock-body { background: #fff; line-height: 0; }
        .browser-mock-body img { display: block; width: 100%; }
        .gradient-hero { background: linear-gradient(135deg, #312e81 0%, #4c1d95 40%, #7c3aed 70%, #6366f1 100%); }
        .glass-card { background: rgba(255,255,255,0.08); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.12); }
        .feature-card:hover { transform: translateY(-4px); transition: transform 0.2s ease; }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased">


<header class="fixed top-0 left-0 right-0 z-50 bg-white/70 backdrop-blur-xl border-b border-slate-200/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-2.5 font-bold text-slate-800 text-lg no-underline">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-7 h-7 text-indigo-600"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <span>BizOS</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="<?php echo e(url('/docs')); ?>" class="text-sm font-medium text-slate-600 hover:text-slate-900 no-underline px-3 py-2 rounded-lg hover:bg-slate-100 transition-colors hidden sm:inline-flex">Dokumentasi</a>
                <a href="<?php echo e(url('/admin/login')); ?>" class="text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg transition-colors no-underline shadow-sm">Login</a>
            </div>
        </div>
    </div>
</header>


<section class="gradient-hero pt-28 pb-20 sm:pt-36 sm:pb-28 px-4">
    <div class="max-w-4xl mx-auto text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-sm border border-white/15 text-white/80 text-xs font-medium mb-6">
            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
            Platform Bisnis All-in-One untuk Indonesia
        </div>
        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold text-white leading-tight mb-6">
            BizOS — <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 via-purple-300 to-pink-300">Business<br class="sm:hidden"> Operating System</span>
        </h1>
        <p class="text-lg sm:text-xl text-indigo-200 max-w-2xl mx-auto mb-8 leading-relaxed">
            Satu platform untuk menjalankan seluruh operasional bisnis Anda. HRM, Akuntansi, CRM, Project, POS, LMS, AI — terintegrasi otomatis. Tidak perlu 5+ software berbeda.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?php echo e(url('/admin/login')); ?>" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-indigo-700 font-bold rounded-xl hover:bg-indigo-50 transition-colors shadow-xl shadow-indigo-500/25 text-lg no-underline">
                Coba Demo Gratis
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M6 10a.75.75 0 01.75-.75h9.546l-1.048-.943a.75.75 0 111.004-1.114l2.5 2.25a.75.75 0 010 1.114l-2.5 2.25a.75.75 0 11-1.004-1.114l1.048-.943H6.75A.75.75 0 016 10z" clip-rule="evenodd"/></svg>
            </a>
            <a href="<?php echo e(url('/docs')); ?>" class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-white/30 text-white font-bold rounded-xl hover:bg-white/10 transition-colors text-lg no-underline">
                Lihat Dokumentasi
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M4.25 2A2.25 2.25 0 002 4.25v11.5A2.25 2.25 0 004.25 18h11.5A2.25 2.25 0 0018 15.75V4.25A2.25 2.25 0 0015.75 2H4.25zm4.03 6.28a.75.75 0 00-1.06-1.06L4.97 9.47a.75.75 0 000 1.06l2.25 2.25a.75.75 0 001.06-1.06L6.56 10l1.72-1.72zm4.5-1.06a.75.75 0 10-1.06 1.06L13.44 10l-1.72 1.72a.75.75 0 101.06 1.06l2.25-2.25a.75.75 0 000-1.06l-2.25-2.25z" clip-rule="evenodd"/></svg>
            </a>
        </div>
    </div>
</section>


<section class="bg-white border-b border-slate-200 py-8 px-4">
    <div class="max-w-5xl mx-auto grid grid-cols-2 sm:grid-cols-4 gap-8 text-center">
        <div>
            <div class="text-3xl sm:text-4xl font-extrabold text-indigo-600">14</div>
            <div class="text-sm text-slate-500 mt-1">Modul Bisnis</div>
        </div>
        <div>
            <div class="text-3xl sm:text-4xl font-extrabold text-indigo-600">150+</div>
            <div class="text-sm text-slate-500 mt-1">Fitur Total</div>
        </div>
        <div>
            <div class="text-3xl sm:text-4xl font-extrabold text-indigo-600">163</div>
            <div class="text-sm text-slate-500 mt-1">Tabel Database</div>
        </div>
        <div>
            <div class="text-3xl sm:text-4xl font-extrabold text-indigo-600">99.9%</div>
            <div class="text-sm text-slate-500 mt-1">Uptime</div>
        </div>
    </div>
</section>


<section class="bg-slate-50 py-12 px-4">
    <div class="max-w-5xl mx-auto text-center">
        <p class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-8">Cocok untuk</p>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-6">
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-indigo-100 rounded-2xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-indigo-600"><path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-600">HR Manager</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-emerald-600"><path d="M12 7.5a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5z"/><path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 011.5 14.625v-9.75zM8.25 9.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM18.75 9a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75V9.75a.75.75 0 00-.75-.75h-.008zM4.5 9.75A.75.75 0 015.25 9h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75V9.75z" clip-rule="evenodd"/><path d="M2.25 18a.75.75 0 000 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 00-.75-.75H2.25z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-600">Finance</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-purple-100 rounded-2xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-purple-600"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 01.67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 11-.671-1.34l.041-.022zM12 9a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-600">CEO / Owner</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-amber-600"><path d="M11.25 5.337c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.036 1.007-1.875 2.25-1.875S15 2.34 15 3.375c0 .369-.128.713-.349 1.003-.215.283-.401.604-.401.959 0 .332.278.598.61.578 1.91-.114 3.79-.342 5.632-.676a.75.75 0 01.878.645 49.808 49.808 0 01.376 5.452.75.75 0 01-.879.645c-1.842-.334-3.722-.562-5.632-.676a.606.606 0 00-.61.578c0 .355.186.676.401.959.221.29.349.634.349 1.003 0 1.036-1.007 1.875-2.25 1.875s-2.25-.84-2.25-1.875c0-.369.128-.713.349-1.003.215-.283.401-.604.401-.959 0-.332-.278-.598-.61-.578-1.91.114-3.79.342-5.632.676a.75.75 0 01-.878-.645 49.808 49.808 0 01-.376-5.452.75.75 0 01.878-.645c1.842.334 3.722.562 5.632.676.332.02.61-.246.61-.578zM15 3.375a.625.625 0 11-1.25 0 .625.625 0 011.25 0z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-600">Project Manager</span>
            </div>
            <div class="flex flex-col items-center gap-2">
                <div class="w-14 h-14 bg-pink-100 rounded-2xl flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-7 h-7 text-pink-600"><path d="M2.273 5.625A4.483 4.483 0 015.25 4.5h13.5c1.141 0 2.183.425 2.977 1.125A3 3 0 0018.75 3H5.25a3 3 0 00-2.977 2.625zM2.273 8.625A4.483 4.483 0 015.25 7.5h13.5c1.141 0 2.183.425 2.977 1.125A3 3 0 0018.75 6H5.25a3 3 0 00-2.977 2.625zM5.25 9a3 3 0 00-3 3v6a3 3 0 003 3h13.5a3 3 0 003-3v-6a3 3 0 00-3-3H15a.75.75 0 00-.75.75 2.25 2.25 0 01-4.5 0A.75.75 0 009 9H5.25z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-600">Kasir</span>
            </div>
        </div>
    </div>
</section>


<section class="py-20 px-4">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 text-center mb-12">Dari Kekacauan Excel ke Satu Sumber Kebenaran</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-red-50 rounded-2xl p-8 border border-red-200">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-red-500 rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-5 h-5"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-red-800">Sebelum</h3>
                </div>
                <ul class="space-y-3 text-sm text-red-700">
                    <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&times;</span> Data karyawan di satu file, absensi di file lain, payroll manual pakai Excel terpisah</li>
                    <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&times;</span> Invoice ditulis manual, lupa kirim, tidak tahu mana yang sudah dibayar</li>
                    <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&times;</span> Approval cuti via WhatsApp — tidak ada audit trail</li>
                    <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&times;</span> Laporan keuangan butuh 3 hari kompilasi dari berbagai sumber</li>
                    <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&times;</span> Salah rumus PPh21 = risiko denda pajak</li>
                    <li class="flex items-start gap-2"><span class="text-red-400 mt-0.5">&times;</span> Data pelanggan tersebar di HP sales masing-masing</li>
                </ul>
            </div>
            <div class="bg-emerald-50 rounded-2xl p-8 border border-emerald-200">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="white" class="w-5 h-5"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-emerald-800">Sesudah — Dengan BizOS</h3>
                </div>
                <ul class="space-y-3 text-sm text-emerald-700">
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Satu database terpusat — HRM &rarr; Payroll &rarr; Accounting otomatis terhubung</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Invoice otomatis dengan tracking status paid/unpaid/overdue</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Approval workflow bertingkat dengan audit trail lengkap</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Laporan real-time — dashboard update otomatis setiap transaksi</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> PPh21 & BPJS auto-kalkulasi sesuai regulasi terbaru</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> CRM terpusat — semua interaksi pelanggan tercatat rapi</li>
                </ul>
            </div>
        </div>
    </div>
</section>


<section class="py-20 px-4 bg-slate-50">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 text-center mb-4">Fitur Unggulan BizOS</h2>
        <p class="text-slate-500 text-center mb-14 max-w-2xl mx-auto">14 modul bisnis terintegrasi — semuanya dalam satu platform. Tidak perlu integrasi manual antar software berbeda.</p>

        <div class="space-y-20">
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div>
                    <span class="text-indigo-600 font-bold text-sm uppercase tracking-wider">HRM</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 mb-4">Manajemen SDM Lengkap</h3>
                    <p class="text-slate-600 leading-relaxed mb-5">Kelola seluruh siklus karyawan — dari rekrutmen hingga pensiun — dalam satu sistem terintegrasi. Absensi GPS/WiFi/Selfie, cuti otomatis, lembur, reimbursement, dan feedback 360 derajat.</p>
                    <ul class="space-y-2.5 text-sm text-slate-700">
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Data karyawan lengkap — 40+ field termasuk BPJS, NPWP, kontrak, dokumen</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Absensi multi-metode — GPS geofencing, WiFi BSSID, selfie, QR code, NFC</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Cuti dengan multi-level approval dan saldo real-time otomatis</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Rekrutmen end-to-end — job posting, screening, interview, offering</li>
                    </ul>
                </div>
                <div class="browser-mock">
                    <div class="browser-mock-header">
                        <span class="browser-mock-dot bg-red-400"></span><span class="browser-mock-dot bg-yellow-400"></span><span class="browser-mock-dot bg-green-400"></span>
                        <span class="browser-mock-url">hrm.bizos.id</span>
                    </div>
                    <div class="browser-mock-body"><img src="https://placehold.co/800x500/4f46e5/ffffff?text=BizOS+HRM+Dashboard" alt="BizOS HRM Dashboard" loading="lazy"></div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div class="order-2 lg:order-1 browser-mock">
                    <div class="browser-mock-header">
                        <span class="browser-mock-dot bg-red-400"></span><span class="browser-mock-dot bg-yellow-400"></span><span class="browser-mock-dot bg-green-400"></span>
                        <span class="browser-mock-url">payroll.bizos.id</span>
                    </div>
                    <div class="browser-mock-body"><img src="https://placehold.co/800x500/7c3aed/ffffff?text=BizOS+Payroll" alt="BizOS Payroll" loading="lazy"></div>
                </div>
                <div class="order-1 lg:order-2">
                    <span class="text-purple-600 font-bold text-sm uppercase tracking-wider">Payroll</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 mb-4">Payroll & Perpajakan Otomatis</h3>
                    <p class="text-slate-600 leading-relaxed mb-5">Hitung gaji batch processing dengan kalkulasi PPh21 progresif, BPJS multi-komponen, THR, bonus. Slip gaji digital siap kirim ke email karyawan.</p>
                    <ul class="space-y-2.5 text-sm text-slate-700">
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Komponen gaji fleksibel — fixed, percentage, formula, per attendance</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> PPh21 otomatis — PTKP, layer progresif, update mengikuti regulasi DJP</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> BPJS TK & Kesehatan — JHT, JP, JKK, JKM, KES dengan salary cap</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> THR otomatis — 1x gaji, prorated, atau formula custom</li>
                    </ul>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div>
                    <span class="text-emerald-600 font-bold text-sm uppercase tracking-wider">Finance</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 mb-4">Akuntansi Double-Entry</h3>
                    <p class="text-slate-600 leading-relaxed mb-5">COA hierarkis PSAK Indonesia, jurnal otomatis dari semua transaksi, invoice PPN 11%, AR/AP aging, budget variance, dan manajemen aset lengkap.</p>
                    <ul class="space-y-2.5 text-sm text-slate-700">
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Jurnal otomatis — dari invoice, pembayaran, payroll, POS</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Multi-tax — PPN, PPh21, PPh22, PPh23, PPh25, PPh Final</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Budget per departemen/proyek dengan realisasi & variance</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Manajemen aset — penyusutan otomatis, mutasi, maintenance</li>
                    </ul>
                </div>
                <div class="browser-mock">
                    <div class="browser-mock-header">
                        <span class="browser-mock-dot bg-red-400"></span><span class="browser-mock-dot bg-yellow-400"></span><span class="browser-mock-dot bg-green-400"></span>
                        <span class="browser-mock-url">finance.bizos.id</span>
                    </div>
                    <div class="browser-mock-body"><img src="https://placehold.co/800x500/059669/ffffff?text=BizOS+Finance" alt="BizOS Finance" loading="lazy"></div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div class="order-2 lg:order-1 browser-mock">
                    <div class="browser-mock-header">
                        <span class="browser-mock-dot bg-red-400"></span><span class="browser-mock-dot bg-yellow-400"></span><span class="browser-mock-dot bg-green-400"></span>
                        <span class="browser-mock-url">crm.bizos.id</span>
                    </div>
                    <div class="browser-mock-body"><img src="https://placehold.co/800x500/2563eb/ffffff?text=BizOS+CRM" alt="BizOS CRM" loading="lazy"></div>
                </div>
                <div class="order-1 lg:order-2">
                    <span class="text-blue-600 font-bold text-sm uppercase tracking-wider">CRM</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 mb-4">CRM & Pipeline Sales</h3>
                    <p class="text-slate-600 leading-relaxed mb-5">Kelola leads dari berbagai sumber, tracking pipeline deal dengan probability, segmentasi klien, dan integrasi WhatsApp blast & auto-reply.</p>
                    <ul class="space-y-2.5 text-sm text-slate-700">
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Lead scoring & aktivitas — follow-up reminder, conversion tracking</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Pipeline visual drag-and-drop — probability & expected value</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> WhatsApp blast campaign — template variabel, targeting segmentasi</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Percakapan WA terpusat — auto-reply, assignment ke sales</li>
                    </ul>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div>
                    <span class="text-amber-600 font-bold text-sm uppercase tracking-wider">Project</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 mb-4">Manajemen Proyek & Task</h3>
                    <p class="text-slate-600 leading-relaxed mb-5">Kanban board, Gantt-style task dependency, milestone, timesheet, dan kolaborasi tim real-time. Progress tracking otomatis per proyek.</p>
                    <ul class="space-y-2.5 text-sm text-slate-700">
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Kanban: Backlog &rarr; Todo &rarr; In Progress &rarr; Review &rarr; Done</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Task dependency — blocks, requires, relates_to</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Timesheet per task dengan approval workflow</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Progress otomatis — milestone & task completion percentage</li>
                    </ul>
                </div>
                <div class="browser-mock">
                    <div class="browser-mock-header">
                        <span class="browser-mock-dot bg-red-400"></span><span class="browser-mock-dot bg-yellow-400"></span><span class="browser-mock-dot bg-green-400"></span>
                        <span class="browser-mock-url">projects.bizos.id</span>
                    </div>
                    <div class="browser-mock-body"><img src="https://placehold.co/800x500/d97706/ffffff?text=BizOS+Project+Management" alt="BizOS Project" loading="lazy"></div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div class="order-2 lg:order-1 browser-mock">
                    <div class="browser-mock-header">
                        <span class="browser-mock-dot bg-red-400"></span><span class="browser-mock-dot bg-yellow-400"></span><span class="browser-mock-dot bg-green-400"></span>
                        <span class="browser-mock-url">pos.bizos.id</span>
                    </div>
                    <div class="browser-mock-body"><img src="https://placehold.co/800x500/dc2626/ffffff?text=BizOS+Point+of+Sales" alt="BizOS POS" loading="lazy"></div>
                </div>
                <div class="order-1 lg:order-2">
                    <span class="text-red-600 font-bold text-sm uppercase tracking-wider">POS</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 mb-4">Point of Sales</h3>
                    <p class="text-slate-600 leading-relaxed mb-5">Sistem kasir lengkap: shift management, barcode/scanner, multi-payment, refund, member loyalty, voucher, dan laporan penjualan real-time.</p>
                    <ul class="space-y-2.5 text-sm text-slate-700">
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-red-100 text-red-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Shift kasir — buka/tutup dengan rekonsiliasi otomatis</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-red-100 text-red-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Multi-payment — Cash, Debit, Credit, QRIS, Transfer</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-red-100 text-red-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Member loyalty — point rewards, earning & redemption</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-red-100 text-red-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Voucher — percentage/fixed, min purchase, usage limit</li>
                    </ul>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div>
                    <span class="text-sky-600 font-bold text-sm uppercase tracking-wider">Kolaborasi</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 mb-4">Kolaborasi Tim Real-Time</h3>
                    <p class="text-slate-600 leading-relaxed mb-5">Chat real-time, meeting dengan AI recap, kalender bersama, form builder, dan cloud storage perusahaan — semua dalam satu platform.</p>
                    <ul class="space-y-2.5 text-sm text-slate-700">
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Chat: personal, group, department — file sharing, emoji reaction</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Meeting — Google Meet/Zoom link, minutes, AI-generated recap</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Form builder — 14 tipe field, drag-and-drop, public share link</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-sky-100 text-sky-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Cloud storage — folder tree, file versioning, public/private</li>
                    </ul>
                </div>
                <div class="browser-mock">
                    <div class="browser-mock-header">
                        <span class="browser-mock-dot bg-red-400"></span><span class="browser-mock-dot bg-yellow-400"></span><span class="browser-mock-dot bg-green-400"></span>
                        <span class="browser-mock-url">chat.bizos.id</span>
                    </div>
                    <div class="browser-mock-body"><img src="https://placehold.co/800x500/0284c7/ffffff?text=BizOS+Collaboration" alt="BizOS Collaboration" loading="lazy"></div>
                </div>
            </div>

            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
                <div class="order-2 lg:order-1 browser-mock">
                    <div class="browser-mock-header">
                        <span class="browser-mock-dot bg-red-400"></span><span class="browser-mock-dot bg-yellow-400"></span><span class="browser-mock-dot bg-green-400"></span>
                        <span class="browser-mock-url">lms.bizos.id</span>
                    </div>
                    <div class="browser-mock-body"><img src="https://placehold.co/800x500/0891b2/ffffff?text=BizOS+LMS+%26+AI" alt="BizOS LMS + AI" loading="lazy"></div>
                </div>
                <div class="order-1 lg:order-2">
                    <span class="text-teal-600 font-bold text-sm uppercase tracking-wider">LMS & AI</span>
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-2 mb-4">Learning & AI Assistant</h3>
                    <p class="text-slate-600 leading-relaxed mb-5">Buat kursus internal, quiz interaktif, sertifikat digital. AI Assistant untuk analisis data, rekomendasi, dan otomatisasi — BYOK (bring your own key).</p>
                    <ul class="space-y-2.5 text-sm text-slate-700">
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Kursus dengan modul & lesson — text, video, PDF, quiz</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Quiz: multiple choice, essay, fill-blank — auto-grading</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> AI dynamic provider — OpenAI, DeepSeek, Anthropic, Ollama</li>
                        <li class="flex items-start gap-2"><span class="w-5 h-5 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 text-xs font-bold">&check;</span> Knowledge base RAG — upload SOP, policy, FAQ</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>


<section class="py-20 px-4">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 text-center mb-4">Jelajahi Semua Modul</h2>
        <p class="text-slate-500 text-center mb-12">14 modul bisnis dalam satu platform terintegrasi</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php
            $screens = [
                ['title' => 'Dashboard', 'url' => 'https://placehold.co/400x300/312e81/ffffff?text=Dashboard'],
                ['title' => 'Karyawan', 'url' => 'https://placehold.co/400x300/4338ca/ffffff?text=Karyawan'],
                ['title' => 'Absensi', 'url' => 'https://placehold.co/400x300/4f46e5/ffffff?text=Absensi'],
                ['title' => 'Payroll', 'url' => 'https://placehold.co/400x300/6366f1/ffffff?text=Payroll'],
                ['title' => 'COA & Jurnal', 'url' => 'https://placehold.co/400x300/7c3aed/ffffff?text=COA+%26+Jurnal'],
                ['title' => 'Invoice', 'url' => 'https://placehold.co/400x300/8b5cf6/ffffff?text=Invoice'],
                ['title' => 'CRM Pipeline', 'url' => 'https://placehold.co/400x300/a855f7/ffffff?text=CRM+Pipeline'],
                ['title' => 'Kanban Task', 'url' => 'https://placehold.co/400x300/c084fc/ffffff?text=Kanban+Task'],
                ['title' => 'POS Kasir', 'url' => 'https://placehold.co/400x300/d946ef/ffffff?text=POS+Kasir'],
                ['title' => 'Chat', 'url' => 'https://placehold.co/400x300/e879f9/ffffff?text=Chat'],
                ['title' => 'Meeting', 'url' => 'https://placehold.co/400x300/f0abfc/ffffff?text=Meeting'],
                ['title' => 'Laporan', 'url' => 'https://placehold.co/400x300/e9d5ff/ffffff?text=Laporan'],
            ];
            ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $screens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $screen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div class="browser-mock">
                <div class="browser-mock-header">
                    <span class="browser-mock-dot bg-red-400"></span><span class="browser-mock-dot bg-yellow-400"></span><span class="browser-mock-dot bg-green-400"></span>
                    <span class="browser-mock-url text-[10px]"><?php echo e(strtolower(str_replace(' ', '-', $screen['title']))); ?>.bizos.id</span>
                </div>
                <div class="browser-mock-body"><img src="<?php echo e($screen['url']); ?>" alt="<?php echo e($screen['title']); ?>" loading="lazy"></div>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>
</section>


<section class="py-20 px-4 bg-slate-50">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 text-center mb-4">Siapa yang Membutuhkan BizOS?</h2>
        <p class="text-slate-500 text-center mb-12">BizOS dirancang untuk berbagai industri dan skala bisnis</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-indigo-600"><path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-2">Perusahaan Menengah-Besar (100-5000+ karyawan)</h3>
                <p class="text-sm text-slate-600 leading-relaxed">HRM untuk kelola ribuan karyawan, payroll batch processing, multi-cabang, multi-company. Struktur organisasi kompleks dengan departemen, jabatan, grade.</p>
            </div>
            <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-emerald-600"><path d="M12 7.5a2.25 2.25 0 100 4.5 2.25 2.25 0 000-4.5z"/><path fill-rule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 011.5 14.625v-9.75zM8.25 9.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM18.75 9a.75.75 0 00-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 00.75-.75V9.75a.75.75 0 00-.75-.75h-.008zM4.5 9.75A.75.75 0 015.25 9h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H5.25a.75.75 0 01-.75-.75V9.75z" clip-rule="evenodd"/><path d="M2.25 18a.75.75 0 000 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 00-.75-.75H2.25z"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-2">Finance & Accounting Firm</h3>
                <p class="text-sm text-slate-600 leading-relaxed">Double-entry accounting, COA PSAK, PPN/PPh, AR/AP aging, budget control, aset management. Laporan keuangan lengkap untuk audit dan kepatuhan pajak.</p>
            </div>
            <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-amber-600"><path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0118 9.375v9.375a3 3 0 003-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 00-.673-.05A3 3 0 0015 1.5h-1.5a3 3 0 00-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6zM13.5 3A1.5 1.5 0 0012 4.5h4.5A1.5 1.5 0 0015 3h-1.5z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 013 20.625V9.375zM6 12a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V12zm2.25 0a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75zM6 15a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V15zm2.25 0a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75zM6 18a.75.75 0 01.75-.75h.008a.75.75 0 01.75.75v.008a.75.75 0 01-.75.75H6.75a.75.75 0 01-.75-.75V18zm2.25 0a.75.75 0 01.75-.75h3.75a.75.75 0 010 1.5H9a.75.75 0 01-.75-.75z" clip-rule="evenodd"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-2">Retail & F&B</h3>
                <p class="text-sm text-slate-600 leading-relaxed">POS dengan barcode scanner, multi-payment, shift kasir, member loyalty, voucher, inventory tracking. Integrasi langsung ke accounting & laporan penjualan.</p>
            </div>
            <div class="bg-white rounded-2xl p-7 border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-purple-600"><path d="M11.25 5.337c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.036 1.007-1.875 2.25-1.875S15 2.34 15 3.375c0 .369-.128.713-.349 1.003-.215.283-.401.604-.401.959 0 .332.278.598.61.578 1.91-.114 3.79-.342 5.632-.676a.75.75 0 01.878.645 49.808 49.808 0 01.376 5.452.75.75 0 01-.879.645c-1.842-.334-3.722-.562-5.632-.676a.606.606 0 00-.61.578c0 .355.186.676.401.959.221.29.349.634.349 1.003 0 1.036-1.007 1.875-2.25 1.875s-2.25-.84-2.25-1.875c0-.369.128-.713.349-1.003.215-.283.401-.604.401-.959 0-.332-.278-.598-.61-.578-1.91.114-3.79.342-5.632.676a.75.75 0 01-.878-.645 49.808 49.808 0 01-.376-5.452.75.75 0 01.878-.645c1.842.334 3.722.562 5.632.676.332.02.61-.246.61-.578z"/></svg>
                </div>
                <h3 class="font-bold text-slate-800 mb-2">Agency & Konsultan</h3>
                <p class="text-sm text-slate-600 leading-relaxed">Project management dengan Kanban, timesheet, milestone, client billing. CRM untuk pipeline deal. Kolaborasi tim jarak jauh dengan chat & meeting.</p>
            </div>
        </div>
    </div>
</section>


<section class="py-20 px-4">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 text-center mb-4">Akun Demo</h2>
        <p class="text-slate-500 text-center mb-8">Coba BizOS dengan akun demo sesuai role Anda. Semua akun menggunakan password: <code class="bg-slate-100 px-2 py-0.5 rounded text-sm font-mono">password</code></p>
        <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-100">
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-700 uppercase text-xs tracking-wider">Role</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-700 uppercase text-xs tracking-wider">Email</th>
                        <th class="px-5 py-3.5 text-left font-semibold text-slate-700 uppercase text-xs tracking-wider hidden sm:table-cell">Cakupan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <tr class="hover:bg-slate-50"><td class="px-5 py-3 font-semibold">Super Admin</td><td class="px-5 py-3"><code class="text-xs bg-slate-100 px-2 py-1 rounded font-mono">admin@bizos.test</code></td><td class="px-5 py-3 text-slate-600 text-xs hidden sm:table-cell">Akses penuh — semua perusahaan, semua modul</td></tr>
                    <tr class="hover:bg-slate-50"><td class="px-5 py-3 font-semibold">HR Manager</td><td class="px-5 py-3"><code class="text-xs bg-slate-100 px-2 py-1 rounded font-mono">hr@bizos.test</code></td><td class="px-5 py-3 text-slate-600 text-xs hidden sm:table-cell">Karyawan, absensi, cuti, reimbursement, payroll</td></tr>
                    <tr class="hover:bg-slate-50"><td class="px-5 py-3 font-semibold">Finance</td><td class="px-5 py-3"><code class="text-xs bg-slate-100 px-2 py-1 rounded font-mono">finance@bizos.test</code></td><td class="px-5 py-3 text-slate-600 text-xs hidden sm:table-cell">COA, jurnal, invoice, pembayaran, pajak</td></tr>
                    <tr class="hover:bg-slate-50"><td class="px-5 py-3 font-semibold">Manager</td><td class="px-5 py-3"><code class="text-xs bg-slate-100 px-2 py-1 rounded font-mono">manager@bizos.test</code></td><td class="px-5 py-3 text-slate-600 text-xs hidden sm:table-cell">Proyek, task, timesheet, CRM, laporan</td></tr>
                    <tr class="hover:bg-slate-50"><td class="px-5 py-3 font-semibold">Kasir</td><td class="px-5 py-3"><code class="text-xs bg-slate-100 px-2 py-1 rounded font-mono">kasir@bizos.test</code></td><td class="px-5 py-3 text-slate-600 text-xs hidden sm:table-cell">Shift kasir, transaksi POS, refund</td></tr>
                    <tr class="hover:bg-slate-50"><td class="px-5 py-3 font-semibold">Staff</td><td class="px-5 py-3"><code class="text-xs bg-slate-100 px-2 py-1 rounded font-mono">staff@bizos.test</code></td><td class="px-5 py-3 text-slate-600 text-xs hidden sm:table-cell">Absensi, cuti, overtime, task, chat</td></tr>
                </tbody>
            </table>
        </div>
        <div class="mt-6 text-center">
            <a href="<?php echo e(url('/admin/login')); ?>" class="inline-flex items-center gap-2 px-8 py-4 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-200 no-underline">
                Login Admin Panel
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M6 10a.75.75 0 01.75-.75h9.546l-1.048-.943a.75.75 0 111.004-1.114l2.5 2.25a.75.75 0 010 1.114l-2.5 2.25a.75.75 0 11-1.004-1.114l1.048-.943H6.75A.75.75 0 016 10z" clip-rule="evenodd"/></svg>
            </a>
        </div>
    </div>
</section>


<section class="py-20 px-4 bg-slate-50">
    <div class="max-w-5xl mx-auto">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 text-center mb-4">Harga Sederhana</h2>
        <p class="text-slate-500 text-center mb-12">Pilih paket yang sesuai dengan skala bisnis Anda</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm hover:shadow-lg transition-shadow">
                <h3 class="text-xl font-bold text-slate-800 mb-2">Starter</h3>
                <div class="text-4xl font-extrabold text-slate-900 mb-1">Rp 0</div>
                <p class="text-sm text-slate-500 mb-6">Gratis selamanya</p>
                <ul class="space-y-3 text-sm text-slate-600 mb-8">
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> 1 Company</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Maks 10 karyawan</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Absensi & cuti dasar</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Fitur accounting dasar</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Support forum</li>
                </ul>
                <a href="<?php echo e(url('/admin/login')); ?>" class="block text-center w-full py-3 border-2 border-indigo-200 text-indigo-700 font-bold rounded-xl hover:bg-indigo-50 transition-colors no-underline">Mulai Gratis</a>
            </div>
            
            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-8 shadow-xl shadow-indigo-200 text-white transform scale-105 relative">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-amber-400 text-amber-900 text-xs font-bold px-3 py-1 rounded-full uppercase">Paling Populer</div>
                <h3 class="text-xl font-bold mb-2">Growth</h3>
                <div class="text-4xl font-extrabold mb-1">Rp 1.5jt</div>
                <p class="text-sm text-indigo-200 mb-6">per bulan</p>
                <ul class="space-y-3 text-sm text-indigo-100 mb-8">
                    <li class="flex items-start gap-2"><span class="text-emerald-300 mt-0.5">&check;</span> 3 Companies</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-300 mt-0.5">&check;</span> Karyawan unlimited</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-300 mt-0.5">&check;</span> Semua modul: HRM, Payroll, Finance</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-300 mt-0.5">&check;</span> CRM, Project, POS, LMS</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-300 mt-0.5">&check;</span> AI Assistant</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-300 mt-0.5">&check;</span> WhatsApp Broadcast</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-300 mt-0.5">&check;</span> Support prioritas</li>
                </ul>
                <a href="<?php echo e(url('/admin/login')); ?>" class="block text-center w-full py-3 bg-white text-indigo-700 font-bold rounded-xl hover:bg-indigo-50 transition-colors no-underline">Coba Growth</a>
            </div>
            
            <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm hover:shadow-lg transition-shadow">
                <h3 class="text-xl font-bold text-slate-800 mb-2">Enterprise</h3>
                <div class="text-4xl font-extrabold text-slate-900 mb-1">Custom</div>
                <p class="text-sm text-slate-500 mb-6">Hubungi kami</p>
                <ul class="space-y-3 text-sm text-slate-600 mb-8">
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Company unlimited</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Semua fitur Growth</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Whitelabel / rebrand</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> On-premise deployment</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Custom development</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> SLA & dedicated support</li>
                    <li class="flex items-start gap-2"><span class="text-emerald-500 mt-0.5">&check;</span> Source code (opsional)</li>
                </ul>
                <a href="#" class="block text-center w-full py-3 border-2 border-indigo-200 text-indigo-700 font-bold rounded-xl hover:bg-indigo-50 transition-colors no-underline">Hubungi Sales</a>
            </div>
        </div>
    </div>
</section>


<section class="gradient-hero py-20 px-4">
    <div class="max-w-3xl mx-auto text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-4">Siap Upgrade Bisnis Anda?</h2>
        <p class="text-indigo-200 text-lg mb-8 max-w-xl mx-auto">Dari Excel manual ke sistem terintegrasi. Coba BizOS gratis sekarang — tanpa kartu kredit, tanpa komitmen.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?php echo e(url('/admin/login')); ?>" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-indigo-700 font-bold rounded-xl hover:bg-indigo-50 transition-colors shadow-xl shadow-indigo-500/25 text-lg no-underline">
                Coba Demo Gratis
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 015.25 2h5.5A2.25 2.25 0 0113 4.25v2a.75.75 0 01-1.5 0v-2a.75.75 0 00-.75-.75h-5.5a.75.75 0 00-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 00.75-.75v-2a.75.75 0 011.5 0v2A2.25 2.25 0 0110.75 18h-5.5A2.25 2.25 0 013 15.75V4.25z" clip-rule="evenodd"/><path fill-rule="evenodd" d="M6 10a.75.75 0 01.75-.75h9.546l-1.048-.943a.75.75 0 111.004-1.114l2.5 2.25a.75.75 0 010 1.114l-2.5 2.25a.75.75 0 11-1.004-1.114l1.048-.943H6.75A.75.75 0 016 10z" clip-rule="evenodd"/></svg>
            </a>
            <a href="<?php echo e(url('/docs')); ?>" class="inline-flex items-center justify-center gap-2 px-8 py-4 border-2 border-white/30 text-white font-bold rounded-xl hover:bg-white/10 transition-colors text-lg no-underline">
                Baca Dokumentasi
            </a>
        </div>
    </div>
</section>


<footer class="bg-slate-900 text-slate-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Produk</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?php echo e(url('/docs')); ?>" class="hover:text-white transition-colors no-underline">Dokumentasi</a></li>
                    <li><a href="<?php echo e(url('/best-hrm-software')); ?>" class="hover:text-white transition-colors no-underline">Fitur HRM</a></li>
                    <li><a href="<?php echo e(url('/best-accounting-software-indonesia')); ?>" class="hover:text-white transition-colors no-underline">Fitur Akuntansi</a></li>
                    <li><a href="<?php echo e(url('/compare/bizos-vs-spreadsheet')); ?>" class="hover:text-white transition-colors no-underline">vs Spreadsheet</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Akses</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?php echo e(url('/admin/login')); ?>" class="hover:text-white transition-colors no-underline">Admin Login</a></li>
                    <li><a href="<?php echo e(url('/sitemap.xml')); ?>" class="hover:text-white transition-colors no-underline">Sitemap</a></li>
                    <li><a href="<?php echo e(url('/robots.txt')); ?>" class="hover:text-white transition-colors no-underline">Robots.txt</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Perusahaan</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?php echo e(url('/')); ?>" class="hover:text-white transition-colors no-underline">Beranda</a></li>
                    <li><a href="#" class="hover:text-white transition-colors no-underline">Kontak</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Legal</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-white transition-colors no-underline">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-white transition-colors no-underline">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div>&copy; <?php echo e(date('Y')); ?> BizOS — Business Operating System. Seluruh hak cipta dilindungi.</div>
            <div class="flex items-center gap-4">
                <a href="<?php echo e(url('/sitemap.xml')); ?>" class="hover:text-white transition-colors no-underline">Sitemap</a>
                <a href="<?php echo e(url('/robots.txt')); ?>" class="hover:text-white transition-colors no-underline">Robots</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
<?php /**PATH D:\project laravel\bizos\resources\views/marketing.blade.php ENDPATH**/ ?>