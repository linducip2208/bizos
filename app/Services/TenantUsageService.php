<?php

namespace App\Services;

use App\Models\Company;
use App\Models\TenantUsageLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantUsageService
{
    protected ?int $companyId = null;

    public function __construct(?int $companyId = null)
    {
        $this->companyId = $companyId ?? session('current_company_id');
    }

    public function record(string $metric, float $value): void
    {
        if (!$this->companyId) return;

        $existing = TenantUsageLog::where('company_id', $this->companyId)
            ->where('metric', $metric)
            ->where('recorded_at', today()->toDateString())
            ->first();

        if ($existing) {
            $existing->increment('value', $value);
        } else {
            TenantUsageLog::create([
                'company_id' => $this->companyId,
                'metric' => $metric,
                'value' => $value,
                'recorded_at' => today(),
            ]);
        }
    }

    public function recordIncrement(string $metric): void
    {
        $this->record($metric, 1);
    }

    public function getCurrentUsage(int $companyId): array
    {
        $usage = [
            'users_count' => Company::find($companyId)?->users()->count() ?? 0,
            'storage_used_mb' => 0,
            'transactions_this_month' => 0,
            'api_calls_today' => 0,
            'total_storage_mb' => TenantUsageLog::forMetric('storage_mb')
                ->where('company_id', $companyId)
                ->where('recorded_at', today())
                ->sum('value'),
            'total_api_calls' => TenantUsageLog::forMetric('api_calls')
                ->where('company_id', $companyId)
                ->where('recorded_at', today())
                ->sum('value'),
            'total_transactions' => TenantUsageLog::forMetric('transactions')
                ->where('company_id', $companyId)
                ->whereMonth('recorded_at', now()->month)
                ->sum('value'),
        ];

        if (Schema::hasTable('pos_transactions')) {
            $usage['transactions_this_month'] = \App\Models\PosTransaction::where('company_id', $companyId)
                ->whereMonth('created_at', now()->month)->count();
        }

        if (Schema::hasTable('invoices')) {
            $usage['transactions_this_month'] += \App\Models\Invoice::where('company_id', $companyId)
                ->whereMonth('created_at', now()->month)->count();
        }

        return $usage;
    }

    public function getUsageHistory(int $companyId, string $metric, int $days = 30): array
    {
        return TenantUsageLog::where('company_id', $companyId)
            ->where('metric', $metric)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->orderBy('recorded_at')
            ->get()
            ->map(fn ($log) => [
                'date' => $log->recorded_at->format('Y-m-d'),
                'value' => (float) $log->value,
            ])
            ->toArray();
    }

    public function getTopTenants(string $metric, int $limit = 10): array
    {
        $query = TenantUsageLog::query();

        if ($metric === 'all') {
            return Company::with('usageLogs')
                ->whereHas('usageLogs')
                ->get()
                ->map(function ($company) {
                    return [
                        'company_id' => $company->id,
                        'company_name' => $company->name,
                        'total_usage' => $company->usageLogs()
                            ->whereMonth('recorded_at', now()->month)
                            ->sum('value'),
                    ];
                })
                ->sortByDesc('total_usage')
                ->take($limit)
                ->values()
                ->toArray();
        }

        return TenantUsageLog::forMetric($metric)
            ->whereMonth('recorded_at', now()->month)
            ->select('company_id', DB::raw('SUM(value) as total_usage'))
            ->groupBy('company_id')
            ->orderByDesc('total_usage')
            ->limit($limit)
            ->with('company')
            ->get()
            ->map(function ($log) {
                return [
                    'company_id' => $log->company_id,
                    'company_name' => $log->company?->name ?? 'Unknown',
                    'total_usage' => (float) $log->total_usage,
                ];
            })
            ->toArray();
    }

    public function getDailyUsageSummary(int $companyId, string $metric, int $days = 7): array
    {
        return TenantUsageLog::where('company_id', $companyId)
            ->where('metric', $metric)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->orderBy('recorded_at')
            ->get()
            ->map(fn ($log) => [
                'date' => $log->recorded_at->format('d M'),
                'value' => (float) $log->value,
            ]);
    }

    public function getAllMetricsForCompany(int $companyId): array
    {
        return TenantUsageLog::where('company_id', $companyId)
            ->whereMonth('recorded_at', now()->month)
            ->select('metric', DB::raw('SUM(value) as total'))
            ->groupBy('metric')
            ->get()
            ->pluck('total', 'metric')
            ->toArray();
    }

    public function recordCompanyUsage(int $companyId): void
    {
        $tempService = new static($companyId);

        $company = Company::find($companyId);
        if (!$company) return;

        $tempService->record('users', $company->users()->count());

        if (Schema::hasTable('pos_transactions')) {
            $count = \App\Models\PosTransaction::where('company_id', $companyId)
                ->whereDate('created_at', today())
                ->count();
            $tempService->record('transactions', $count);
        }

        if (Schema::hasTable('audit_logs')) {
            $count = \App\Models\AuditLog::where('company_id', $companyId)
                ->whereDate('created_at', today())
                ->count();
            $tempService->record('api_calls', $count);
        }
    }

    public function recordAllCompaniesUsage(): void
    {
        Company::where('is_active', true)->get()->each(function ($company) {
            try {
                $this->recordCompanyUsage($company->id);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Usage record error for company {$company->id}: " . $e->getMessage());
            }
        });
    }
}
