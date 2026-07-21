<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ProgrammaticSeoController extends Controller
{
    public function bestHrm(): View
    {
        $features = $this->hrmFeatures();
        $meta = $this->bestHrmMeta();

        return view('pseo.best-hrm', compact('features', 'meta'));
    }

    public function bestAccounting(): View
    {
        $features = $this->accountingFeatures();
        $meta = $this->bestAccountingMeta();

        return view('pseo.best-accounting', compact('features', 'meta'));
    }

    public function compareVsSpreadsheet(): View
    {
        $comparisons = $this->comparisonData();
        $meta = $this->compareMeta();

        return view('pseo.compare-spreadsheet', compact('comparisons', 'meta'));
    }

    protected function hrmFeatures(): array
    {
        return [
            [
                'rank' => 1,
                'title' => 'Manajemen Data Karyawan Lengkap',
                'desc' => 'BizOS menyediakan database karyawan terpusat dengan 40+ field data: personal, employment, bank account, BPJS, NPWP, kontrak, dan dokumen pendukung. Semua terintegrasi dengan modul payroll, absensi, dan performance.',
                'icon' => 'users',
            ],
            [
                'rank' => 2,
                'title' => 'Absensi Multi-Metode',
                'desc' => 'Dukung GPS geofencing, WiFi BSSID validation, selfie verification, QR code scanning, dan NFC untuk clock-in/clock-out. Anti-fraud dengan foto + lokasi + waktu real-time. Semua tercatat di audit log.',
                'icon' => 'clock',
            ],
            [
                'rank' => 3,
                'title' => 'Manajemen Cuti & Lembur',
                'desc' => '15+ tipe cuti dengan saldo otomatis, multi-level approval, dan tracking real-time. Lembur dihitung otomatis dengan rate multiplier (1.5x, 2x) sesuai aturan ketenagakerjaan.',
                'icon' => 'calendar',
            ],
            [
                'rank' => 4,
                'title' => 'Rekrutmen End-to-End',
                'desc' => 'Job posting → screening → interview scheduling → hasil interview → offering → hire. Semua dalam satu alur. Pipeline stage visual dengan drag-and-drop.',
                'icon' => 'briefcase',
            ],
            [
                'rank' => 5,
                'title' => 'Payroll Otomatis',
                'desc' => 'Generate payroll batch dengan kalkulasi PPh21 progresif, BPJS multi-komponen (JHT, JP, JKK, JKM, KES), THR, bonus. Output slip gaji PDF siap kirim.',
                'icon' => 'dollar',
            ],
            [
                'rank' => 6,
                'title' => 'Feedback 360 Derajat',
                'desc' => 'Siklus review dengan self-assessment, supervisor review, peer review, dan subordinate feedback. Kategori kompetensi: technical, soft skill, leadership, communication.',
                'icon' => 'star',
            ],
            [
                'rank' => 7,
                'title' => 'Reimbursement & Expense',
                'desc' => 'Pengajuan klaim dengan upload bukti, approval bertingkat, dan tracking pembayaran. Kategori fleksibel: transport, makan, hotel, medis, dll.',
                'icon' => 'receipt',
            ],
            [
                'rank' => 8,
                'title' => 'Multi-Company Support',
                'desc' => 'Satu instalasi BizOS bisa mengelola banyak perusahaan dengan data terisolasi. Cocok untuk holding company, grup usaha, atau BPO HR.',
                'icon' => 'building',
            ],
            [
                'rank' => 9,
                'title' => 'Kantin & Pengumuman',
                'desc' => 'Fitur kantin digital: menu, order, pembayaran via saldo. Pengumuman perusahaan dengan targeting per departemen/jabatan dan read receipt.',
                'icon' => 'megaphone',
            ],
            [
                'rank' => 10,
                'title' => 'Laporan & Analitik HR',
                'desc' => 'Dashboard HR real-time: headcount, turnover rate, absensi rate, demographic analysis. Export PDF/Excel untuk kebutuhan audit dan management review.',
                'icon' => 'chart',
            ],
        ];
    }

    protected function bestHrmMeta(): array
    {
        $url = url('/best-hrm-software');

        return [
            'title' => '10 Fitur HRM Terbaik di BizOS — Software HR Indonesia 2026',
            'description' => 'Inilah 10 fitur HRM unggulan BizOS: manajemen karyawan lengkap, absensi GPS/WiFi/Selfie, payroll otomatis dengan PPh21 & BPJS, rekrutmen, cuti, feedback 360. Software HR all-in-one untuk bisnis Indonesia.',
            'canonical' => $url,
            'og_title' => '10 Fitur HRM Terbaik BizOS — Software HR Indonesia',
            'og_description' => 'Manajemen SDM komplet: absensi multi-metode, payroll PPh21, cuti otomatis, rekrutmen, feedback 360. All-in-one HR solution.',
            'og_url' => $url,
            'jsonld' => [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => '10 Fitur HRM Terbaik di BizOS',
                'description' => 'Daftar 10 fitur HRM unggulan di BizOS Business Operating System',
                'numberOfItems' => 10,
                'itemListElement' => array_map(fn ($f, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $f['title'],
                    'description' => $f['desc'],
                ], $this->hrmFeatures(), array_keys($this->hrmFeatures())),
            ],
        ];
    }

    protected function accountingFeatures(): array
    {
        return [
            [
                'rank' => 1,
                'title' => 'Double-Entry Accounting',
                'desc' => 'Sistem akuntansi double-entry lengkap dengan Chart of Accounts (COA) hierarkis sesuai standar PSAK Indonesia. Jurnal otomatis dari setiap transaksi: penjualan, pembelian, payroll, pembayaran.',
                'icon' => 'book',
            ],
            [
                'rank' => 2,
                'title' => 'Invoice & Pembayaran Terintegrasi',
                'desc' => 'Buat invoice sales & purchase dengan PPN 11%, multi-item, diskon. Tracking status: draft → sent → partial → paid → overdue. Multi-metode pembayaran: cash, transfer, QRIS, kartu kredit.',
                'icon' => 'document',
            ],
            [
                'rank' => 3,
                'title' => 'Perpajakan Lengkap',
                'desc' => 'Konfigurasi PPN, PPh21, PPh22, PPh23, PPh25, PPh Final. Tax transaction tracking dengan payment status. Siap e-Faktur & SPT.',
                'icon' => 'calculator',
            ],
            [
                'rank' => 4,
                'title' => 'Budget & Variance Analysis',
                'desc' => 'Buat budget per departemen, proyek, atau COA. Real-time tracking realisasi vs rencana. Variance analysis untuk kontrol keuangan.',
                'icon' => 'chart-bar',
            ],
            [
                'rank' => 5,
                'title' => 'Manajemen Aset & Penyusutan',
                'desc' => 'Catat seluruh aset perusahaan dengan kategori, lokasi, PIC. Hitung penyusutan otomatis (straight-line, declining balance). Tracking mutasi & maintenance.',
                'icon' => 'cube',
            ],
            [
                'rank' => 6,
                'title' => 'General Ledger & Neraca',
                'desc' => 'Posting otomatis dari semua jurnal ke General Ledger. Generate Trial Balance, Income Statement, Balance Sheet, Cash Flow Statement.',
                'icon' => 'table',
            ],
            [
                'rank' => 7,
                'title' => 'Multi-Company Accounting',
                'desc' => 'Kelola akuntansi untuk banyak perusahaan dalam satu sistem. Konsolidasi laporan keuangan grup usaha dengan eliminasi intra-grup.',
                'icon' => 'office',
            ],
            [
                'rank' => 8,
                'title' => 'Approval Workflow',
                'desc' => 'Approval bertingkat untuk transaksi di atas threshold. Configurable approval flow dengan step dan approver role. Full audit trail.',
                'icon' => 'check-badge',
            ],
            [
                'rank' => 9,
                'title' => 'AR/AP Aging Report',
                'desc' => 'Analisis piutang dan hutang berdasarkan umur (0-30, 31-60, 61-90, >90 hari). Identifikasi overdue dan potensi bad debt.',
                'icon' => 'clock',
            ],
            [
                'rank' => 10,
                'title' => 'Export & Reporting',
                'desc' => 'Export laporan keuangan ke Excel & PDF. Format siap audit. Schedule report otomatis dan kirim via email ke management.',
                'icon' => 'arrow-down',
            ],
        ];
    }

    protected function bestAccountingMeta(): array
    {
        $url = url('/best-accounting-software-indonesia');

        return [
            'title' => '10 Fitur Akuntansi Terbaik BizOS — Software Accounting Indonesia 2026',
            'description' => 'Fitur akuntansi BizOS: double-entry, COA PSAK, invoice PPN, PPh21-25, budget variance, manajemen aset, general ledger. Software akuntansi bisnis Indonesia.',
            'canonical' => $url,
            'og_title' => '10 Fitur Akuntansi Terbaik BizOS — Accounting Indonesia',
            'og_description' => 'Solusi akuntansi komplet: double-entry, PPN/PPh, invoice, budget, aset, laporan keuangan. Integrasi dengan HRM, POS, CRM.',
            'og_url' => $url,
            'jsonld' => [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => '10 Fitur Akuntansi Terbaik di BizOS',
                'description' => 'Daftar 10 fitur akuntansi unggulan di BizOS',
                'numberOfItems' => 10,
                'itemListElement' => array_map(fn ($f, $i) => [
                    '@type' => 'ListItem',
                    'position' => $i + 1,
                    'name' => $f['title'],
                    'description' => $f['desc'],
                ], $this->accountingFeatures(), array_keys($this->accountingFeatures())),
            ],
        ];
    }

    protected function comparisonData(): array
    {
        return [
            [
                'category' => 'Integrasi Data',
                'bizos' => 'Semua modul (HRM, Payroll, Finance, CRM, Project, POS, LMS) terintegrasi otomatis. Satu kali input data, semua modul terupdate.',
                'spreadsheet' => 'Data terpisah di file Excel berbeda. HR punya file sendiri, Finance punya file sendiri. Update manual rawan inkonsistensi.',
                'bizosWin' => true,
            ],
            [
                'category' => 'Akurasi Perhitungan',
                'bizos' => 'Kalkulasi payroll, PPh21, BPJS, PPN otomatis dengan formula yang selalu update. Risiko human error mendekati nol.',
                'spreadsheet' => 'Formula Excel rawan error: salah cell reference, salah copy-paste, lupa update rate pajak terbaru. Satu kesalahan rumus bisa berakibat fatal.',
                'bizosWin' => true,
            ],
            [
                'category' => 'Keamanan Data',
                'bizos' => 'Role-based access control granular. Audit log mencatat setiap perubahan: siapa, kapan, data lama, data baru. Data dienkripsi, backup otomatis.',
                'spreadsheet' => 'File Excel bisa dikirim via email/WhatsApp, disimpan di laptop pribadi, tanpa password. Risiko kebocoran data karyawan & keuangan sangat tinggi.',
                'bizosWin' => true,
            ],
            [
                'category' => 'Kolaborasi Tim',
                'bizos' => 'Chat real-time, meeting scheduler, task assignment, approval workflow. Semua stakeholder bisa akses data yang sama secara real-time.',
                'spreadsheet' => 'Kolaborasi terbatas: kirim file via email, tunggu balasan, merge perubahan manual. Sering terjadi konflik versi ("final_v2_revised_FINAL.xlsx").',
                'bizosWin' => true,
            ],
            [
                'category' => 'Akses Mobile',
                'bizos' => 'Akses via browser di HP untuk semua fitur. Karyawan bisa clock-in, ajukan cuti, cek slip gaji via mobile. Owner bisa monitor bisnis dari mana saja.',
                'spreadsheet' => 'Excel di HP sangat terbatas. Edit formula di layar kecil hampir mustahil. Data tidak real-time.',
                'bizosWin' => true,
            ],
            [
                'category' => 'Skalabilitas',
                'bizos' => 'Dirancang untuk multi-company, ratusan/rbuan karyawan, puluhan ribu transaksi per hari. Database MySQL dengan indexing optimal.',
                'spreadsheet' => 'Mulai lambat di 10.000+ baris. Formula kompleks bikin file corrupt. Tidak ada multi-user locking yang proper.',
                'bizosWin' => true,
            ],
            [
                'category' => 'Kepatuhan Pajak',
                'bizos' => 'PPh21 progresif terbaru, BPJS rate update, PPN 11%. Semua perhitungan sesuai regulasi terbaru. Siap generate SPT.',
                'spreadsheet' => 'Harus update rate manual setiap ada perubahan peraturan. Berisiko salah hitung pajak yang berujung denda atau masalah hukum.',
                'bizosWin' => true,
            ],
            [
                'category' => 'Laporan & Analitik',
                'bizos' => 'Dashboard real-time dengan chart interaktif. Laporan HR, Finance, Sales, Project otomatis. Export PDF/Excel. Kirim jadwal via email.',
                'spreadsheet' => 'Bikin laporan butuh waktu berjam-jam: kumpulkan data dari berbagai file, pivot table, chart manual. Setiap bulan ulangi proses yang sama.',
                'bizosWin' => true,
            ],
            [
                'category' => 'Approval Workflow',
                'bizos' => 'Cuti, lembur, reimbursement, budget, transaksi besar — semua punya approval workflow bertingkat. Notifikasi otomatis ke approver.',
                'spreadsheet' => 'Approval via chat/email: "Pak, approve ya" — tidak ada audit trail formal. Siapa approve, kapan, tidak tercatat sistematis.',
                'bizosWin' => true,
            ],
            [
                'category' => 'Biaya Total',
                'bizos' => 'Mulai dari Rp 0 (Starter). Growth Rp 1.5jt/bulan. Enterprise custom. Satu sistem untuk semua departemen — lebih murah dari 5+ software terpisah.',
                'spreadsheet' => 'Tampak "gratis" tapi hidden cost tinggi: waktu admin untuk input & koreksi, risiko kesalahan mahal, produktivitas rendah, compliance risk.',
                'bizosWin' => true,
            ],
        ];
    }

    protected function compareMeta(): array
    {
        $url = url('/compare/bizos-vs-spreadsheet');

        return [
            'title' => 'BizOS vs Excel/Spreadsheet: Perbandingan Lengkap untuk Bisnis',
            'description' => 'Bandingkan BizOS Business Operating System dengan spreadsheet tradisional (Excel/Google Sheets) untuk manajemen HR, akuntansi, proyek, dan operasional bisnis Anda.',
            'canonical' => $url,
            'og_title' => 'BizOS vs Excel — Mengapa Bisnis Anda Butuh Upgrade',
            'og_description' => '10 kategori perbandingan: integrasi, akurasi, keamanan, kolaborasi, mobile, skalabilitas, pajak, laporan, approval, dan total biaya.',
            'og_url' => $url,
            'jsonld' => [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Apakah BizOS lebih baik dari Excel untuk manajemen bisnis?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Ya, BizOS menawarkan integrasi data otomatis, kalkulasi payroll & pajak akurat, keamanan data dengan role-based access, kolaborasi real-time, dan laporan analitik yang tidak mungkin dicapai dengan spreadsheet manual.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Berapa biaya BizOS dibandingkan dengan menggunakan Excel?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'BizOS mulai dari Rp 0 (Starter) hingga Rp 1.5jt/bulan (Growth). Meskipun Excel terlihat gratis, hidden cost dari waktu admin, risiko kesalahan perhitungan, dan kehilangan produktivitas jauh lebih besar.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Apakah BizOS cocok untuk UKM di Indonesia?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Sangat cocok. BizOS dirancang khusus untuk pasar Indonesia dengan dukungan bahasa Indonesia, perhitungan pajak lokal (PPh21, BPJS, PPN), dan pricing yang terjangkau untuk UKM. Tersedia paket Starter gratis dan Growth Rp 1.5jt/bulan.',
                        ],
                    ],
                ],
            ],
        ];
    }

    public function bestPayroll(): View
    {
        return view('pseo.best-payroll', ['seo' => $this->seoMeta('best-payroll')]);
    }

    public function bestCrm(): View
    {
        return view('pseo.best-crm', ['seo' => $this->seoMeta('best-crm')]);
    }

    public function bestProject(): View
    {
        return view('pseo.best-project', ['seo' => $this->seoMeta('best-project')]);
    }

    public function compareVsTalenta(): View
    {
        return view('pseo.compare-talenta', ['seo' => $this->seoMeta('compare-talenta')]);
    }

    public function compareVsJurnal(): View
    {
        return view('pseo.compare-jurnal', ['seo' => $this->seoMeta('compare-jurnal')]);
    }

    public function alternativesExcel(): View
    {
        return view('pseo.alternatives-excel', ['seo' => $this->seoMeta('alternatives-excel')]);
    }

    public function alternativesTalenta(): View
    {
        return view('pseo.alternatives-talenta', ['seo' => $this->seoMeta('alternatives-talenta')]);
    }

    protected function seoMeta(string $page): array
    {
        $titles = [
            'best-payroll' => '10 Software Payroll Terbaik Indonesia 2026 — BizOS',
            'best-crm' => '10 Software CRM Terbaik untuk Bisnis Indonesia — BizOS',
            'best-project' => '10 Software Project Management Terbaik 2026 — BizOS',
            'compare-talenta' => 'BizOS vs Talenta — Perbandingan Lengkap HRM Software',
            'compare-jurnal' => 'BizOS vs Jurnal.id — Perbandingan Accounting Software',
            'alternatives-excel' => '5 Alternatif Excel untuk HR Management — BizOS',
            'alternatives-talenta' => '7 Alternatif Talenta yang Lebih Terjangkau — BizOS',
        ];

        return [
            'title' => $titles[$page] ?? 'BizOS — Business Operating System',
            'page' => $page,
        ];
    }
}
