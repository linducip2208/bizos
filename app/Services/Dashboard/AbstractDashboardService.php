<?php

namespace App\Services\Dashboard;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

abstract class AbstractDashboardService
{
    private static array $columnCache = [];
    abstract protected function build(DashboardFilter $filter, User $user): array;

    final public function get(DashboardFilter $filter, User $user): array
    {
        $permissionHash = sha1(implode('|', $user->getMenuPermissions()));
        $version = (int) Cache::get("dashboard:version:{$filter->companyId}", 1);
        $key = sprintf('dashboard:%s:v%s:%s:%s', static::class, $version, $filter->cacheKey(), $permissionHash);

        return Cache::remember($key, now()->addMinutes(10), fn () => $this->build($filter, $user));
    }

    protected function tenant(Builder $query, DashboardFilter $filter, bool $branch = true): Builder
    {
        $model = $query->getModel();
        $table = $model->getTable();

        return $query
            ->where($model->qualifyColumn('company_id'), $filter->companyId)
            ->when($branch && $filter->branchId && $this->hasColumn($table, 'branch_id'), fn (Builder $query) => $query->where($model->qualifyColumn('branch_id'), $filter->branchId))
            ->when($filter->businessUnitId && $this->hasColumn($table, 'business_unit_id'), fn (Builder $query) => $query->where($model->qualifyColumn('business_unit_id'), $filter->businessUnitId))
            ->when($filter->departmentId && $this->hasColumn($table, 'department_id'), fn (Builder $query) => $query->where($model->qualifyColumn('department_id'), $filter->departmentId))
            ->when($filter->projectId && $this->hasColumn($table, 'project_id'), fn (Builder $query) => $query->where($model->qualifyColumn('project_id'), $filter->projectId));
    }

    protected function delta(float $current, float $previous): ?float
    {
        return $previous == 0.0 ? null : round((($current - $previous) / abs($previous)) * 100, 1);
    }

    private function hasColumn(string $table, string $column): bool
    {
        return self::$columnCache[$table][$column] ??= Schema::hasColumn($table, $column);
    }
}
