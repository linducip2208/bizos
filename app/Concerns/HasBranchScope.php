<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait HasBranchScope
{
    protected static function bootHasBranchScope(): void
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();

            if (!$user) {
                return;
            }

            if (in_array($user->role?->slug, ['super-admin', 'admin'])) {
                return;
            }

            $branchId = static::resolveUserBranchId($user);

            if (!$branchId) {
                return;
            }

            $table = $builder->getModel()->getTable();

            if (Schema::hasColumn($table, 'branch_id')) {
                $builder->where("{$table}.branch_id", $branchId);
                return;
            }

            if (Schema::hasColumn($table, 'employee_id')) {
                $builder->whereIn("{$table}.employee_id", function ($query) use ($branchId) {
                    $query->select('id')
                        ->from('employees')
                        ->where('branch_id', $branchId);
                });
                return;
            }
        });
    }

    protected static function resolveUserBranchId($user): ?int
    {
        if ($user->employee?->branch_id) {
            return $user->employee->branch_id;
        }

        return null;
    }

    public static function bootedWithoutBranchScope(callable $callback): mixed
    {
        return static::withoutGlobalScope('branch', $callback);
    }
}
