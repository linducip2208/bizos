<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class DocumentTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Surat Perjanjian Kerja (PKWT)',
                'type' => 'contract',
                'module' => 'employee',
                'content' => $this->getPkwtContent(),
                'variables' => json_encode(['employee.name', 'employee.position', 'employee.salary', 'company.name', 'company.address', 'company.tax_id', 'date.today', 'employee.id_number', 'employee.address', 'employee.join_date', 'employee.contract_end']),
            ],
            [
                'name' => 'Surat Penawaran Harga',
                'type' => 'offer_letter',
                'module' => 'deal',
                'content' => $this->getOfferContent(),
                'variables' => json_encode(['client.name', 'deal.amount', 'company.name', 'company.address', 'company.phone', 'date.today', 'client.address', 'deal.title']),
            ],
            [
                'name' => 'Surat Peringatan (SP)',
                'type' => 'warning_letter',
                'module' => 'warning',
                'content' => $this->getWarningContent(),
                'variables' => json_encode(['employee.name', 'warning.reason', 'warning.level', 'warning.date', 'company.name', 'employee.position', 'employee.department']),
            ],
            [
                'name' => 'Sertifikat Pelatihan',
                'type' => 'certificate',
                'module' => 'course',
                'content' => $this->getCertificateContent(),
                'variables' => json_encode(['employee.name', 'course.name', 'course.completion_date', 'company.name', 'date.today']),
            ],
            [
                'name' => 'Invoice Template',
                'type' => 'invoice_custom',
                'module' => 'invoice',
                'content' => $this->getInvoiceContent(),
                'variables' => json_encode(['client.name', 'invoice.number', 'invoice.total', 'invoice.date', 'invoice.due_date', 'company.name', 'company.address', 'company.tax_id', 'client.address', 'client.tax_id', 'date.today', 'invoice.subtotal', 'invoice.tax_amount']),
            ],
        ];

        foreach ($templates as $template) {
            DocumentTemplate::updateOrCreate(
                ['name' => $template['name'], 'company_id' => 1],
                array_merge($template, [
                    'company_id' => 1,
                    'is_active' => true,
                    'created_by' => 1,
                ])
            );
        }
    }

    protected function getPkwtContent(): string
    {
        return <<<HTML
<div class="header">
    <h1>SURAT PERJANJIAN KERJA WAKTU TERTENTU (PKWT)</h1>
    <p style="text-align: center; color: #6b7280;">Nomor: 001/PKWT/{{company.name}}/{{date.today}}</p>
</div>

<p>Pada hari ini, tanggal <strong>{{date.today}}</strong>, yang bertanda tangan di bawah ini:</p>

<h2>Pihak Pertama (Perusahaan):</h2>
<table>
    <tr><td style="width: 200px;">Nama Perusahaan</td><td>: <strong>{{company.name}}</strong></td></tr>
    <tr><td>Alamat</td><td>: {{company.address}}</td></tr>
    <tr><td>NPWP</td><td>: {{company.tax_id}}</td></tr>
</table>

<h2>Pihak Kedua (Karyawan):</h2>
<table>
    <tr><td style="width: 200px;">Nama Lengkap</td><td>: <strong>{{employee.name}}</strong></td></tr>
    <tr><td>No. KTP</td><td>: {{employee.id_number}}</td></tr>
    <tr><td>Alamat</td><td>: {{employee.address}}</td></tr>
</table>

<p>Kedua belah pihak sepakat mengadakan Perjanjian Kerja Waktu Tertentu (PKWT) dengan ketentuan sebagai berikut:</p>

<h2>Pasal 1 — Jabatan dan Tugas</h2>
<p>Pihak Pertama menerima Pihak Kedua untuk bekerja sebagai <strong>{{employee.position}}</strong> dengan tugas dan tanggung jawab sesuai uraian pekerjaan yang ditetapkan oleh Pihak Pertama.</p>

<h2>Pasal 2 — Masa Kerja</h2>
<p>Perjanjian kerja ini berlaku untuk waktu tertentu, terhitung mulai tanggal <strong>{{employee.join_date}}</strong> sampai dengan <strong>{{employee.contract_end}}</strong>.</p>

<h2>Pasal 3 — Gaji dan Tunjangan</h2>
<p>Pihak Pertama akan memberikan gaji pokok sebesar <strong>Rp {{employee.salary}}</strong> per bulan, dibayarkan setiap akhir bulan sesuai kebijakan perusahaan.</p>

<h2>Pasal 4 — Hak dan Kewajiban</h2>
<p>Pihak Kedua wajib melaksanakan tugas dengan penuh tanggung jawab, mematuhi peraturan perusahaan, dan menjaga kerahasiaan data perusahaan.</p>

<h2>Pasal 5 — Pemutusan Hubungan Kerja</h2>
<p>Perjanjian ini dapat diputus oleh salah satu pihak dengan pemberitahuan tertulis sesuai ketentuan yang berlaku.</p>

<p style="margin-top: 40px;">Demikian perjanjian ini dibuat rangkap 2 (dua) dengan materai cukup untuk masing-masing pihak.</p>

<div class="signature-section">
    <div class="signature-box">
        <p><strong>Pihak Pertama</strong></p>
        <p>{{company.name}}</p>
        <div class="signature-line">(Direktur)</div>
    </div>
    <div class="signature-box">
        <p><strong>Pihak Kedua</strong></p>
        <p>{{employee.name}}</p>
        <div class="signature-line">(Karyawan)</div>
    </div>
</div>
HTML;
    }

    protected function getOfferContent(): string
    {
        return <<<HTML
<div class="header">
    <h1>SURAT PENAWARAN HARGA</h1>
    <p style="text-align: center; color: #6b7280;">No: SPH-{{date.today}}</p>
</div>

<table>
    <tr><td style="width: 180px;">Kepada Yth.</td><td>: <strong>{{client.name}}</strong></td></tr>
    <tr><td>Alamat</td><td>: {{client.address}}</td></tr>
    <tr><td>Tanggal</td><td>: {{date.today}}</td></tr>
</table>

<p>Dengan hormat,</p>

<p>Menindaklanjuti permintaan penawaran dari {{client.name}}, bersama ini kami <strong>{{company.name}}</strong> mengajukan penawaran harga untuk:</p>

<p><strong>{{deal.title}}</strong></p>

<p>dengan nilai penawaran sebesar:</p>

<p style="font-size: 16pt; font-weight: 700; color: #4f46e5; text-align: center; padding: 16px;">
    Rp {{deal.amount}}
</p>

<p>Harga tersebut sudah termasuk PPN 11% dan berlaku selama 14 (empat belas) hari kalender sejak tanggal surat ini diterbitkan.</p>

<p>Ketentuan pembayaran: Transfer 50% di muka, 50% setelah pekerjaan selesai.</p>

<p>Demikian penawaran ini kami sampaikan. Atas perhatian dan kerjasamanya, kami ucapkan terima kasih.</p>

<div class="signature-section">
    <div class="signature-box">
        <p><strong>Hormat Kami,</strong></p>
        <p>{{company.name}}</p>
        <div class="signature-line">(Direktur)</div>
    </div>
</div>

<div style="margin-top: 40px; padding: 12px; background: #f9fafb; border-radius: 8px; font-size: 10pt; color: #6b7280;">
    <strong>Kontak:</strong><br>
    {{company.name}}<br>
    {{company.address}}<br>
    Telp: {{company.phone}}
</div>
HTML;
    }

    protected function getWarningContent(): string
    {
        return <<<HTML
<div class="header">
    <h1>SURAT PERINGATAN ({{warning.level}})</h1>
    <p style="text-align: center; color: #6b7280;">No: SP-{{warning.date}}</p>
</div>

<table>
    <tr><td style="width: 180px;">Nama Karyawan</td><td>: <strong>{{employee.name}}</strong></td></tr>
    <tr><td>Jabatan</td><td>: {{employee.position}}</td></tr>
    <tr><td>Departemen</td><td>: {{employee.department}}</td></tr>
    <tr><td>Tanggal</td><td>: {{warning.date}}</td></tr>
</table>

<p>Dengan ini perusahaan memberikan <strong>Surat Peringatan {{warning.level}}</strong> kepada Saudara/i <strong>{{employee.name}}</strong> atas pelanggaran berikut:</p>

<div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin: 16px 0;">
    <p><strong>Pelanggaran:</strong></p>
    <p>{{warning.reason}}</p>
</div>

<p>Kami mengharapkan Saudara/i segera memperbaiki kinerja dan sikap sesuai dengan peraturan perusahaan yang berlaku.</p>

<p>Apabila dalam jangka waktu 6 (enam) bulan ke depan masih terjadi pelanggaran serupa, maka perusahaan akan memberikan sanksi tegas sesuai peraturan yang berlaku, termasuk pemberian Surat Peringatan berikutnya hingga Pemutusan Hubungan Kerja (PHK).</p>

<p>Demikian surat peringatan ini disampaikan untuk menjadi perhatian bersama.</p>

<div class="signature-section">
    <div class="signature-box">
        <p><strong>Manajemen</strong></p>
        <p>{{company.name}}</p>
        <div class="signature-line">(HR Manager)</div>
    </div>
    <div class="signature-box">
        <p><strong>Karyawan</strong></p>
        <p>{{employee.name}}</p>
        <div class="signature-line">(Tanda Tangan)</div>
    </div>
</div>
HTML;
    }

    protected function getCertificateContent(): string
    {
        return <<<HTML
<div style="border: 4px double #4f46e5; padding: 40px; text-align: center; min-height: 500px; display: flex; flex-direction: column; justify-content: center;">
    <p style="font-size: 10pt; color: #6b7280; text-transform: uppercase; letter-spacing: 4px; margin-bottom: 8px;">SERTIFIKAT</p>
    <h1 style="font-size: 24pt; color: #4f46e5; margin-bottom: 24px;">PENGHARGAAN</h1>

    <p style="font-size: 12pt; margin-bottom: 8px;">Diberikan kepada:</p>
    <p style="font-size: 20pt; font-weight: 700; color: #111827; margin-bottom: 24px;">{{employee.name}}</p>

    <p style="font-size: 12pt; margin-bottom: 8px;">Atas keberhasilan menyelesaikan:</p>
    <p style="font-size: 16pt; font-weight: 600; color: #4f46e5; margin-bottom: 24px;">{{course.name}}</p>

    <p style="font-size: 11pt; color: #374151; margin-bottom: 8px;">Diselenggarakan oleh:</p>
    <p style="font-size: 14pt; font-weight: 600; margin-bottom: 24px;">{{company.name}}</p>

    <p style="font-size: 11pt;">Tanggal penyelesaian: <strong>{{course.completion_date}}</strong></p>

    <div style="margin-top: 60px; text-align: right;">
        <p style="margin-bottom: 0;">Dikeluarkan di: Jakarta</p>
        <p>Tanggal: {{date.today}}</p>
        <div style="margin-top: 40px; border-top: 1px solid #000; width: 200px; margin-left: auto; padding-top: 8px;">
            <strong>{{company.name}}</strong>
        </div>
    </div>
</div>
HTML;
    }

    protected function getInvoiceContent(): string
{
    return <<<HTML
<div class="header" style="display: flex; justify-content: space-between; align-items: start;">
    <div>
        <h1 style="margin: 0;">INVOICE</h1>
        <p style="font-size: 11pt; color: #6b7280;">{{invoice.number}}</p>
    </div>
    <div style="text-align: right;">
        <p style="font-weight: 700; font-size: 14pt;">{{company.name}}</p>
        <p style="font-size: 9pt; color: #6b7280;">{{company.address}}</p>
        <p style="font-size: 9pt; color: #6b7280;">NPWP: {{company.tax_id}}</p>
    </div>
</div>

<h2>Kepada:</h2>
<table>
    <tr><td style="width: 100px;">Nama</td><td>: <strong>{{client.name}}</strong></td></tr>
    <tr><td>Alamat</td><td>: {{client.address}}</td></tr>
    <tr><td>NPWP</td><td>: {{client.tax_id}}</td></tr>
</table>

<table style="margin-top: 24px;">
    <tr><td style="width: 200px;">Tanggal Invoice</td><td>: {{invoice.date}}</td></tr>
    <tr><td>Jatuh Tempo</td><td>: {{invoice.due_date}}</td></tr>
</table>

<h2>Rincian:</h2>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Deskripsi</th>
            <th style="text-align: right;">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Jasa Layanan</td>
            <td style="text-align: right;">Rp {{invoice.subtotal}}</td>
        </tr>
    </tbody>
</table>

<table style="margin-top: 16px;">
    <tr><td style="width: 200px;">Subtotal</td><td style="text-align: right;">Rp {{invoice.subtotal}}</td></tr>
    <tr><td>PPN 11%</td><td style="text-align: right;">Rp {{invoice.tax_amount}}</td></tr>
    <tr style="font-weight: 700; font-size: 13pt;"><td>TOTAL</td><td style="text-align: right;">Rp {{invoice.total}}</td></tr>
</table>

<div style="margin-top: 40px; padding: 16px; background: #f9fafb; border-radius: 8px; font-size: 9pt;">
    <p><strong>Pembayaran melalui transfer ke:</strong></p>
    <p>Bank: BCA | No. Rek: 1234567890 | a.n. {{company.name}}</p>
</div>

<div class="signature-section">
    <div class="signature-box">
        <p><strong>Hormat Kami,</strong></p>
        <div class="signature-line">{{company.name}}</div>
    </div>
    <div class="signature-box">
        <p><strong>Diterima Oleh,</strong></p>
        <div class="signature-line">{{client.name}}</div>
    </div>
</div>
HTML;
    }
}
