<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\User;
use Illuminate\Support\Str;

class ApiHubService
{
    protected array $endpointCache = [];

    public function getEndpoints(): array
    {
        if (! empty($this->endpointCache)) {
            return $this->endpointCache;
        }

        $this->endpointCache = [
            [
                'resource' => 'employees',
                'label' => 'Karyawan',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/employees', 'description' => 'Daftar karyawan'],
                    ['method' => 'GET', 'path' => '/api/v1/employees/{id}', 'description' => 'Detail karyawan'],
                    ['method' => 'POST', 'path' => '/api/v1/employees', 'description' => 'Tambah karyawan'],
                    ['method' => 'PUT', 'path' => '/api/v1/employees/{id}', 'description' => 'Ubah karyawan'],
                    ['method' => 'DELETE', 'path' => '/api/v1/employees/{id}', 'description' => 'Hapus karyawan'],
                ],
            ],
            [
                'resource' => 'attendances',
                'label' => 'Absensi',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/attendances', 'description' => 'Daftar absensi'],
                    ['method' => 'GET', 'path' => '/api/v1/attendances/{id}', 'description' => 'Detail absensi'],
                    ['method' => 'POST', 'path' => '/api/v1/attendances', 'description' => 'Input absensi'],
                    ['method' => 'PUT', 'path' => '/api/v1/attendances/{id}', 'description' => 'Ubah absensi'],
                    ['method' => 'DELETE', 'path' => '/api/v1/attendances/{id}', 'description' => 'Hapus absensi'],
                ],
            ],
            [
                'resource' => 'leaves',
                'label' => 'Cuti',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/leaves', 'description' => 'Daftar cuti'],
                    ['method' => 'GET', 'path' => '/api/v1/leaves/{id}', 'description' => 'Detail cuti'],
                    ['method' => 'POST', 'path' => '/api/v1/leaves', 'description' => 'Ajukan cuti'],
                    ['method' => 'PUT', 'path' => '/api/v1/leaves/{id}', 'description' => 'Ubah cuti'],
                    ['method' => 'DELETE', 'path' => '/api/v1/leaves/{id}', 'description' => 'Hapus cuti'],
                ],
            ],
            [
                'resource' => 'invoices',
                'label' => 'Invoice',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/invoices', 'description' => 'Daftar invoice'],
                    ['method' => 'GET', 'path' => '/api/v1/invoices/{id}', 'description' => 'Detail invoice'],
                    ['method' => 'POST', 'path' => '/api/v1/invoices', 'description' => 'Buat invoice'],
                    ['method' => 'PUT', 'path' => '/api/v1/invoices/{id}', 'description' => 'Ubah invoice'],
                    ['method' => 'DELETE', 'path' => '/api/v1/invoices/{id}', 'description' => 'Hapus invoice'],
                ],
            ],
            [
                'resource' => 'payments',
                'label' => 'Pembayaran',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/payments', 'description' => 'Daftar pembayaran'],
                    ['method' => 'GET', 'path' => '/api/v1/payments/{id}', 'description' => 'Detail pembayaran'],
                    ['method' => 'POST', 'path' => '/api/v1/payments', 'description' => 'Buat pembayaran'],
                    ['method' => 'PUT', 'path' => '/api/v1/payments/{id}', 'description' => 'Ubah pembayaran'],
                    ['method' => 'DELETE', 'path' => '/api/v1/payments/{id}', 'description' => 'Hapus pembayaran'],
                ],
            ],
            [
                'resource' => 'journals',
                'label' => 'Jurnal',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/journals', 'description' => 'Daftar jurnal'],
                    ['method' => 'GET', 'path' => '/api/v1/journals/{id}', 'description' => 'Detail jurnal'],
                    ['method' => 'POST', 'path' => '/api/v1/journals', 'description' => 'Buat jurnal'],
                    ['method' => 'PUT', 'path' => '/api/v1/journals/{id}', 'description' => 'Ubah jurnal'],
                    ['method' => 'DELETE', 'path' => '/api/v1/journals/{id}', 'description' => 'Hapus jurnal'],
                ],
            ],
            [
                'resource' => 'clients',
                'label' => 'Klien',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/clients', 'description' => 'Daftar klien'],
                    ['method' => 'GET', 'path' => '/api/v1/clients/{id}', 'description' => 'Detail klien'],
                    ['method' => 'POST', 'path' => '/api/v1/clients', 'description' => 'Tambah klien'],
                    ['method' => 'PUT', 'path' => '/api/v1/clients/{id}', 'description' => 'Ubah klien'],
                    ['method' => 'DELETE', 'path' => '/api/v1/clients/{id}', 'description' => 'Hapus klien'],
                ],
            ],
            [
                'resource' => 'leads',
                'label' => 'Lead',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/leads', 'description' => 'Daftar lead'],
                    ['method' => 'GET', 'path' => '/api/v1/leads/{id}', 'description' => 'Detail lead'],
                    ['method' => 'POST', 'path' => '/api/v1/leads', 'description' => 'Tambah lead'],
                    ['method' => 'PUT', 'path' => '/api/v1/leads/{id}', 'description' => 'Ubah lead'],
                    ['method' => 'DELETE', 'path' => '/api/v1/leads/{id}', 'description' => 'Hapus lead'],
                ],
            ],
            [
                'resource' => 'deals',
                'label' => 'Deal',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/deals', 'description' => 'Daftar deal'],
                    ['method' => 'GET', 'path' => '/api/v1/deals/{id}', 'description' => 'Detail deal'],
                    ['method' => 'POST', 'path' => '/api/v1/deals', 'description' => 'Buat deal'],
                    ['method' => 'PUT', 'path' => '/api/v1/deals/{id}', 'description' => 'Ubah deal'],
                    ['method' => 'DELETE', 'path' => '/api/v1/deals/{id}', 'description' => 'Hapus deal'],
                ],
            ],
            [
                'resource' => 'products',
                'label' => 'Produk',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/products', 'description' => 'Daftar produk'],
                    ['method' => 'GET', 'path' => '/api/v1/products/{id}', 'description' => 'Detail produk'],
                    ['method' => 'POST', 'path' => '/api/v1/products', 'description' => 'Tambah produk'],
                    ['method' => 'PUT', 'path' => '/api/v1/products/{id}', 'description' => 'Ubah produk'],
                    ['method' => 'DELETE', 'path' => '/api/v1/products/{id}', 'description' => 'Hapus produk'],
                ],
            ],
            [
                'resource' => 'pos-transactions',
                'label' => 'Transaksi POS',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/pos-transactions', 'description' => 'Daftar transaksi POS'],
                    ['method' => 'GET', 'path' => '/api/v1/pos-transactions/{id}', 'description' => 'Detail transaksi POS'],
                    ['method' => 'POST', 'path' => '/api/v1/pos-transactions', 'description' => 'Buat transaksi POS'],
                    ['method' => 'PUT', 'path' => '/api/v1/pos-transactions/{id}', 'description' => 'Ubah transaksi POS'],
                    ['method' => 'DELETE', 'path' => '/api/v1/pos-transactions/{id}', 'description' => 'Hapus transaksi POS'],
                ],
            ],
            [
                'resource' => 'projects',
                'label' => 'Proyek',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/projects', 'description' => 'Daftar proyek'],
                    ['method' => 'GET', 'path' => '/api/v1/projects/{id}', 'description' => 'Detail proyek'],
                    ['method' => 'POST', 'path' => '/api/v1/projects', 'description' => 'Buat proyek'],
                    ['method' => 'PUT', 'path' => '/api/v1/projects/{id}', 'description' => 'Ubah proyek'],
                    ['method' => 'DELETE', 'path' => '/api/v1/projects/{id}', 'description' => 'Hapus proyek'],
                ],
            ],
            [
                'resource' => 'tasks',
                'label' => 'Tugas',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/tasks', 'description' => 'Daftar tugas'],
                    ['method' => 'GET', 'path' => '/api/v1/tasks/{id}', 'description' => 'Detail tugas'],
                    ['method' => 'POST', 'path' => '/api/v1/tasks', 'description' => 'Buat tugas'],
                    ['method' => 'PUT', 'path' => '/api/v1/tasks/{id}', 'description' => 'Ubah tugas'],
                    ['method' => 'DELETE', 'path' => '/api/v1/tasks/{id}', 'description' => 'Hapus tugas'],
                ],
            ],
            [
                'resource' => 'timesheets',
                'label' => 'Timesheet',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/timesheets', 'description' => 'Daftar timesheet'],
                    ['method' => 'GET', 'path' => '/api/v1/timesheets/{id}', 'description' => 'Detail timesheet'],
                    ['method' => 'POST', 'path' => '/api/v1/timesheets', 'description' => 'Input timesheet'],
                    ['method' => 'PUT', 'path' => '/api/v1/timesheets/{id}', 'description' => 'Ubah timesheet'],
                    ['method' => 'DELETE', 'path' => '/api/v1/timesheets/{id}', 'description' => 'Hapus timesheet'],
                ],
            ],
            [
                'resource' => 'tickets',
                'label' => 'Tiket',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/tickets', 'description' => 'Daftar tiket'],
                    ['method' => 'GET', 'path' => '/api/v1/tickets/{id}', 'description' => 'Detail tiket'],
                    ['method' => 'POST', 'path' => '/api/v1/tickets', 'description' => 'Buat tiket'],
                    ['method' => 'PUT', 'path' => '/api/v1/tickets/{id}', 'description' => 'Ubah tiket'],
                    ['method' => 'DELETE', 'path' => '/api/v1/tickets/{id}', 'description' => 'Hapus tiket'],
                ],
            ],
            [
                'resource' => 'payrolls',
                'label' => 'Payroll',
                'endpoints' => [
                    ['method' => 'GET', 'path' => '/api/v1/payrolls', 'description' => 'Daftar payroll'],
                    ['method' => 'GET', 'path' => '/api/v1/payrolls/{id}', 'description' => 'Detail payroll'],
                    ['method' => 'POST', 'path' => '/api/v1/payrolls', 'description' => 'Proses payroll'],
                    ['method' => 'PUT', 'path' => '/api/v1/payrolls/{id}', 'description' => 'Ubah payroll'],
                    ['method' => 'DELETE', 'path' => '/api/v1/payrolls/{id}', 'description' => 'Hapus payroll'],
                ],
            ],
        ];

        return $this->endpointCache;
    }

    public function generateToken(User $user, string $name, array $permissions = [], ?int $rateLimit = 60, ?\DateTime $expiresAt = null): string
    {
        $token = Str::random(64);

        ApiKey::create([
            'company_id' => $user->company_id,
            'name' => $name,
            'key' => hash('sha256', $token),
            'permissions' => $permissions,
            'rate_limit' => $rateLimit,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        return $token;
    }

    public function validateToken(string $token, string $requiredPermission): bool
    {
        $hashedToken = hash('sha256', $token);
        $apiKey = ApiKey::where('key', $hashedToken)->first();

        if (! $apiKey || ! $apiKey->isValid()) {
            return false;
        }

        $apiKey->update(['last_used_at' => now()]);

        if (! empty($apiKey->permissions) && ! in_array('*', $apiKey->permissions)) {
            return $apiKey->hasPermission($requiredPermission);
        }

        return true;
    }

    public function checkRateLimit(string $token): bool
    {
        $hashedToken = hash('sha256', $token);
        $apiKey = ApiKey::where('key', $hashedToken)->first();

        if (! $apiKey || ! $apiKey->isValid()) {
            return false;
        }

        $cacheKey = "api_rate_limit:{$apiKey->id}";
        $window = 60;
        $current = cache()->get($cacheKey, 0);

        if ($current >= $apiKey->rate_limit) {
            return false;
        }

        cache()->increment($cacheKey);
        if ($current === 0) {
            cache()->put($cacheKey, 1, now()->addSeconds($window));
        }

        return true;
    }

    public function findKeyByToken(string $token): ?ApiKey
    {
        $hashedToken = hash('sha256', $token);
        return ApiKey::where('key', $hashedToken)->first();
    }

    public function revokeToken(string $token): bool
    {
        $hashedToken = hash('sha256', $token);
        return ApiKey::where('key', $hashedToken)->update(['is_active' => false]) > 0;
    }

    public function getTokenUsageStats(ApiKey $apiKey): array
    {
        return [
            'total_requests' => cache()->get("api_stats:{$apiKey->id}:total", 0),
            'last_used_at' => $apiKey->last_used_at,
            'rate_limit' => $apiKey->rate_limit,
            'current_window_requests' => cache()->get("api_rate_limit:{$apiKey->id}", 0),
        ];
    }
}
