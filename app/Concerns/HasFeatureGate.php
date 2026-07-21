<?php

namespace App\Concerns;

use App\Services\BillingService;

trait HasFeatureGate
{
    protected ?BillingService $billingService = null;

    protected function billingService(): BillingService
    {
        if ($this->billingService === null) {
            $this->billingService = app(BillingService::class);
        }

        return $this->billingService;
    }

    public function canAccessFeature(string $feature): bool
    {
        $company = $this->getFeatureGateCompany();

        if (!$company) {
            return false;
        }

        $features = $this->billingService()->getFeatureAccess($company);

        return in_array($feature, $features) || in_array('all_features', $features);
    }

    public function getAccessibleFeatures(): array
    {
        $company = $this->getFeatureGateCompany();

        if (!$company) {
            return [];
        }

        return $this->billingService()->getFeatureAccess($company);
    }

    protected function getFeatureGateCompany(): ?\App\Models\Company
    {
        if (method_exists($this, 'company') && $this->company) {
            return $this->company;
        }

        if (method_exists($this, 'company_id') && $this->company_id) {
            return \App\Models\Company::find($this->company_id);
        }

        $user = auth()->user();
        if ($user && $user->company_id) {
            return \App\Models\Company::find($user->company_id);
        }

        return null;
    }
}
