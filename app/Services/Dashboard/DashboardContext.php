<?php

namespace App\Services\Dashboard;

use App\Models\Branch;
use App\Models\BusinessUnit;
use App\Models\Company;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\AuthorizationException;

final class DashboardContext
{
    public function forUser(User $user, array $input = []): DashboardFilter
    {
        $requestedCompany = (int) ($input['company_id'] ?? $user->company_id);
        $isSuperAdmin = $user->role?->slug === 'super-admin';

        if (! $isSuperAdmin && $requestedCompany !== (int) $user->company_id) {
            throw new AuthorizationException('Perusahaan tidak dapat diakses.');
        }

        if (! Company::query()->whereKey($requestedCompany)->where('is_active', true)->exists()) {
            throw new AuthorizationException('Perusahaan aktif tidak ditemukan.');
        }

        $branchId = filled($input['branch_id'] ?? null) ? (int) $input['branch_id'] : null;

        if ($branchId && ! Branch::query()->whereKey($branchId)->where('company_id', $requestedCompany)->exists()) {
            throw new AuthorizationException('Cabang tidak termasuk perusahaan aktif.');
        }

        $businessUnitId = filled($input['business_unit_id'] ?? null) ? (int) $input['business_unit_id'] : null;
        $departmentId = filled($input['department_id'] ?? null) ? (int) $input['department_id'] : null;
        $projectId = filled($input['project_id'] ?? null) ? (int) $input['project_id'] : null;

        $this->assertBelongsToCompany(BusinessUnit::class, $businessUnitId, $requestedCompany, 'Unit bisnis');
        $this->assertBelongsToCompany(Department::class, $departmentId, $requestedCompany, 'Departemen');
        $this->assertBelongsToCompany(Project::class, $projectId, $requestedCompany, 'Proyek');

        $dateTo = CarbonImmutable::parse($input['date_to'] ?? now()->toDateString())->endOfDay();
        $dateFrom = CarbonImmutable::parse($input['date_from'] ?? now()->startOfMonth()->toDateString())->startOfDay();

        if ($dateFrom->greaterThan($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->startOfDay(), $dateFrom->endOfDay()];
        }

        return new DashboardFilter(
            companyId: $requestedCompany,
            branchId: $branchId,
            businessUnitId: $businessUnitId,
            departmentId: $departmentId,
            projectId: $projectId,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            comparisonPeriod: (string) ($input['comparison_period'] ?? 'previous_period'),
            currency: strtoupper((string) ($input['currency'] ?? 'IDR')),
        );
    }

    /** @param class-string<\Illuminate\Database\Eloquent\Model> $model */
    private function assertBelongsToCompany(string $model, ?int $id, int $companyId, string $label): void
    {
        if ($id && ! $model::query()->whereKey($id)->where('company_id', $companyId)->exists()) {
            throw new AuthorizationException("{$label} tidak termasuk perusahaan aktif.");
        }
    }
}
