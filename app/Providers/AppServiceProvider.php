<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\LicenseClient::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'leave' => \App\Models\Leave::class,
            'reimbursement' => \App\Models\Reimbursement::class,
            'budget' => \App\Models\Budget::class,
            'purchase_requisition' => \App\Models\PurchaseRequisition::class,
            'purchase_order' => \App\Models\PurchaseOrder::class,
            'overtime' => \App\Models\Overtime::class,
        ]);
    }
}
