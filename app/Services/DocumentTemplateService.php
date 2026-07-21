<?php

namespace App\Services;

use App\Models\Company;
use App\Models\DocumentGeneration;
use App\Models\DocumentTemplate;
use App\Models\Employee;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Course;
use App\Models\Deal;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentTemplateService
{
    protected array $moduleVariables = [
        'employee' => [
            'employee.name' => 'Nama lengkap karyawan',
            'employee.first_name' => 'Nama depan',
            'employee.last_name' => 'Nama belakang',
            'employee.email' => 'Email karyawan',
            'employee.phone' => 'Nomor telepon',
            'employee.position' => 'Nama jabatan/posisi',
            'employee.department' => 'Nama departemen',
            'employee.branch' => 'Nama cabang',
            'employee.salary' => 'Gaji pokok',
            'employee.join_date' => 'Tanggal bergabung',
            'employee.employee_type' => 'Tipe karyawan',
            'employee.id_number' => 'Nomor KTP',
            'employee.tax_number' => 'NPWP',
            'employee.address' => 'Alamat',
            'employee.gender' => 'Jenis kelamin',
            'employee.birth_date' => 'Tanggal lahir',
            'employee.religion' => 'Agama',
            'employee.marital_status' => 'Status pernikahan',
        ],
        'invoice' => [
            'invoice.number' => 'Nomor invoice',
            'invoice.date' => 'Tanggal invoice',
            'invoice.due_date' => 'Jatuh tempo',
            'invoice.total' => 'Total invoice',
            'invoice.subtotal' => 'Subtotal',
            'invoice.tax_amount' => 'Jumlah pajak',
            'invoice.status' => 'Status invoice',
            'client.name' => 'Nama klien',
            'client.address' => 'Alamat klien',
            'client.phone' => 'Telepon klien',
            'client.email' => 'Email klien',
            'client.tax_id' => 'NPWP klien',
        ],
        'project' => [
            'project.name' => 'Nama proyek',
            'project.description' => 'Deskripsi proyek',
            'project.start_date' => 'Tanggal mulai',
            'project.end_date' => 'Tanggal selesai',
            'project.budget' => 'Anggaran proyek',
            'project.status' => 'Status proyek',
            'client.name' => 'Nama klien',
            'client.address' => 'Alamat klien',
        ],
        'deal' => [
            'deal.title' => 'Judul deal',
            'deal.amount' => 'Nilai deal',
            'deal.status' => 'Status deal',
            'deal.expected_close_date' => 'Tanggal perkiraan tutup',
            'client.name' => 'Nama klien',
            'client.address' => 'Alamat klien',
            'client.phone' => 'Telepon klien',
            'client.email' => 'Email klien',
        ],
        'course' => [
            'course.name' => 'Nama kursus/pelatihan',
            'course.description' => 'Deskripsi kursus',
            'course.completion_date' => 'Tanggal selesai',
            'course.duration_minutes' => 'Durasi (menit)',
            'employee.name' => 'Nama peserta',
        ],
        'warning' => [
            'warning.reason' => 'Alasan peringatan',
            'warning.level' => 'Level peringatan',
            'warning.date' => 'Tanggal peringatan',
            'employee.name' => 'Nama karyawan',
            'employee.position' => 'Jabatan',
            'employee.department' => 'Departemen',
        ],
        'custom' => [],
    ];

    protected array $companyVariables = [
        'company.name' => 'Nama perusahaan',
        'company.address' => 'Alamat perusahaan',
        'company.phone' => 'Telepon perusahaan',
        'company.email' => 'Email perusahaan',
        'company.website' => 'Website perusahaan',
        'company.tax_id' => 'NPWP perusahaan',
        'date.today' => 'Tanggal hari ini',
        'date.now' => 'Tanggal & waktu sekarang',
    ];

    public function parseVariables(string $content): array
    {
        preg_match_all('/\{\{([a-zA-Z0-9_.]+)\}\}/', $content, $matches);
        return array_unique($matches[1] ?? []);
    }

    public function getPlaceholderData(string $module, int $moduleId): array
    {
        $data = $this->getCompanyData();
        $data['date.today'] = now()->format('d F Y');
        $data['date.now'] = now()->format('d F Y H:i');

        $moduleData = $this->resolveModuleData($module, $moduleId);
        return array_merge($data, $moduleData);
    }

    public function generate(DocumentTemplate $template, string $module, int $moduleId): string
    {
        $data = $this->getPlaceholderData($module, $moduleId);
        $content = $this->replacePlaceholders($template->content, $data);

        $pdf = Pdf::loadHTML($this->wrapHtml($content, $template->name))
            ->setPaper('a4')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        $filename = 'documents/' . $template->id . '/' . Str::uuid() . '.pdf';
        Storage::disk('public')->put($filename, $pdf->output());

        $doc = DocumentGeneration::create([
            'template_id' => $template->id,
            'module' => $module,
            'module_id' => $moduleId,
            'generated_by' => auth()->id(),
            'file_path' => $filename,
            'status' => 'generated',
        ]);

        return $filename;
    }

    public function preview(DocumentTemplate $template): string
    {
        $data = $this->getSampleData();
        return $this->replacePlaceholders($template->content, $data);
    }

    public function batchGenerate(DocumentTemplate $template, array $moduleIds): array
    {
        $results = [];
        foreach ($moduleIds as $moduleId) {
            try {
                $path = $this->generate($template, $template->module, $moduleId);
                $results[$moduleId] = ['success' => true, 'path' => $path];
            } catch (\Exception $e) {
                $results[$moduleId] = ['success' => false, 'error' => $e->getMessage()];
            }
        }
        return $results;
    }

    public function sendForSignature(DocumentGeneration $doc, string $signerEmail, string $signerName): void
    {
        $doc->update([
            'status' => 'sent',
            'signature_provider' => 'manual',
        ]);
    }

    public function checkSignatureStatus(DocumentGeneration $doc): string
    {
        if ($doc->signed_at) {
            return 'signed';
        }

        $sigRequest = $doc->signatureRequests()->latest()->first();
        return $sigRequest ? $sigRequest->status : 'draft';
    }

    public function getModuleVariables(): array
    {
        $all = $this->companyVariables;

        foreach ($this->moduleVariables as $module => $vars) {
            foreach ($vars as $key => $description) {
                $all[$key] = $description;
            }
        }

        return $all;
    }

    public function getVariablesForModule(string $module): array
    {
        $vars = $this->companyVariables;
        $moduleVars = $this->moduleVariables[$module] ?? $this->moduleVariables['custom'];
        return array_merge($vars, $moduleVars);
    }

    protected function resolveModuleData(string $module, int $moduleId): array
    {
        $data = [];

        $moduleVars = $this->moduleVariables[$module] ?? [];

        switch ($module) {
            case 'employee':
                $record = Employee::with(['position', 'department', 'branch', 'grade'])->find($moduleId);
                if ($record) {
                    $data['employee.name'] = trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? ''));
                    $data['employee.first_name'] = $record->first_name ?? '';
                    $data['employee.last_name'] = $record->last_name ?? '';
                    $data['employee.email'] = $record->email ?? '';
                    $data['employee.phone'] = $record->phone ?? '';
                    $data['employee.position'] = $record->position->name ?? '';
                    $data['employee.department'] = $record->department->name ?? '';
                    $data['employee.branch'] = $record->branch->name ?? '';
                    $data['employee.salary'] = number_format($record->basic_salary ?? 0, 0, ',', '.');
                    $data['employee.join_date'] = $record->join_date?->format('d F Y') ?? '';
                    $data['employee.employee_type'] = $this->translateEmployeeType($record->employee_type ?? '');
                    $data['employee.id_number'] = $record->id_number ?? '';
                    $data['employee.tax_number'] = $record->tax_number ?? '';
                    $data['employee.address'] = $record->address ?? '';
                    $data['employee.gender'] = $record->gender === 'male' ? 'Laki-laki' : 'Perempuan';
                    $data['employee.birth_date'] = $record->birth_date?->format('d F Y') ?? '';
                    $data['employee.religion'] = $record->religion ?? '';
                    $data['employee.marital_status'] = $this->translateMaritalStatus($record->marital_status ?? '');
                }
                break;

            case 'invoice':
                $record = Invoice::with(['company'])->find($moduleId);
                if ($record) {
                    $data['invoice.number'] = $record->invoice_number ?? '';
                    $data['invoice.date'] = $record->invoice_date?->format('d F Y') ?? '';
                    $data['invoice.due_date'] = $record->due_date?->format('d F Y') ?? '';
                    $data['invoice.total'] = number_format($record->total ?? 0, 0, ',', '.');
                    $data['invoice.subtotal'] = number_format($record->subtotal ?? 0, 0, ',', '.');
                    $data['invoice.tax_amount'] = number_format($record->tax_amount ?? 0, 0, ',', '.');
                    $data['invoice.status'] = $this->translateInvoiceStatus($record->status ?? '');

                    $client = Client::find($record->reference_id);
                    if ($client && in_array($record->reference_entity, ['Client', 'App\Models\Client'])) {
                        $data['client.name'] = $client->name ?? '';
                        $data['client.address'] = $client->address ?? '';
                        $data['client.phone'] = $client->phone ?? '';
                        $data['client.email'] = $client->email ?? '';
                        $data['client.tax_id'] = $client->tax_id ?? '';
                    }
                }
                break;

            case 'project':
                $record = Project::with(['client'])->find($moduleId);
                if ($record) {
                    $data['project.name'] = $record->name ?? '';
                    $data['project.description'] = $record->description ?? '';
                    $data['project.start_date'] = $record->start_date?->format('d F Y') ?? '';
                    $data['project.end_date'] = $record->end_date?->format('d F Y') ?? '';
                    $data['project.budget'] = number_format($record->budget ?? 0, 0, ',', '.');
                    $data['project.status'] = $this->translateProjectStatus($record->status ?? '');

                    if ($record->client) {
                        $data['client.name'] = $record->client->name ?? '';
                        $data['client.address'] = $record->client->address ?? '';
                    }
                }
                break;

            case 'deal':
                $record = Deal::with(['client'])->find($moduleId);
                if ($record) {
                    $data['deal.title'] = $record->title ?? '';
                    $data['deal.amount'] = number_format($record->amount ?? 0, 0, ',', '.');
                    $data['deal.status'] = $record->status ?? '';
                    $data['deal.expected_close_date'] = $record->expected_close_date?->format('d F Y') ?? '';

                    if ($record->client) {
                        $data['client.name'] = $record->client->name ?? '';
                        $data['client.address'] = $record->client->address ?? '';
                        $data['client.phone'] = $record->client->phone ?? '';
                        $data['client.email'] = $record->client->email ?? '';
                    }
                }
                break;

            case 'course':
                $record = Course::find($moduleId);
                if ($record) {
                    $data['course.name'] = $record->title ?? '';
                    $data['course.description'] = $record->description ?? '';
                    $data['course.duration_minutes'] = (string) ($record->duration_minutes ?? 0);
                    $data['course.completion_date'] = now()->format('d F Y');
                }
                break;

            case 'warning':
                $data['warning.date'] = now()->format('d F Y');
                break;
        }

        return $data;
    }

    protected function getCompanyData(): array
    {
        $company = Company::first();
        if (!$company) {
            return [
                'company.name' => 'PT Maju Bersama',
                'company.address' => 'Jl. Jenderal Sudirman No. 123, Jakarta Selatan',
                'company.phone' => '021-5555-6789',
                'company.email' => 'info@majubersama.co.id',
                'company.website' => 'https://majubersama.co.id',
                'company.tax_id' => '01.234.567.8-012.000',
            ];
        }

        return [
            'company.name' => $company->name,
            'company.address' => $company->address ?? '',
            'company.phone' => $company->phone ?? '',
            'company.email' => $company->email ?? '',
            'company.website' => $company->website ?? '',
            'company.tax_id' => $company->tax_id ?? '',
        ];
    }

    protected function getSampleData(): array
    {
        $data = $this->getCompanyData();
        $data['date.today'] = now()->format('d F Y');
        $data['date.now'] = now()->format('d F Y H:i');

        $sampleEmployee = [
            'employee.name' => 'Budi Santoso',
            'employee.first_name' => 'Budi',
            'employee.last_name' => 'Santoso',
            'employee.email' => 'budi@maju.test',
            'employee.phone' => '08123456789',
            'employee.position' => 'Direktur Utama',
            'employee.department' => 'Direksi',
            'employee.branch' => 'Kantor Pusat Jakarta',
            'employee.salary' => '35.000.000',
            'employee.join_date' => '15 Januari 2020',
            'employee.employee_type' => 'Tetap',
            'employee.id_number' => '3174XXXXXXXXXXXX',
            'employee.tax_number' => '12.345.678.0-012.000',
            'employee.address' => 'Jl. Contoh No. 123',
            'employee.gender' => 'Laki-laki',
            'employee.birth_date' => '01 Januari 1990',
            'employee.religion' => 'Islam',
            'employee.marital_status' => 'Menikah',
        ];

        $sampleInvoice = [
            'invoice.number' => 'INV-2026-001',
            'invoice.date' => now()->format('d F Y'),
            'invoice.due_date' => now()->addDays(30)->format('d F Y'),
            'invoice.total' => '10.000.000',
            'invoice.subtotal' => '9.000.000',
            'invoice.tax_amount' => '1.000.000',
            'invoice.status' => 'Belum Dibayar',
            'client.name' => 'PT Contoh Klien',
            'client.address' => 'Jl. Contoh No. 456, Jakarta',
            'client.phone' => '021-1234-5678',
            'client.email' => 'klien@contoh.co.id',
            'client.tax_id' => '99.888.777.6-555.000',
        ];

        $sampleProject = [
            'project.name' => 'Proyek Implementasi ERP',
            'project.description' => 'Implementasi sistem ERP terintegrasi',
            'project.start_date' => now()->subMonths(2)->format('d F Y'),
            'project.end_date' => now()->addMonths(4)->format('d F Y'),
            'project.budget' => '500.000.000',
            'project.status' => 'Dalam Pengerjaan',
            'client.name' => 'PT Contoh Klien',
            'client.address' => 'Jl. Contoh No. 456, Jakarta',
        ];

        $sampleDeal = [
            'deal.title' => 'Deal Proyek IT Infrastructure',
            'deal.amount' => '250.000.000',
            'deal.status' => 'Negosiasi',
            'deal.expected_close_date' => now()->addWeeks(2)->format('d F Y'),
            'client.name' => 'PT Contoh Klien',
            'client.address' => 'Jl. Contoh No. 456, Jakarta',
            'client.phone' => '021-1234-5678',
            'client.email' => 'klien@contoh.co.id',
        ];

        $sampleWarning = [
            'warning.reason' => 'Ketidakhadiran tanpa keterangan 3 hari berturut-turut',
            'warning.level' => 'SP 1',
            'warning.date' => now()->format('d F Y'),
        ];

        return array_merge($data, $sampleEmployee, $sampleInvoice, $sampleProject, $sampleDeal, $sampleWarning);
    }

    public function replacePlaceholders(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }

    protected function wrapHtml(string $content, string $title): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12pt; line-height: 1.6; color: #1f2937; padding: 40px; }
        h1 { font-size: 18pt; font-weight: 700; color: #111827; margin-bottom: 16px; }
        h2 { font-size: 14pt; font-weight: 600; color: #374151; margin-bottom: 12px; }
        p { margin-bottom: 8px; }
        .header { border-bottom: 2px solid #4f46e5; padding-bottom: 16px; margin-bottom: 24px; }
        .footer { border-top: 1px solid #d1d5db; padding-top: 12px; margin-top: 40px; font-size: 9pt; color: #9ca3af; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; }
        th, td { border: 1px solid #d1d5db; padding: 8px 12px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: 600; }
        .signature-section { margin-top: 60px; display: flex; justify-content: space-between; }
        .signature-box { width: 40%; text-align: center; }
        .signature-line { border-top: 1px solid #000; margin-top: 60px; padding-top: 8px; }
    </style>
</head>
<body>
    {$content}
    <div class="footer">
        Dokumen ini digenerate otomatis oleh BizOS pada {{date.now}}
    </div>
</body>
</html>
HTML;
    }

    protected function translateEmployeeType(string $type): string
    {
        return match ($type) {
            'permanent' => 'Tetap',
            'contract' => 'Kontrak',
            'intern' => 'Magang',
            'freelance' => 'Freelance',
            default => $type,
        };
    }

    protected function translateMaritalStatus(string $status): string
    {
        return match ($status) {
            'single' => 'Belum Menikah',
            'married' => 'Menikah',
            'divorced' => 'Cerai',
            'widowed' => 'Duda/Janda',
            default => $status,
        };
    }

    protected function translateInvoiceStatus(string $status): string
    {
        return match ($status) {
            'draft' => 'Draft',
            'sent' => 'Terkirim',
            'partial' => 'Dibayar Sebagian',
            'paid' => 'Lunas',
            'overdue' => 'Jatuh Tempo',
            'cancelled' => 'Dibatalkan',
            default => $status,
        };
    }

    protected function translateProjectStatus(string $status): string
    {
        return match ($status) {
            'planning' => 'Perencanaan',
            'in_progress' => 'Dalam Pengerjaan',
            'on_hold' => 'Ditunda',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $status,
        };
    }
}
