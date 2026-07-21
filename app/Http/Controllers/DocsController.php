<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DocsController extends Controller
{
    public function index(): View
    {
        $demoAccounts = $this->demoAccounts();
        $menuStructure = $this->menuStructure();
        $tutorial = $this->tutorial();
        $features = $this->features();
        $seoMeta = $this->seoMeta();

        return view('pseo.docs-index', compact(
            'demoAccounts',
            'menuStructure',
            'tutorial',
            'features',
            'seoMeta'
        ));
    }

    protected function demoAccounts(): array
    {
        return [
            [
                'role' => 'Super Admin',
                'email' => 'admin@bizos.id',
                'password' => 'password',
                'scope' => 'Akses penuh — semua perusahaan, semua modul, semua laporan, sistem',
            ],
            [
                'role' => 'Owner',
                'email' => 'owner@bizos.id',
                'password' => 'password',
                'scope' => 'Dashboard, semua laporan, overview seluruh data bisnis',
            ],
            [
                'role' => 'HR Manager',
                'email' => 'hr@bizos.id',
                'password' => 'password',
                'scope' => 'Kelola karyawan, absensi, cuti, lembur, reimbursement, rekrutmen, feedback',
            ],
            [
                'role' => 'Finance Manager',
                'email' => 'finance@bizos.id',
                'password' => 'password',
                'scope' => 'COA, jurnal, invoice, pembayaran, pajak, budget, aset, laporan keuangan',
            ],
            [
                'role' => 'Manager Operasional',
                'email' => 'manager@bizos.id',
                'password' => 'password',
                'scope' => 'Project, task, timesheet, CRM leads & deals, laporan tim, kolaborasi',
            ],
            [
                'role' => 'Staff',
                'email' => 'staff@bizos.id',
                'password' => 'password',
                'scope' => 'Absensi, cuti, lembur, reimbursement, task, timesheet, chat, LMS',
            ],
            [
                'role' => 'Kasir',
                'email' => 'kasir@bizos.id',
                'password' => 'password',
                'scope' => 'Shift kasir, transaksi POS, refund, member & voucher',
            ],
        ];
    }

    protected function menuStructure(): array
    {
        return [
            'Master Data' => [
                'Perusahaan', 'Cabang', 'Departemen', 'Jabatan', 'Designasi', 'Grade',
            ],
            'HRM' => [
                'Karyawan', 'Absensi & Aturan', 'Shift', 'Cuti & Tipe Cuti',
                'Lembur', 'Reimbursement', 'Kunjungan', 'Rekrutmen & Interview',
                'Feedback 360', 'Pengumuman', 'Kantin',
            ],
            'Payroll' => [
                'Komponen Gaji', 'Periode Payroll', 'Daftar Payroll',
                'Slip Gaji', 'PPH21', 'BPJS', 'THR',
            ],
            'Finance' => [
                'Kategori Akun', 'Chart of Accounts', 'Saldo COA',
                'Jurnal', 'Invoice & Item', 'Metode Pembayaran', 'Pembayaran',
                'Budget', 'Pajak', 'Aset & Kategori', 'Penyusutan', 'Mutasi', 'Maintenance',
            ],
            'CRM' => [
                'Sumber Lead', 'Leads & Aktivitas', 'Klien & Kontak',
                'Segmentasi Klien', 'Pipeline Stage', 'Deals',
                'Template WA', 'Kampanye Blast WA', 'Auto Reply WA', 'Percakapan WA',
            ],
            'Project' => [
                'Proyek & Fase', 'Anggota Proyek', 'Task & Label',
                'Milestone', 'Timesheet', 'Dependency Task',
            ],
            'POS' => [
                'Kategori Produk', 'Produk & Varian', 'Diskon',
                'Shift Kasir', 'Transaksi POS', 'Refund',
                'Member POS', 'Voucher',
            ],
            'Kolaborasi' => [
                'Chat', 'Meeting & Risalah', 'Kalender & Event',
                'Form Builder', 'Dokumen & Folder',
            ],
            'LMS' => [
                'Kursus & Modul', 'Lesson', 'Enrollment',
                'Quiz & Pertanyaan', 'Sertifikat',
            ],
            'AI Assistant' => [
                'Provider AI', 'Conversations', 'Knowledge Base',
            ],
            'Marketing' => [
                'Blog', 'Promo & Diskon', 'Newsletter', 'Testimonial',
            ],
            'Laporan' => [
                'HRM Report', 'Finance Report', 'CRM Pipeline',
                'Project Progress', 'POS Sales', 'LMS Completion',
            ],
            'Integrasi' => [
                'Integrations', 'Webhooks', 'API Tokens',
                'Import/Export', 'Backup Logs',
            ],
            'Sistem' => [
                'Roles & Permissions', 'System Settings', 'Notification Templates',
                'Audit Logs', 'Scheduled Jobs',
            ],
        ];
    }

    protected function tutorial(): array
    {
        return [
            'Fase 1: Setup Awal' => [
                ['step' => 1, 'title' => 'Buat Perusahaan', 'desc' => 'Daftarkan perusahaan Anda di menu Master Data > Perusahaan. Isi nama, kode, NPWP, alamat, dan logo.'],
                ['step' => 2, 'title' => 'Setup Cabang', 'desc' => 'Tambahkan cabang untuk setiap lokasi operasional. Tentukan cabang pusat (headquarters) dan zona waktu masing-masing.'],
                ['step' => 3, 'title' => 'Buat Struktur Organisasi', 'desc' => 'Setup Departemen, Jabatan, Designasi (level), dan Grade (skala gaji) sesuai struktur organisasi perusahaan Anda.'],
                ['step' => 4, 'title' => 'Konfigurasi Role & Permission', 'desc' => 'Buat role seperti Super Admin, HR Manager, Finance, Manager, Staff, Kasir. Assign permission per role sesuai tanggung jawab.'],
            ],
            'Fase 2: Input Data Master' => [
                ['step' => 5, 'title' => 'Input Data Karyawan', 'desc' => 'Masukkan data setiap karyawan: NIK, nama, email, jabatan, departemen, gaji pokok, data BPJS, NPWP, kontrak, dan upload dokumen pendukung (KTP, KK, Ijazah).'],
                ['step' => 6, 'title' => 'Tambah Keluarga Karyawan', 'desc' => 'Catat anggota keluarga untuk keperluan asuransi, kontak darurat, dan perhitungan PTKP PPh21.'],
                ['step' => 7, 'title' => 'Setup User Account', 'desc' => 'Buat user login untuk setiap karyawan. Assign role dan link ke employee record.'],
                ['step' => 8, 'title' => 'Input Produk & Klien', 'desc' => 'Input katalog produk POS beserta kategori, varian, dan harga. Tambahkan data klien CRM dan kontak untuk sales pipeline.'],
                ['step' => 9, 'title' => 'Setup Chart of Accounts', 'desc' => 'Buat kategori COA (Asset, Liability, Equity, Revenue, Expense) lalu isi akun-akun sesuai standar akuntansi Indonesia.'],
            ],
            'Fase 3: Operasional HRM' => [
                ['step' => 10, 'title' => 'Konfigurasi Shift & Absensi', 'desc' => 'Buat shift kerja (Pagi, Siang, Malam) dengan jam masuk, jam pulang, toleransi keterlambatan. Atur metode absensi (GPS, WiFi, Selfie, QR Code), radius GPS, dan hari libur.'],
                ['step' => 11, 'title' => 'Absensi Harian', 'desc' => 'Karyawan clock-in/clock-out via mobile app atau web. Sistem mencatat lokasi GPS, foto, BSSID WiFi, status (tepat waktu, terlambat, lembur), dan log lengkap.'],
                ['step' => 12, 'title' => 'Setup Tipe Cuti', 'desc' => 'Buat tipe cuti: Tahunan (12 hari), Sakit, Melahirkan, Menikah, Duka, dll. Tentukan alokasi default, syarat persetujuan, dan dokumen wajib.'],
                ['step' => 13, 'title' => 'Pengajuan & Approval Cuti', 'desc' => 'Karyawan ajukan cuti via sistem. Manager/HR approve/reject dengan catatan. Sistem otomatis update saldo cuti.'],
                ['step' => 14, 'title' => 'Pengajuan Lembur', 'desc' => 'Ajukan lembur dengan tanggal, jam, alasan. Sistem hitung durasi dan biaya lembur berdasarkan rate multiplier (1.5x, 2x).'],
                ['step' => 15, 'title' => 'Pengajuan Reimbursement', 'desc' => 'Buat kategori reimbursement (Transport, Makan, Hotel, Medis). Karyawan ajukan klaim dengan upload bukti. HR/Finance approve dan catat pembayaran.'],
            ],
            'Fase 4: Payroll' => [
                ['step' => 16, 'title' => 'Setup Komponen Gaji', 'desc' => 'Buat komponen: Gaji Pokok, Tunjangan Jabatan, Transport, BPJS, PPh21, dll. Tentukan tipe (income/deduction), kalkulasi, dan taxable status.'],
                ['step' => 17, 'title' => 'Konfigurasi PPh21 & BPJS', 'desc' => 'Setup tarif PPh21 (PTKP, layer progresif) dan iuran BPJS Ketenagakerjaan & Kesehatan (persentase perusahaan dan karyawan, salary cap).'],
                ['step' => 18, 'title' => 'Jalankan Payroll', 'desc' => 'Buka periode payroll bulanan di menu Payroll > Periode Payroll. Generate payroll otomatis — sistem hitung gaji pokok, tunjangan, lembur, potongan, PPh21, BPJS.'],
                ['step' => 19, 'title' => 'Cetak Slip Gaji', 'desc' => 'Finalisasi payroll setelah dicek. Generate slip gaji PDF dan kirim ke karyawan via email/in-app notification. THR dan bonus juga bisa diproses.'],
            ],
            'Fase 5: Finance' => [
                ['step' => 20, 'title' => 'Buat Invoice', 'desc' => 'Buat invoice sales/purchase dengan item, diskon, pajak PPN 11%. Invoice bisa ditagihkan ke klien dari CRM. Nomor invoice auto-generate.'],
                ['step' => 21, 'title' => 'Catat Pembayaran', 'desc' => 'Input pembayaran masuk/keluar dengan metode (Cash, Transfer, QRIS). Link ke invoice terkait. Sistem tracking status paid/unpaid/partial/overdue.'],
                ['step' => 22, 'title' => 'Input Jurnal Manual', 'desc' => 'Input jurnal umum, penyesuaian, atau pembalik di menu Finance > Jurnal. Sistem auto-generate jurnal dari invoice dan pembayaran. Posting ke General Ledger.'],
                ['step' => 23, 'title' => 'Budget & Realisasi', 'desc' => 'Buat budget per departemen/proyek dengan item per COA. Sistem tracking realisasi vs rencana dengan variance analysis.'],
                ['step' => 24, 'title' => 'Manajemen Aset', 'desc' => 'Catat aset perusahaan: kode, kategori, tanggal perolehan, harga, lokasi, PIC. Hitung penyusutan otomatis. Catat mutasi dan maintenance.'],
            ],
            'Fase 6: CRM' => [
                ['step' => 25, 'title' => 'Input Lead', 'desc' => 'Input leads dari berbagai sumber (Website, Referral, LinkedIn) di menu CRM > Leads. Assign ke sales, tracking pipeline stage.'],
                ['step' => 26, 'title' => 'Kelola Pipeline Deal', 'desc' => 'Atur pipeline stages (Prospecting → Qualification → Proposal → Negotiation → Won/Lost). Move deal antar stage dengan probability dan nilai deal.'],
                ['step' => 27, 'title' => 'Konversi ke Klien', 'desc' => 'Konversi lead won menjadi klien. Data otomatis terbawa. Kelola kontak klien, segmentasi (VIP, Regular), dan history interaksi.'],
                ['step' => 28, 'title' => 'Kirim WhatsApp Blast', 'desc' => 'Buat template WA dengan variabel dinamis. Kirim kampanye blast ke segmen klien via menu CRM > Kampanye Blast WA. Setup auto-reply berdasarkan keyword.'],
            ],
            'Fase 7: Project Management' => [
                ['step' => 29, 'title' => 'Buat Proyek', 'desc' => 'Buat proyek baru di menu Project > Proyek. Tentukan fase, milestone, dan anggota tim. Set timeline dan budget.'],
                ['step' => 30, 'title' => 'Assign Task', 'desc' => 'Assign task ke anggota tim dengan prioritas, label, deadline, dan dependency antar task (blocks/requires). Gunakan Kanban board untuk tracking visual.'],
                ['step' => 31, 'title' => 'Track Timesheet', 'desc' => 'Log jam kerja per task di menu Project > Timesheet. Submit timesheet untuk approval. Data otomatis masuk ke project costing.'],
            ],
            'Fase 8: POS' => [
                ['step' => 32, 'title' => 'Buka Shift Kasir', 'desc' => 'Kasir buka shift dengan saldo awal di menu POS > Shift Kasir. Sistem record waktu buka dan kasir yang bertugas.'],
                ['step' => 33, 'title' => 'Transaksi Harian', 'desc' => 'Input transaksi dengan pencarian produk/barcode. Proses pembayaran multi-metode (Cash, Debit, QRIS, Transfer). Terapkan diskon dan voucher.'],
                ['step' => 34, 'title' => 'Tutup Shift', 'desc' => 'Tutup shift di akhir hari. Sistem rekonsiliasi otomatis: expected cash vs actual. Selisih tercatat. Generate laporan shift.'],
            ],
            'Fase 9: Laporan & Monitoring' => [
                ['step' => 35, 'title' => 'Dashboard Overview', 'desc' => 'Pantau dashboard utama: ringkasan karyawan, absensi hari ini, pending approvals, revenue chart, task progress. Widget otomatis sesuai role login.'],
                ['step' => 36, 'title' => 'Generate Laporan Bisnis', 'desc' => 'Buka menu Laporan > Laporan Bisnis Utama. Filter per tanggal, group by harian/mingguan/bulanan. Export PDF/Excel.'],
                ['step' => 37, 'title' => 'Laporan Keuangan', 'desc' => 'Generate P&L (Profit & Loss), AR/AP Aging, expense by category. Chart interaktif dengan drill-down per akun.'],
                ['step' => 38, 'title' => 'Audit Log & Monitoring', 'desc' => 'Pantau semua perubahan data di menu Sistem > Audit Logs. Lihat old/new values, siapa yang mengubah, dan timestamp. Scheduled jobs monitoring untuk backup & notifikasi.'],
            ],
        ];
    }

    protected function features(): array
    {
        return [
            // ── Login & Dashboard ──
            ['group' => 'Login & Dashboard', 'title' => 'Halaman Login', 'screenshot' => '01-login.png', 'description' => 'Form login BizOS yang bersih dan profesional.'],
            ['group' => 'Login & Dashboard', 'title' => 'Dashboard', 'screenshot' => '02-dashboard.png', 'description' => 'Ringkasan bisnis real-time dengan widget sesuai role.'],

            // ── Master Data (7) ──
            ['group' => 'Master Data', 'title' => 'Perusahaan', 'screenshot' => '03-companies.png', 'description' => 'Kelola data perusahaan, multi-company support.'],
            ['group' => 'Master Data', 'title' => 'Cabang', 'screenshot' => '04-branches.png', 'description' => 'Daftar cabang/kantor per perusahaan.'],
            ['group' => 'Master Data', 'title' => 'Departemen', 'screenshot' => '05-departments.png', 'description' => 'Struktur organisasi hierarki.'],
            ['group' => 'Master Data', 'title' => 'Jabatan', 'screenshot' => '06-positions.png', 'description' => 'Daftar jabatan per departemen.'],
            ['group' => 'Master Data', 'title' => 'Designasi', 'screenshot' => '07-designations.png', 'description' => 'Level jabatan (Staff, Supervisor, Manager).'],
            ['group' => 'Master Data', 'title' => 'Grade', 'screenshot' => '08-grades.png', 'description' => 'Grade gaji dengan range min-max.'],
            ['group' => 'Master Data', 'title' => 'Karyawan', 'screenshot' => '09-employees.png', 'description' => 'Data lengkap karyawan, personal, kepegawaian, bank.'],

            // ── HRM (23) ──
            ['group' => 'HRM', 'title' => 'Shift', 'screenshot' => '10-shifts.png', 'description' => 'Atur shift kerja (Pagi, Siang, Malam).'],
            ['group' => 'HRM', 'title' => 'Absensi', 'screenshot' => '11-attendances.png', 'description' => 'Riwayat absensi, clock in/out, status.'],
            ['group' => 'HRM', 'title' => 'Konfigurasi Absensi', 'screenshot' => '12-attendance-configs.png', 'description' => 'Atur GPS, WiFi, selfie.'],
            ['group' => 'HRM', 'title' => 'WiFi Access Point', 'screenshot' => '13-wifi-access-points.png', 'description' => 'Daftar WiFi kantor untuk absensi.'],
            ['group' => 'HRM', 'title' => 'Tipe Cuti', 'screenshot' => '14-leave-types.png', 'description' => 'Kategori cuti dengan alokasi default.'],
            ['group' => 'HRM', 'title' => 'Cuti', 'screenshot' => '15-leaves.png', 'description' => 'Pengajuan, approval, dan tracking saldo cuti.'],
            ['group' => 'HRM', 'title' => 'Lembur', 'screenshot' => '16-overtimes.png', 'description' => 'Pengajuan lembur dengan kalkulasi rate multiplier.'],
            ['group' => 'HRM', 'title' => 'Kategori Reimbursement', 'screenshot' => '17-reimbursement-categories.png', 'description' => 'Tipe reimbursement (Transport, Makan, Hotel).'],
            ['group' => 'HRM', 'title' => 'Reimbursement', 'screenshot' => '18-reimbursements.png', 'description' => 'Klaim reimbursement dengan upload bukti.'],
            ['group' => 'HRM', 'title' => 'Kunjungan', 'screenshot' => '19-visits.png', 'description' => 'Tracking kunjungan lapangan.'],
            ['group' => 'HRM', 'title' => 'Lowongan', 'screenshot' => '20-job-postings.png', 'description' => 'Posting lowongan pekerjaan.'],
            ['group' => 'HRM', 'title' => 'Kandidat', 'screenshot' => '21-candidates.png', 'description' => 'Data pelamar dan screening.'],
            ['group' => 'HRM', 'title' => 'Interview', 'screenshot' => '22-interviews.png', 'description' => 'Jadwal interview rekrutmen.'],
            ['group' => 'HRM', 'title' => 'Pewawancara', 'screenshot' => '23-interviewers.png', 'description' => 'Daftar pewawancara per rekrutmen.'],
            ['group' => 'HRM', 'title' => 'Hasil Interview', 'screenshot' => '24-interview-results.png', 'description' => 'Skor dan keputusan interview.'],
            ['group' => 'HRM', 'title' => 'Siklus Feedback', 'screenshot' => '25-feedback-cycles.png', 'description' => 'Siklus review feedback 360.'],
            ['group' => 'HRM', 'title' => 'Pertanyaan Feedback', 'screenshot' => '26-feedback-questions.png', 'description' => 'Bank pertanyaan feedback per kompetensi.'],
            ['group' => 'HRM', 'title' => 'Reviewer Feedback', 'screenshot' => '27-feedback-reviewers.png', 'description' => 'Assign reviewer untuk feedback.'],
            ['group' => 'HRM', 'title' => 'Jawaban Feedback', 'screenshot' => '28-feedback-answers.png', 'description' => 'Jawaban dan skor hasil feedback.'],
            ['group' => 'HRM', 'title' => 'Menu Kantin', 'screenshot' => '29-canteen-menus.png', 'description' => 'Daftar menu kantin dengan harga.'],
            ['group' => 'HRM', 'title' => 'Pesanan Kantin', 'screenshot' => '30-canteen-orders.png', 'description' => 'Order makanan kantin dari karyawan.'],
            ['group' => 'HRM', 'title' => 'Item Pesanan Kantin', 'screenshot' => '31-canteen-order-items.png', 'description' => 'Detail item per pesanan kantin.'],
            ['group' => 'HRM', 'title' => 'Pengumuman', 'screenshot' => '32-announcements.png', 'description' => 'Pengumuman perusahaan dan karyawan.'],

            // ── Payroll (9) ──
            ['group' => 'Payroll', 'title' => 'Komponen Gaji', 'screenshot' => '33-salary-components.png', 'description' => 'Komponen pendapatan dan potongan.'],
            ['group' => 'Payroll', 'title' => 'Komponen Gaji Karyawan', 'screenshot' => '34-employee-salary-components.png', 'description' => 'Assign komponen gaji per karyawan.'],
            ['group' => 'Payroll', 'title' => 'Periode Payroll', 'screenshot' => '35-payroll-periods.png', 'description' => 'Periode penggajian bulanan.'],
            ['group' => 'Payroll', 'title' => 'Payroll', 'screenshot' => '36-payrolls.png', 'description' => 'Generate dan kelola payroll.'],
            ['group' => 'Payroll', 'title' => 'Item Payroll', 'screenshot' => '37-payroll-items.png', 'description' => 'Detail perhitungan per karyawan.'],
            ['group' => 'Payroll', 'title' => 'Slip Gaji', 'screenshot' => '38-pay-slips.png', 'description' => 'Slip gaji digital per karyawan.'],
            ['group' => 'Payroll', 'title' => 'Konfigurasi PPh21', 'screenshot' => '39-pph21-configs.png', 'description' => 'Tarif PPh21 progresif dan PTKP.'],
            ['group' => 'Payroll', 'title' => 'Konfigurasi BPJS', 'screenshot' => '40-bpjs-configs.png', 'description' => 'Iuran BPJS Ketenagakerjaan & Kesehatan.'],
            ['group' => 'Payroll', 'title' => 'Konfigurasi THR', 'screenshot' => '41-thr-configs.png', 'description' => 'Formula THR dan bonus.'],

            // ── Finance (19) ──
            ['group' => 'Finance', 'title' => 'Kategori COA', 'screenshot' => '42-coa-categories.png', 'description' => 'Kategori akun (Asset, Liability, Equity, Revenue, Expense).'],
            ['group' => 'Finance', 'title' => 'Chart of Accounts', 'screenshot' => '43-coa.png', 'description' => 'Daftar akun lengkap dengan kode.'],
            ['group' => 'Finance', 'title' => 'Saldo COA', 'screenshot' => '44-coa-balances.png', 'description' => 'Saldo awal dan saldo berjalan per akun.'],
            ['group' => 'Finance', 'title' => 'Jurnal', 'screenshot' => '45-journals.png', 'description' => 'Jurnal umum, penyesuaian, pembalik.'],
            ['group' => 'Finance', 'title' => 'Entry Jurnal', 'screenshot' => '46-journal-entries.png', 'description' => 'Debit/kredit per transaksi jurnal.'],
            ['group' => 'Finance', 'title' => 'Invoice', 'screenshot' => '47-invoices.png', 'description' => 'Invoice penjualan dan pembelian.'],
            ['group' => 'Finance', 'title' => 'Item Invoice', 'screenshot' => '48-invoice-items.png', 'description' => 'Detail item per invoice dengan jumlah dan harga.'],
            ['group' => 'Finance', 'title' => 'Metode Pembayaran', 'screenshot' => '49-payment-methods.png', 'description' => 'Cash, Transfer, QRIS, Debit.'],
            ['group' => 'Finance', 'title' => 'Pembayaran', 'screenshot' => '50-payments.png', 'description' => 'Transaksi pembayaran masuk dan keluar.'],
            ['group' => 'Finance', 'title' => 'Pembayaran Invoice', 'screenshot' => '51-invoice-payments.png', 'description' => 'Alokasi pembayaran ke invoice.'],
            ['group' => 'Finance', 'title' => 'Budget', 'screenshot' => '52-budgets.png', 'description' => 'Budget per departemen atau proyek.'],
            ['group' => 'Finance', 'title' => 'Item Budget', 'screenshot' => '53-budget-items.png', 'description' => 'Detail line item per budget.'],
            ['group' => 'Finance', 'title' => 'Konfigurasi Pajak', 'screenshot' => '54-tax-configs.png', 'description' => 'Setup tarif PPN, PPh, dan pajak lain.'],
            ['group' => 'Finance', 'title' => 'Transaksi Pajak', 'screenshot' => '55-tax-transactions.png', 'description' => 'Catatan pajak per transaksi.'],
            ['group' => 'Finance', 'title' => 'Kategori Aset', 'screenshot' => '56-asset-categories.png', 'description' => 'Klasifikasi aset (Tanah, Kendaraan, Elektronik).'],
            ['group' => 'Finance', 'title' => 'Aset', 'screenshot' => '57-assets.png', 'description' => 'Data aset perusahaan dengan lokasi dan PIC.'],
            ['group' => 'Finance', 'title' => 'Penyusutan Aset', 'screenshot' => '58-asset-depreciations.png', 'description' => 'Jadwal penyusutan otomatis.'],
            ['group' => 'Finance', 'title' => 'Mutasi Aset', 'screenshot' => '59-asset-mutations.png', 'description' => 'Perpindahan aset antar cabang.'],
            ['group' => 'Finance', 'title' => 'Maintenance Aset', 'screenshot' => '60-asset-maintenances.png', 'description' => 'Catatan perbaikan dan servis.'],

            // ── CRM (13) ──
            ['group' => 'CRM', 'title' => 'Sumber Lead', 'screenshot' => '61-lead-sources.png', 'description' => 'Kanal sumber lead (Website, Referral, LinkedIn).'],
            ['group' => 'CRM', 'title' => 'Leads', 'screenshot' => '62-leads.png', 'description' => 'Data leads dengan scoring dan follow-up.'],
            ['group' => 'CRM', 'title' => 'Aktivitas Lead', 'screenshot' => '63-lead-activities.png', 'description' => 'Log aktivitas per lead.'],
            ['group' => 'CRM', 'title' => 'Klien', 'screenshot' => '64-clients.png', 'description' => 'Konversi lead menjadi klien.'],
            ['group' => 'CRM', 'title' => 'Kontak Klien', 'screenshot' => '65-client-contacts.png', 'description' => 'Kontak person per klien.'],
            ['group' => 'CRM', 'title' => 'Segmentasi Klien', 'screenshot' => '66-client-segments.png', 'description' => 'Segmen klien (VIP, Regular, New).'],
            ['group' => 'CRM', 'title' => 'Tahap Pipeline', 'screenshot' => '67-pipeline-stages.png', 'description' => 'Stage pipeline (Prospecting → Won/Lost).'],
            ['group' => 'CRM', 'title' => 'Deals', 'screenshot' => '68-deals.png', 'description' => 'Tracking deal dengan nilai dan probability.'],
            ['group' => 'CRM', 'title' => 'Template WA', 'screenshot' => '69-wa-templates.png', 'description' => 'Template pesan WhatsApp dengan variabel.'],
            ['group' => 'CRM', 'title' => 'Kampanye Blast WA', 'screenshot' => '70-wa-blast-campaigns.png', 'description' => 'Kirim blast WhatsApp ke segmen klien.'],
            ['group' => 'CRM', 'title' => 'Log Blast WA', 'screenshot' => '71-wa-blast-logs.png', 'description' => 'Log pengiriman blast WhatsApp.'],
            ['group' => 'CRM', 'title' => 'Auto Reply WA', 'screenshot' => '72-wa-auto-replies.png', 'description' => 'Balasan otomatis berdasarkan keyword.'],
            ['group' => 'CRM', 'title' => 'Percakapan WA', 'screenshot' => '73-wa-conversations.png', 'description' => 'Riwayat percakapan WhatsApp.'],

            // ── Project (10) ──
            ['group' => 'Project', 'title' => 'Proyek', 'screenshot' => '74-projects.png', 'description' => 'Daftar proyek dengan timeline dan budget.'],
            ['group' => 'Project', 'title' => 'Fase Proyek', 'screenshot' => '75-project-phases.png', 'description' => 'Fase atau tahapan proyek.'],
            ['group' => 'Project', 'title' => 'Anggota Proyek', 'screenshot' => '76-project-members.png', 'description' => 'Tim yang terlibat per proyek.'],
            ['group' => 'Project', 'title' => 'Task', 'screenshot' => '77-tasks.png', 'description' => 'Task tracking dengan Kanban board.'],
            ['group' => 'Project', 'title' => 'Komentar Task', 'screenshot' => '78-task-comments.png', 'description' => 'Diskusi dan komentar per task.'],
            ['group' => 'Project', 'title' => 'Lampiran Task', 'screenshot' => '79-task-attachments.png', 'description' => 'File attachment per task.'],
            ['group' => 'Project', 'title' => 'Label Task', 'screenshot' => '80-task-labels.png', 'description' => 'Tag label untuk task.'],
            ['group' => 'Project', 'title' => 'Milestone', 'screenshot' => '81-milestones.png', 'description' => 'Target milestone per proyek.'],
            ['group' => 'Project', 'title' => 'Timesheet', 'screenshot' => '82-timesheets.png', 'description' => 'Log jam kerja dengan approval.'],
            ['group' => 'Project', 'title' => 'Entry Timesheet', 'screenshot' => '83-timesheet-entries.png', 'description' => 'Detail entry jam kerja per task.'],

            // ── POS (11) ──
            ['group' => 'POS', 'title' => 'Kategori Produk', 'screenshot' => '84-product-categories.png', 'description' => 'Klasifikasi produk POS.'],
            ['group' => 'POS', 'title' => 'Produk', 'screenshot' => '85-products.png', 'description' => 'Katalog produk dengan harga dan stok.'],
            ['group' => 'POS', 'title' => 'Varian Produk', 'screenshot' => '86-product-variants.png', 'description' => 'Varian ukuran, warna, rasa.'],
            ['group' => 'POS', 'title' => 'Diskon Produk', 'screenshot' => '87-product-discounts.png', 'description' => 'Diskon per produk atau kategori.'],
            ['group' => 'POS', 'title' => 'Member POS', 'screenshot' => '88-pos-members.png', 'description' => 'Data member dengan poin loyalty.'],
            ['group' => 'POS', 'title' => 'Voucher POS', 'screenshot' => '89-pos-vouchers.png', 'description' => 'Voucher diskon dengan syarat.'],
            ['group' => 'POS', 'title' => 'Shift Kasir', 'screenshot' => '90-cashier-shifts.png', 'description' => 'Buka/tutup shift kasir.'],
            ['group' => 'POS', 'title' => 'Transaksi POS', 'screenshot' => '91-pos-transactions.png', 'description' => 'Transaksi penjualan kasir.'],
            ['group' => 'POS', 'title' => 'Item Transaksi', 'screenshot' => '92-pos-transaction-items.png', 'description' => 'Detail item per transaksi.'],
            ['group' => 'POS', 'title' => 'Pembayaran POS', 'screenshot' => '93-pos-payments.png', 'description' => 'Multi-metode pembayaran.'],
            ['group' => 'POS', 'title' => 'Refund POS', 'screenshot' => '94-pos-refunds.png', 'description' => 'Pengembalian dana dengan approval.'],

            // ── Kolaborasi (9) ──
            ['group' => 'Kolaborasi', 'title' => 'Chat', 'screenshot' => '95-chats.png', 'description' => 'Chat real-time personal dan grup.'],
            ['group' => 'Kolaborasi', 'title' => 'Meeting', 'screenshot' => '96-meetings.png', 'description' => 'Jadwal meeting dengan Google Meet/Zoom.'],
            ['group' => 'Kolaborasi', 'title' => 'Peserta Meeting', 'screenshot' => '97-meeting-attendees.png', 'description' => 'Daftar hadir meeting.'],
            ['group' => 'Kolaborasi', 'title' => 'Risalah Meeting', 'screenshot' => '98-meeting-minutes.png', 'description' => 'Notulen dan action items.'],
            ['group' => 'Kolaborasi', 'title' => 'Kalender', 'screenshot' => '99-calendars.png', 'description' => 'Kalender bersama perusahaan.'],
            ['group' => 'Kolaborasi', 'title' => 'Event Kalender', 'screenshot' => '100-calendar-events.png', 'description' => 'Event dengan reminder.'],
            ['group' => 'Kolaborasi', 'title' => 'Form Builder', 'screenshot' => '101-forms.png', 'description' => 'Pembuatan form drag-and-drop.'],
            ['group' => 'Kolaborasi', 'title' => 'Field Form', 'screenshot' => '102-form-fields.png', 'description' => 'Konfigurasi field per form.'],
            ['group' => 'Kolaborasi', 'title' => 'Submission Form', 'screenshot' => '103-form-submissions.png', 'description' => 'Data hasil submit form.'],

            // ── LMS (6) ──
            ['group' => 'LMS', 'title' => 'Kursus', 'screenshot' => '104-courses.png', 'description' => 'Katalog kursus internal.'],
            ['group' => 'LMS', 'title' => 'Modul Kursus', 'screenshot' => '105-course-modules.png', 'description' => 'Struktur modul per kursus.'],
            ['group' => 'LMS', 'title' => 'Lesson', 'screenshot' => '106-course-lessons.png', 'description' => 'Konten lesson (text, video, PDF, quiz).'],
            ['group' => 'LMS', 'title' => 'Enrollment', 'screenshot' => '107-course-enrollments.png', 'description' => 'Pendaftaran dan progress kursus.'],
            ['group' => 'LMS', 'title' => 'Quiz', 'screenshot' => '108-quizzes.png', 'description' => 'Bank quiz interaktif.'],
            ['group' => 'LMS', 'title' => 'Pertanyaan Quiz', 'screenshot' => '109-quiz-questions.png', 'description' => 'Soal multiple choice, essay, true/false.'],

            // ── AI Assistant (3) ──
            ['group' => 'AI Assistant', 'title' => 'Provider AI', 'screenshot' => '110-ai-providers.png', 'description' => 'Konfigurasi provider AI (OpenAI, Gemini, Ollama).'],
            ['group' => 'AI Assistant', 'title' => 'Percakapan AI', 'screenshot' => '111-ai-conversations.png', 'description' => 'Riwayat chat AI per konteks.'],
            ['group' => 'AI Assistant', 'title' => 'Knowledge Base AI', 'screenshot' => '112-ai-knowledge-bases.png', 'description' => 'Basis pengetahuan untuk RAG.'],

            // ── Laporan (3) ──
            ['group' => 'Laporan', 'title' => 'Laporan Bisnis', 'screenshot' => '113-laporan-bisnis.png', 'description' => 'Revenue, sales, profit margin.'],
            ['group' => 'Laporan', 'title' => 'Laporan Keuangan', 'screenshot' => '114-laporan-keuangan.png', 'description' => 'P&L, AR/AP aging, expense.'],
            ['group' => 'Laporan', 'title' => 'Laporan Operasional', 'screenshot' => '115-laporan-operasional.png', 'description' => 'Absensi, project, POS.'],

            // ── Sistem (7) ──
            ['group' => 'Sistem', 'title' => 'Roles', 'screenshot' => '116-roles.png', 'description' => 'Role-based access control.'],
            ['group' => 'Sistem', 'title' => 'Permission Resources', 'screenshot' => '117-permissions.png', 'description' => 'Permission per resource.'],
            ['group' => 'Sistem', 'title' => 'Template Notifikasi', 'screenshot' => '118-notification-templates.png', 'description' => 'Template email, in-app, WA.'],
            ['group' => 'Sistem', 'title' => 'Notifikasi', 'screenshot' => '119-notifications.png', 'description' => 'Log notifikasi terkirim.'],
            ['group' => 'Sistem', 'title' => 'Audit Log', 'screenshot' => '120-audit-logs.png', 'description' => 'Log perubahan data dengan old/new values.'],
            ['group' => 'Sistem', 'title' => 'Pengaturan Sistem', 'screenshot' => '121-system-settings.png', 'description' => 'Konfigurasi global sistem.'],
            ['group' => 'Sistem', 'title' => 'Integrasi', 'screenshot' => '122-integrations.png', 'description' => 'Integrasi dengan sistem eksternal.'],
        ];
    }

    protected function seoMeta(): array
    {
        $url = url('/docs');

        return [
            'title' => 'Dokumentasi BizOS — Business Operating System 150+ Fitur',
            'description' => 'Dokumentasi lengkap BizOS: tutorial langkah demi langkah, demo account, struktur menu, dan fitur lengkap HRM, Accounting, CRM, Project, POS, LMS, AI.',
            'canonical' => $url,
            'og_title' => 'Dokumentasi BizOS — Tutorial & Fitur Lengkap',
            'og_description' => 'Pelajari cara menggunakan BizOS dari setup awal hingga laporan. 35+ langkah tutorial, demo account, dan penjelasan fitur.',
            'og_image' => url('/marketing/screens/bizos-dashboard.png'),
            'og_url' => $url,
            'twitter_card' => 'summary_large_image',
            'jsonld' => [
                '@context' => 'https://schema.org',
                '@type' => 'SoftwareApplication',
                'name' => 'BizOS — Business Operating System',
                'applicationCategory' => 'BusinessApplication',
                'operatingSystem' => 'Web',
                'description' => 'All-in-one business OS: HRM, Accounting, CRM, Project Management, POS, AI Assistant, Collaboration — 150+ fitur.',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'IDR',
                ],
            ],
        ];
    }
}
