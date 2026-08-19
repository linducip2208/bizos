<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class DashboardCacheObserver
{
    public function saved(Model $model): void
    {
        $this->bump($model);
    }

    public function deleted(Model $model): void
    {
        $this->bump($model);
    }

    protected function bump(Model $model): void
    {
        $companyId = $model->getAttribute('company_id');

        if (! $companyId && method_exists($model, 'employee')) {
            $companyId = $model->employee?->company_id;
        }

        if (! $companyId && method_exists($model, 'project')) {
            $companyId = $model->project?->company_id;
        }

        if (! $companyId) {
            return;
        }

        $key = "dashboard:version:{$companyId}";
        Cache::forever($key, ((int) Cache::get($key, 1)) + 1);
    }
}
