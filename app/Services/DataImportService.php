<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Coa;
use App\Models\Employee;
use App\Models\ImportLog;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DataImportService
{
    public ?string $currentFilename = null;

    protected array $entities = [
        'client' => [
            'model' => Client::class,
            'label' => 'Klien',
            'icon' => '👤',
            'duplicate_checks' => [
                'client_code' => 'exact',
                'email' => 'exact',
                'phone' => 'normalized',
            ],
            'fields' => [
                'client_code' => ['label' => 'Kode Klien', 'required' => true, 'type' => 'string', 'max' => 50],
                'name' => ['label' => 'Nama', 'required' => true, 'type' => 'string', 'max' => 255],
                'client_type' => ['label' => 'Tipe Klien', 'type' => 'enum', 'options' => ['individual', 'company', 'government'], 'default' => 'individual'],
                'industry' => ['label' => 'Industri', 'type' => 'string', 'max' => 255],
                'tax_id' => ['label' => 'NPWP', 'type' => 'string', 'max' => 50],
                'website' => ['label' => 'Website', 'type' => 'string', 'max' => 255],
                'address' => ['label' => 'Alamat', 'type' => 'string'],
                'city' => ['label' => 'Kota', 'type' => 'string', 'max' => 100],
                'province' => ['label' => 'Provinsi', 'type' => 'string', 'max' => 100],
                'postal_code' => ['label' => 'Kode Pos', 'type' => 'string', 'max' => 10],
                'phone' => ['label' => 'Telepon', 'type' => 'string', 'max' => 30],
                'email' => ['label' => 'Email', 'type' => 'email', 'max' => 255],
                'status' => ['label' => 'Status', 'type' => 'enum', 'options' => ['aktif', 'nonaktif', 'prospek', 'blacklist'], 'default' => 'aktif'],
                'notes' => ['label' => 'Catatan', 'type' => 'string'],
            ],
        ],
        'supplier' => [
            'model' => Supplier::class,
            'label' => 'Supplier',
            'icon' => '🚚',
            'duplicate_checks' => [
                'code' => 'exact',
                'tax_number' => 'exact',
                'email' => 'exact',
            ],
            'fields' => [
                'code' => ['label' => 'Kode', 'required' => true, 'type' => 'string', 'max' => 50],
                'name' => ['label' => 'Nama', 'required' => true, 'type' => 'string', 'max' => 200],
                'contact_person' => ['label' => 'Kontak Person', 'type' => 'string', 'max' => 200],
                'phone' => ['label' => 'Telepon', 'type' => 'string', 'max' => 20],
                'email' => ['label' => 'Email', 'type' => 'email', 'max' => 200],
                'address' => ['label' => 'Alamat', 'type' => 'string'],
                'tax_number' => ['label' => 'NPWP', 'type' => 'string', 'max' => 50],
                'payment_terms' => ['label' => 'Syarat Pembayaran', 'type' => 'enum', 'options' => ['COD', 'CBD', 'NET7', 'NET15', 'NET30', 'NET60', 'NET90'], 'default' => 'NET30'],
                'is_active' => ['label' => 'Aktif', 'type' => 'boolean', 'default' => 1],
            ],
        ],
        'product' => [
            'model' => Product::class,
            'label' => 'Produk',
            'icon' => '📦',
            'duplicate_checks' => [
                'code' => 'exact',
            ],
            'fields' => [
                'code' => ['label' => 'Kode Produk', 'required' => true, 'type' => 'string', 'max' => 50],
                'name' => ['label' => 'Nama Produk', 'required' => true, 'type' => 'string', 'max' => 255],
                'category_id' => ['label' => 'ID Kategori', 'type' => 'integer'],
                'brand_id' => ['label' => 'ID Brand', 'type' => 'integer'],
                'unit' => ['label' => 'Satuan', 'type' => 'string', 'max' => 20, 'default' => 'pcs'],
                'product_type' => ['label' => 'Tipe Produk', 'type' => 'string', 'max' => 50],
                'description' => ['label' => 'Deskripsi', 'type' => 'string'],
                'purchase_price' => ['label' => 'Harga Beli', 'type' => 'numeric'],
                'selling_price' => ['label' => 'Harga Jual', 'type' => 'numeric'],
                'stock' => ['label' => 'Stok', 'type' => 'numeric', 'default' => 0],
                'min_stock' => ['label' => 'Stok Minimum', 'type' => 'numeric', 'default' => 0],
                'max_stock' => ['label' => 'Stok Maksimum', 'type' => 'numeric', 'default' => 0],
                'is_taxable' => ['label' => 'Kena Pajak', 'type' => 'boolean', 'default' => 0],
                'tax_rate' => ['label' => 'Tarif Pajak (%)', 'type' => 'numeric'],
                'is_active' => ['label' => 'Aktif', 'type' => 'boolean', 'default' => 1],
            ],
        ],
        'employee' => [
            'model' => Employee::class,
            'label' => 'Karyawan',
            'icon' => '🧑‍💼',
            'duplicate_checks' => [
                'employee_code' => 'exact',
                'email' => 'exact',
            ],
            'fields' => [
                'employee_code' => ['label' => 'ID Karyawan', 'required' => true, 'type' => 'string', 'max' => 50],
                'first_name' => ['label' => 'Nama Depan', 'required' => true, 'type' => 'string', 'max' => 100],
                'last_name' => ['label' => 'Nama Belakang', 'type' => 'string', 'max' => 100],
                'email' => ['label' => 'Email', 'required' => true, 'type' => 'email', 'max' => 255],
                'phone' => ['label' => 'Telepon', 'type' => 'string', 'max' => 30],
                'gender' => ['label' => 'Jenis Kelamin', 'type' => 'enum', 'options' => ['male', 'female', 'other']],
                'birth_date' => ['label' => 'Tanggal Lahir', 'type' => 'date'],
                'birth_place' => ['label' => 'Tempat Lahir', 'type' => 'string', 'max' => 100],
                'religion' => ['label' => 'Agama', 'type' => 'string', 'max' => 50],
                'marital_status' => ['label' => 'Status Pernikahan', 'type' => 'enum', 'options' => ['single', 'married', 'divorced', 'widowed']],
                'nationality' => ['label' => 'Kewarganegaraan', 'type' => 'string', 'max' => 50, 'default' => 'Indonesia'],
                'id_number' => ['label' => 'NIK KTP', 'type' => 'string', 'max' => 50],
                'tax_number' => ['label' => 'NPWP', 'type' => 'string', 'max' => 50],
                'address' => ['label' => 'Alamat', 'type' => 'string'],
                'city' => ['label' => 'Kota', 'type' => 'string', 'max' => 100],
                'province' => ['label' => 'Provinsi', 'type' => 'string', 'max' => 100],
                'postal_code' => ['label' => 'Kode Pos', 'type' => 'string', 'max' => 10],
                'branch_id' => ['label' => 'ID Cabang', 'type' => 'integer'],
                'department_id' => ['label' => 'ID Departemen', 'type' => 'integer'],
                'position_id' => ['label' => 'ID Jabatan', 'type' => 'integer'],
                'join_date' => ['label' => 'Tanggal Bergabung', 'type' => 'date'],
                'employee_type' => ['label' => 'Tipe Karyawan', 'type' => 'enum', 'options' => ['permanent', 'contract', 'probation', 'intern', 'freelance', 'part_time'], 'default' => 'permanent'],
                'status' => ['label' => 'Status', 'type' => 'enum', 'options' => ['active', 'inactive', 'terminated', 'resigned', 'retired'], 'default' => 'active'],
                'basic_salary' => ['label' => 'Gaji Pokok', 'type' => 'numeric'],
            ],
        ],
        'lead' => [
            'model' => Lead::class,
            'label' => 'Lead',
            'icon' => '🎯',
            'duplicate_checks' => [
                'email' => 'exact',
                'phone' => 'normalized',
            ],
            'fields' => [
                'first_name' => ['label' => 'Nama Depan', 'required' => true, 'type' => 'string', 'max' => 255],
                'last_name' => ['label' => 'Nama Belakang', 'type' => 'string', 'max' => 255],
                'email' => ['label' => 'Email', 'type' => 'email', 'max' => 255],
                'phone' => ['label' => 'Telepon', 'type' => 'string', 'max' => 30],
                'company_name' => ['label' => 'Nama Perusahaan', 'type' => 'string', 'max' => 255],
                'industry' => ['label' => 'Industri', 'type' => 'string', 'max' => 255],
                'address' => ['label' => 'Alamat', 'type' => 'string'],
                'source_id' => ['label' => 'ID Sumber', 'type' => 'integer'],
                'assigned_to' => ['label' => 'ID Ditugaskan Ke', 'type' => 'integer'],
                'score' => ['label' => 'Skor', 'type' => 'integer', 'default' => 0],
                'status' => ['label' => 'Status', 'type' => 'enum', 'options' => ['baru', 'dihubungi', 'terkualifikasi', 'tidak_tertarik', 'terkonversi'], 'default' => 'baru'],
                'notes' => ['label' => 'Catatan', 'type' => 'string'],
            ],
        ],
        'coa' => [
            'model' => Coa::class,
            'label' => 'COA',
            'icon' => '📒',
            'duplicate_checks' => [
                'code' => 'exact',
            ],
            'fields' => [
                'code' => ['label' => 'Kode', 'required' => true, 'type' => 'string', 'max' => 30],
                'name' => ['label' => 'Nama Akun', 'required' => true, 'type' => 'string', 'max' => 255],
                'category_id' => ['label' => 'ID Kategori', 'type' => 'integer'],
                'parent_id' => ['label' => 'ID Induk COA', 'type' => 'integer'],
                'balance_type' => ['label' => 'Tipe Saldo', 'required' => true, 'type' => 'enum', 'options' => ['debit', 'credit']],
                'opening_balance' => ['label' => 'Saldo Awal', 'type' => 'numeric', 'default' => 0],
                'is_header' => ['label' => 'Header', 'type' => 'boolean', 'default' => 0],
                'is_active' => ['label' => 'Aktif', 'type' => 'boolean', 'default' => 1],
                'description' => ['label' => 'Deskripsi', 'type' => 'string'],
                'cost_center_id' => ['label' => 'ID Pusat Biaya', 'type' => 'integer'],
                'profit_center_id' => ['label' => 'ID Pusat Laba', 'type' => 'integer'],
            ],
        ],
    ];

    public function getImportableEntities(): array
    {
        $result = [];
        foreach ($this->entities as $key => $config) {
            $result[$key] = $config['label'];
        }
        return $result;
    }

    public function getEntities(): array
    {
        return $this->entities;
    }

    public function entityConfig(string $entity): array
    {
        $config = $this->entities[$entity] ?? null;
        if (!$config) {
            throw new \InvalidArgumentException("Tipe entitas '$entity' tidak dikenal.");
        }
        return $config;
    }

    public function getEntityFields(string $entity): array
    {
        $config = $this->entityConfig($entity);
        $required = [];
        $optional = [];

        foreach ($config['fields'] as $name => $def) {
            $item = [
                'name' => $name,
                'label' => $def['label'],
                'type' => $def['type'] ?? 'string',
                'options' => $def['options'] ?? [],
                'default' => $def['default'] ?? null,
            ];
            if ($def['required'] ?? false) {
                $required[] = $item;
            } else {
                $optional[] = $item;
            }
        }

        return [
            'entity' => $entity,
            'label' => $config['label'],
            'icon' => $config['icon'] ?? null,
            'required' => $required,
            'optional' => $optional,
        ];
    }

    public function parseCsv(string $filePath): array
    {
        $fullPath = $filePath;
        if (!file_exists($fullPath)) {
            $fullPath = Storage::disk('public')->path($filePath);
        }
        if (!file_exists($fullPath)) {
            throw new \InvalidArgumentException('File CSV tidak ditemukan.');
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Gagal membuka file CSV.');
        }

        $delimiter = $this->detectDelimiter($handle);
        rewind($handle);

        $headers = [];
        $rows = [];
        $firstLine = true;

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($data === [null] || $data === false) {
                continue;
            }
            $data = array_map(fn ($v) => $this->cleanCell((string) $v), $data);

            if ($firstLine) {
                $headers = [];
                foreach ($data as $i => $h) {
                    $h = trim($h);
                    $headers[] = $h !== '' ? $h : 'kolom_' . ($i + 1);
                }
                $firstLine = false;
                continue;
            }

            if (!array_filter($data, fn ($v) => trim((string) $v) !== '')) {
                continue;
            }

            $padded = array_pad($data, count($headers), '');
            $rows[] = array_combine($headers, array_slice($padded, 0, count($headers)));
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'rows' => $rows,
            'total_rows' => count($rows),
            'preview' => array_slice($rows, 0, 5),
        ];
    }

    public function validateRows(array $rows, string $entity, array $mapping): array
    {
        $this->entityConfig($entity);
        $errors = [];
        $validRows = 0;
        $seen = [];
        $companyId = auth()->user()?->company_id;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $rowErrors = $this->validateSingleRow($row, $rowNumber, $entity, $mapping, $seen, $companyId);
            if (empty($rowErrors)) {
                $validRows++;
            } else {
                foreach ($rowErrors as $e) {
                    $errors[] = $e;
                }
            }
        }

        return [
            'total_rows' => count($rows),
            'valid_rows' => $validRows,
            'error_rows' => count($rows) - $validRows,
            'error_count' => count($errors),
            'errors' => $errors,
            'has_errors' => count($errors) > 0,
        ];
    }

    public function import(string $entity, array $rows, array $mapping, int $userId): array
    {
        $config = $this->entityConfig($entity);
        $companyId = User::find($userId)?->company_id;

        $log = ImportLog::create([
            'company_id' => $companyId,
            'entity_type' => $entity,
            'filename' => $this->currentFilename,
            'total_rows' => count($rows),
            'success_count' => 0,
            'error_count' => 0,
            'status' => 'processing',
            'errors' => null,
            'imported_by' => $userId,
        ]);

        $success = 0;
        $errors = [];
        $seen = [];

        try {
            DB::transaction(function () use ($entity, $rows, $mapping, $companyId, &$success, &$errors, &$seen) {
                foreach ($rows as $index => $row) {
                    $rowNumber = $index + 2;
                    $rowErrors = $this->validateSingleRow($row, $rowNumber, $entity, $mapping, $seen, $companyId);
                    if (!empty($rowErrors)) {
                        foreach ($rowErrors as $e) {
                            $errors[] = $e;
                        }
                        continue;
                    }

                    $model = $this->entities[$entity]['model'];
                    $model::create($this->buildData($entity, $row, $mapping, $companyId));
                    $success++;
                }
            });

            $log->update([
                'success_count' => $success,
                'error_count' => count($errors),
                'status' => 'completed',
                'errors' => $errors ?: null,
            ]);
        } catch (\Throwable $e) {
            $errors[] = [
                'row' => 0,
                'field' => 'system',
                'message' => $e->getMessage(),
            ];
            $log->update([
                'success_count' => $success,
                'error_count' => count($errors),
                'status' => 'failed',
                'errors' => $errors,
            ]);
            throw $e;
        }

        return [
            'success_count' => $success,
            'error_count' => count($errors),
            'errors' => $errors,
            'import_log_id' => $log->id,
        ];
    }

    public function generateTemplate(string $entity): string
    {
        $config = $this->entityConfig($entity);
        $headers = array_keys($config['fields']);
        $example = [];
        foreach ($config['fields'] as $field => $def) {
            $example[] = $this->templateExample($field, $def);
        }

        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, $headers);
        fputcsv($stream, $example);
        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }

    public function getImportLogs(): Collection
    {
        $companyId = auth()->user()?->company_id;

        return ImportLog::with('importer')
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->latest()
            ->limit(100)
            ->get();
    }

    public function setFilename(string $filename): self
    {
        $this->currentFilename = $filename;
        return $this;
    }

    protected function validateSingleRow(array $row, int $rowNumber, string $entity, array $mapping, array &$seen, ?int $companyId = null): array
    {
        $config = $this->entityConfig($entity);
        $errors = [];

        foreach ($config['fields'] as $field => $def) {
            $csvHeader = $mapping[$field] ?? null;
            $value = ($csvHeader !== null && $csvHeader !== '') ? trim((string) ($row[$csvHeader] ?? '')) : '';

            if (($def['required'] ?? false) && $value === '') {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => $field,
                    'message' => "{$def['label']} wajib diisi",
                ];
                continue;
            }

            if ($value === '') {
                continue;
            }

            switch ($def['type'] ?? 'string') {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = [
                            'row' => $rowNumber,
                            'field' => $field,
                            'message' => "{$def['label']} bukan alamat email yang valid",
                        ];
                    }
                    break;
                case 'numeric':
                    if (!is_numeric($this->normalizeNumber($value))) {
                        $errors[] = [
                            'row' => $rowNumber,
                            'field' => $field,
                            'message' => "{$def['label']} harus berupa angka",
                        ];
                    }
                    break;
                case 'integer':
                    if (!preg_match('/^-?\d+$/', trim($value))) {
                        $errors[] = [
                            'row' => $rowNumber,
                            'field' => $field,
                            'message' => "{$def['label']} harus berupa bilangan bulat",
                        ];
                    }
                    break;
                case 'date':
                    if (!$this->isValidDate($value)) {
                        $errors[] = [
                            'row' => $rowNumber,
                            'field' => $field,
                            'message' => "{$def['label']} bukan tanggal yang valid (format: YYYY-MM-DD)",
                        ];
                    }
                    break;
                case 'enum':
                    if (!in_array($value, $def['options'] ?? [], true)) {
                        $errors[] = [
                            'row' => $rowNumber,
                            'field' => $field,
                            'message' => "{$def['label']} '{$value}' tidak valid. Pilihan: " . implode(', ', $def['options'] ?? []),
                        ];
                    }
                    break;
                case 'boolean':
                    if (!$this->isBoolean($value)) {
                        $errors[] = [
                            'row' => $rowNumber,
                            'field' => $field,
                            'message' => "{$def['label']} harus 0/1, ya/tidak, atau true/false",
                        ];
                    }
                    break;
            }

            if (isset($def['max']) && mb_strlen($value) > $def['max']) {
                $errors[] = [
                    'row' => $rowNumber,
                    'field' => $field,
                    'message' => "{$def['label']} melebihi {$def['max']} karakter",
                ];
            }
        }

        $errors = array_merge($errors, $this->detectRowDuplicates($entity, $row, $mapping, $seen, $companyId));

        return $errors;
    }

    protected function detectRowDuplicates(string $entity, array $row, array $mapping, array &$seen, ?int $companyId = null): array
    {
        $config = $this->entityConfig($entity);
        $checks = $config['duplicate_checks'] ?? [];
        $companyId = $companyId ?? auth()->user()?->company_id;
        $errors = [];

        foreach ($checks as $field => $mode) {
            $csvHeader = $mapping[$field] ?? null;
            if (!$csvHeader) {
                continue;
            }
            $value = trim((string) ($row[$csvHeader] ?? ''));
            if ($value === '') {
                continue;
            }

            $key = $mode === 'normalized' ? $this->normalizePhone($value) : strtolower($value);
            $label = $config['fields'][$field]['label'] ?? $field;

            if (isset($seen[$field][$key])) {
                $errors[] = [
                    'field' => $field,
                    'message' => "Duplikat {$label} '{$value}' terdeteksi di dalam file",
                ];
            } else {
                $seen[$field][$key] = true;
            }

            if ($companyId && $this->existsInDb($config['model'], $field, $value, $mode, $companyId)) {
                $errors[] = [
                    'field' => $field,
                    'message' => "{$label} '{$value}' sudah terdaftar di database",
                ];
            }
        }

        return $errors;
    }

    protected function existsInDb(string $model, string $field, string $value, string $mode, int $companyId): bool
    {
        $query = $model::query()->where('company_id', $companyId);

        if ($mode === 'normalized') {
            $query->whereRaw(
                "REPLACE(REPLACE(REPLACE(REPLACE({$field}, ' ', ''), '-', ''), '(', ''), ')', '') = ?",
                [$this->normalizePhone($value)]
            );
        } else {
            $query->where(DB::raw("LOWER({$field})"), strtolower($value));
        }

        return $query->exists();
    }

    protected function buildData(string $entity, array $row, array $mapping, ?int $companyId): array
    {
        $config = $this->entityConfig($entity);
        $data = ['company_id' => $companyId];

        foreach ($config['fields'] as $field => $def) {
            $csvHeader = $mapping[$field] ?? null;
            $value = $csvHeader ? trim((string) ($row[$csvHeader] ?? '')) : '';
            if ($value === '') {
                $value = $def['default'] ?? null;
            }
            if ($value === null) {
                continue;
            }
            $data[$field] = $this->castValue($value, $def);
        }

        return $data;
    }

    protected function castValue(string $value, array $def): mixed
    {
        return match ($def['type'] ?? 'string') {
            'boolean' => $this->castBoolean($value),
            'numeric' => (float) $this->normalizeNumber($value),
            'integer' => (int) trim($value),
            'date' => $this->normalizeDate($value),
            default => trim($value),
        };
    }

    protected function normalizeNumber(string $value): string
    {
        $value = str_replace(['Rp', 'rp', ' '], '', $value);
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }
        return $value;
    }

    protected function normalizeDate(string $value): string
    {
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'd/m/y'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim($value))->format('Y-m-d');
            } catch (\Throwable) {
                // try next format
            }
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return trim($value);
        }
    }

    protected function isValidDate(string $value): bool
    {
        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'] as $format) {
            try {
                Carbon::createFromFormat($format, trim($value));
                return true;
            } catch (\Throwable) {
                // continue
            }
        }
        try {
            Carbon::parse($value);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function isBoolean(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['0', '1', 'ya', 'tidak', 'true', 'false'], true);
    }

    protected function castBoolean(string $value): int
    {
        return in_array(strtolower(trim($value)), ['1', 'ya', 'true'], true) ? 1 : 0;
    }

    protected function templateExample(string $field, array $def): string
    {
        return match ($def['type'] ?? 'string') {
            'email' => 'contoh@perusahaan.com',
            'numeric' => '0',
            'integer' => '0',
            'date' => '2026-01-01',
            'boolean' => '1',
            'enum' => $def['options'][0] ?? '',
            default => $this->templateTextExample($field, $def),
        };
    }

    protected function templateTextExample(string $field, array $def): string
    {
        $map = [
            'name' => 'Contoh Nama',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'client_code' => 'CLI-001',
            'code' => 'KD-001',
            'employee_code' => 'EMP-001',
            'phone' => '081234567890',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'postal_code' => '12345',
            'tax_id' => '00.000.000.0-000.000',
            'tax_number' => '00.000.000.0-000.000',
            'address' => 'Jl. Contoh No. 1',
            'website' => 'https://contoh.com',
        ];
        return $map[$field] ?? ('Contoh ' . ($def['label'] ?? $field));
    }

    protected function detectDelimiter($handle): string
    {
        $line = fgets($handle);
        if ($line === false) {
            return ',';
        }
        $candidates = [',', ';', "\t"];
        $best = ',';
        $bestCount = 0;
        foreach ($candidates as $candidate) {
            $count = substr_count($line, $candidate);
            if ($count > $bestCount) {
                $bestCount = $count;
                $best = $candidate;
            }
        }
        return $best;
    }

    protected function cleanCell(string $value): string
    {
        $value = trim($value);
        if (str_starts_with($value, "\xEF\xBB\xBF")) {
            $value = substr($value, 3);
        }
        return trim($value);
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '+62' . substr($phone, 1);
        }
        return $phone;
    }
}
