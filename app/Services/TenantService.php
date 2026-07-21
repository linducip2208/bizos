<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Models\Coa;
use App\Models\CoaCategory;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class TenantService
{
    public function applyCompanyScope(): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        if ($user->company_id) {
            session(['current_company_id' => $user->company_id]);

            if ($user->employee?->branch_id) {
                session(['current_branch_id' => $user->employee->branch_id]);
            }

            if ($user->employee?->department_id) {
                session(['current_department_id' => $user->employee->department_id]);
            }
        }
    }

    public function applyBranchScope(User $user): void
    {
        if ($user->role?->slug === 'super-admin') {
            session()->forget('current_branch_id');
            return;
        }

        if ($user->employee?->branch_id) {
            session(['current_branch_id' => $user->employee->branch_id]);
        }
    }

    public function provisionTenant(string $companyName, string $adminEmail, string $adminName, string $plan = 'trial'): Company
    {
        return DB::transaction(function () use ($companyName, $adminEmail, $adminName, $plan) {
            $slug = \Illuminate\Support\Str::slug($companyName);
            $code = strtoupper(\Illuminate\Support\Str::substr($slug, 0, 4)) . rand(100, 999);

            $company = Company::create([
                'code' => $code,
                'name' => $companyName,
                'slug' => $slug,
                'email' => $adminEmail,
                'is_active' => true,
                'is_suspended' => false,
                'data_retention_days' => 90,
            ]);

            $this->createDefaultRoles($company);
            $this->createDefaultCoa($company);
            $this->createDefaultBranches($company);
            $this->createDefaultDepartments($company);
            $this->createDefaultDesignations($company);
            $this->createDefaultSettings($company);

            $adminRole = Role::where('company_id', $company->id)->where('slug', 'admin')->first();

            $user = User::create([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'role_id' => $adminRole?->id,
                'is_active' => true,
            ]);

            Log::info("Tenant provisioned: {$companyName} ({$company->id})", [
                'plan' => $plan,
                'admin_email' => $adminEmail,
            ]);

            return $company;
        });
    }

    protected function createDefaultRoles(Company $company): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'level' => 1],
            ['name' => 'Admin', 'slug' => 'admin', 'level' => 2],
            ['name' => 'Manager', 'slug' => 'manager', 'level' => 3],
            ['name' => 'Staff', 'slug' => 'staff', 'level' => 4],
            ['name' => 'Kasir', 'slug' => 'kasir', 'level' => 5],
        ];

        foreach ($roles as $role) {
            Role::create([
                'company_id' => $company->id,
                'name' => $role['name'],
                'slug' => $role['slug'],
                'level' => $role['level'],
            ]);
        }
    }

    protected function createDefaultCoa(Company $company): void
    {
        $categories = [
            ['code' => '1', 'name' => 'Aset', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '2', 'name' => 'Kewajiban', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '3', 'name' => 'Ekuitas', 'type' => 'equity', 'normal_balance' => 'credit'],
            ['code' => '4', 'name' => 'Pendapatan', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '5', 'name' => 'Beban', 'type' => 'expense', 'normal_balance' => 'debit'],
        ];

        foreach ($categories as $cat) {
            $category = CoaCategory::create([
                'company_id' => $company->id,
                'code' => $cat['code'],
                'name' => $cat['name'],
                'type' => $cat['type'],
                'normal_balance' => $cat['normal_balance'],
            ]);

            $defaultAccounts = match ($cat['code']) {
                '1' => [
                    ['code' => '1-100', 'name' => 'Kas Kecil', 'subtype' => 'cash'],
                    ['code' => '1-200', 'name' => 'Bank', 'subtype' => 'bank'],
                    ['code' => '1-300', 'name' => 'Piutang Usaha', 'subtype' => 'receivable'],
                ],
                '2' => [
                    ['code' => '2-100', 'name' => 'Hutang Usaha', 'subtype' => 'payable'],
                ],
                '3' => [
                    ['code' => '3-100', 'name' => 'Modal Disetor', 'subtype' => 'capital'],
                    ['code' => '3-200', 'name' => 'Laba Ditahan', 'subtype' => 'retained_earnings'],
                ],
                '4' => [
                    ['code' => '4-100', 'name' => 'Pendapatan Jasa', 'subtype' => 'service_revenue'],
                    ['code' => '4-200', 'name' => 'Pendapatan Lainnya', 'subtype' => 'other_revenue'],
                ],
                '5' => [
                    ['code' => '5-100', 'name' => 'Beban Gaji', 'subtype' => 'salary_expense'],
                    ['code' => '5-200', 'name' => 'Beban Operasional', 'subtype' => 'operational_expense'],
                ],
                default => [],
            };

            foreach ($defaultAccounts as $acc) {
                Coa::create([
                    'company_id' => $company->id,
                    'coa_category_id' => $category->id,
                    'code' => $acc['code'],
                    'name' => $acc['name'],
                    'type' => $cat['type'],
                    'subtype' => $acc['subtype'],
                    'normal_balance' => $cat['normal_balance'],
                ]);
            }
        }
    }

    protected function createDefaultBranches(Company $company): void
    {
        Branch::create([
            'company_id' => $company->id,
            'code' => 'PST',
            'name' => 'Kantor Pusat',
            'is_headquarters' => true,
            'is_active' => true,
        ]);
    }

    protected function createDefaultDepartments(Company $company): void
    {
        $departments = ['Direksi', 'Keuangan', 'HRD', 'Operasional', 'Teknologi', 'Marketing', 'Sales'];

        foreach ($departments as $dept) {
            Department::create([
                'company_id' => $company->id,
                'name' => $dept,
                'is_active' => true,
            ]);
        }
    }

    protected function createDefaultDesignations(Company $company): void
    {
        $designations = [
            ['name' => 'Direktur', 'level' => 1],
            ['name' => 'Manager', 'level' => 2],
            ['name' => 'Supervisor', 'level' => 3],
            ['name' => 'Staff', 'level' => 4],
        ];

        foreach ($designations as $desig) {
            Designation::create([
                'company_id' => $company->id,
                'name' => $desig['name'],
                'level' => $desig['level'],
                'is_active' => true,
            ]);
        }
    }

    protected function createDefaultSettings(Company $company): void
    {
        $defaults = [
            ['key' => 'company_name', 'value' => $company->name],
            ['key' => 'company_address', 'value' => ''],
            ['key' => 'date_format', 'value' => 'd/m/Y'],
            ['key' => 'timezone', 'value' => 'Asia/Jakarta'],
            ['key' => 'currency', 'value' => 'IDR'],
            ['key' => 'fiscal_year_start', 'value' => '01-01'],
            ['key' => 'invoice_prefix', 'value' => 'INV'],
            ['key' => 'auto_approval_threshold', 'value' => '5000000'],
        ];

        foreach ($defaults as $setting) {
            SystemSetting::create([
                'company_id' => $company->id,
                'key' => $setting['key'],
                'value' => $setting['value'],
            ]);
        }
    }

    // ─── Suspension ───────────────────────────────────────────────────

    public function suspendTenant(Company $company, string $reason): void
    {
        $company->update([
            'is_suspended' => true,
            'suspended_reason' => $reason,
            'suspended_at' => now(),
        ]);

        $company->users()->update(['is_active' => false]);

        Log::warning("Tenant suspended: {$company->name} ({$company->id})", ['reason' => $reason]);
    }

    public function reactivateTenant(Company $company): void
    {
        $company->update([
            'is_suspended' => false,
            'suspended_reason' => null,
            'suspended_at' => null,
        ]);

        Log::info("Tenant reactivated: {$company->name} ({$company->id})");
    }

    // ─── Usage ────────────────────────────────────────────────────────

    public function getUsage(Company $company): array
    {
        return [
            'users_count' => $company->users()->count(),
            'employees_count' => $company->employees()->count(),
            'storage_used_mb' => $this->calculateStorageUsed($company),
            'transactions_this_month' => $this->countTransactionsThisMonth($company),
            'api_calls_today' => $this->countApiCallsToday($company),
            'is_suspended' => $company->is_suspended,
            'subscription_start' => $company->subscription_start?->format('Y-m-d'),
            'subscription_end' => $company->subscription_end?->format('Y-m-d'),
        ];
    }

    protected function calculateStorageUsed(Company $company): float
    {
        try {
            $path = storage_path("app/company_{$company->id}");
            if (!is_dir($path)) return 0;

            $size = 0;
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS));
            foreach ($iterator as $file) {
                $size += $file->getSize();
            }
            return round($size / 1024 / 1024, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function countTransactionsThisMonth(Company $company): int
    {
        $count = 0;

        if (Schema::hasTable('pos_transactions')) {
            $count += \App\Models\PosTransaction::where('company_id', $company->id)
                ->whereMonth('created_at', now()->month)
                ->count();
        }

        if (Schema::hasTable('invoices')) {
            $count += \App\Models\Invoice::where('company_id', $company->id)
                ->whereMonth('created_at', now()->month)
                ->count();
        }

        return $count;
    }

    protected function countApiCallsToday(Company $company): int
    {
        return \App\Models\AuditLog::where('company_id', $company->id)
            ->whereDate('created_at', today())
            ->where('action', 'like', 'api.%')
            ->count();
    }

    // ─── Limits ──────────────────────────────────────────────────────

    public function enforceLimits(Company $company): void
    {
        $usage = $this->getUsage($company);

        $limits = $this->getTenantLimits();

        $issues = [];

        if ($limits['max_users'] > 0 && $usage['users_count'] > $limits['max_users']) {
            $issues[] = "Pengguna ({$usage['users_count']}) melebihi batas ({$limits['max_users']})";
        }

        if ($limits['max_storage_mb'] > 0 && $usage['storage_used_mb'] > $limits['max_storage_mb']) {
            $issues[] = "Storage ({$usage['storage_used_mb']}MB) melebihi batas ({$limits['max_storage_mb']}MB)";
        }

        if ($limits['max_transactions'] > 0 && $usage['transactions_this_month'] > $limits['max_transactions']) {
            $issues[] = "Transaksi bulan ini ({$usage['transactions_this_month']}) melebihi batas ({$limits['max_transactions']})";
        }

        if ($company->subscription_end && $company->subscription_end->isPast() && !$company->is_suspended) {
            $this->suspendTenant($company, 'Langganan berakhir pada ' . $company->subscription_end->format('d M Y'));
        }

        if (!empty($issues) && !$company->is_suspended) {
            $this->suspendTenant($company, 'Batas penggunaan terlampaui: ' . implode('; ', $issues));
        }
    }

    protected function getTenantLimits(): array
    {
        return [
            'max_users' => (int) (config('license.tenant_limits.max_users') ?: env('TENANT_MAX_USERS', 0)),
            'max_storage_mb' => (int) (config('license.tenant_limits.max_storage_mb') ?: env('TENANT_MAX_STORAGE_MB', 0)),
            'max_transactions' => (int) (config('license.tenant_limits.max_transactions') ?: env('TENANT_MAX_TRANSACTIONS', 0)),
        ];
    }

    // ─── Export ───────────────────────────────────────────────────────

    public function exportTenantData(Company $company): string
    {
        $exportPath = storage_path("app/exports/tenant_{$company->id}_" . now()->format('Ymd_His'));
        if (!is_dir($exportPath)) {
            mkdir($exportPath, 0755, true);
        }

        // Export company info
        file_put_contents("{$exportPath}/company.json", json_encode($company->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Export all related tables
        $tables = [
            'users', 'employees', 'roles', 'branches', 'departments', 'designations',
            'coas', 'coa_categories', 'system_settings', 'clients', 'suppliers',
            'products', 'product_categories', 'invoices', 'invoice_items', 'invoice_payments',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) continue;

            $records = DB::table($table)->where('company_id', $company->id)->get()->toArray();
            if (!empty($records)) {
                file_put_contents(
                    "{$exportPath}/{$table}.json",
                    json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );
            }
        }

        // Create ZIP
        $zipPath = "{$exportPath}.zip";
        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($exportPath, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($exportPath) + 1);
                $zip->addFile($filePath, $relativePath);
            }
            $zip->close();
        }

        // Cleanup temp directory
        $this->deleteDirectory($exportPath);

        return $zipPath;
    }

    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    // ─── Data Retention ──────────────────────────────────────────────

    public function purgeExpiredTenantData(): int
    {
        $companies = Company::where('is_suspended', true)
            ->whereNotNull('suspended_at')
            ->whereNotNull('data_retention_days')
            ->whereRaw('DATE_ADD(suspended_at, INTERVAL data_retention_days DAY) <= NOW()')
            ->get();

        $purged = 0;

        foreach ($companies as $company) {
            Log::info("Purging data for tenant: {$company->name} ({$company->id})");

            // Hapus data sensitif, jangan hapus company record
            $company->users()->delete();
            $company->employees()->delete();
            $company->branches()->delete();
            $company->departments()->delete();
            $company->designations()->delete();
            $company->waConversations()?->delete();
            $company->waTemplates()?->delete();
            $company->chatbotFlows()?->delete();

            $purged++;
        }

        return $purged;
    }
}
