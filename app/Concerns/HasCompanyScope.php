<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasCompanyScope
{
    protected static function bootHasCompanyScope(): void
    {
        static::addGlobalScope('company', function (Builder $builder) {
            $companyId = session('current_company_id');

            if (!$companyId && auth()->check()) {
                $companyId = auth()->user()->company_id;
            }

            if ($companyId) {
                $table = $builder->getModel()->getTable();
                $builder->where($table . '.company_id', $companyId);
            }
        });

        static::creating(function ($model) {
            if (!$model->company_id) {
                $companyId = session('current_company_id');

                if (!$companyId && auth()->check()) {
                    $companyId = auth()->user()->company_id;
                }

                if ($companyId) {
                    $model->company_id = $companyId;
                }
            }
        });
    }

    public static function bootedWithoutCompanyScope(callable $callback): mixed
    {
        return static::withoutGlobalScope('company', $callback);
    }

    public static function allWithoutScope(): \Illuminate\Database\Eloquent\Collection
    {
        return static::withoutGlobalScope('company')->get();
    }
}
