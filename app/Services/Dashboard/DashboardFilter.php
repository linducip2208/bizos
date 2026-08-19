<?php

namespace App\Services\Dashboard;

use Carbon\CarbonImmutable;

final readonly class DashboardFilter
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public ?int $businessUnitId,
        public ?int $departmentId,
        public ?int $projectId,
        public CarbonImmutable $dateFrom,
        public CarbonImmutable $dateTo,
        public string $comparisonPeriod,
        public string $currency,
    ) {}

    public function previousPeriod(): array
    {
        if ($this->comparisonPeriod === 'previous_year') {
            return [$this->dateFrom->subYear(), $this->dateTo->subYear()];
        }

        $days = $this->dateFrom->diffInDays($this->dateTo) + 1;

        return [
            $this->dateFrom->subDays($days),
            $this->dateTo->subDays($days),
        ];
    }

    public function cacheKey(): string
    {
        return implode(':', [
            $this->companyId,
            $this->branchId ?: 'all',
            $this->businessUnitId ?: 'all',
            $this->departmentId ?: 'all',
            $this->projectId ?: 'all',
            $this->dateFrom->toDateString(),
            $this->dateTo->toDateString(),
            $this->comparisonPeriod,
            $this->currency,
        ]);
    }

    public function toArray(): array
    {
        return [
            'company_id' => $this->companyId,
            'branch_id' => $this->branchId,
            'business_unit_id' => $this->businessUnitId,
            'department_id' => $this->departmentId,
            'project_id' => $this->projectId,
            'date_from' => $this->dateFrom->toDateString(),
            'date_to' => $this->dateTo->toDateString(),
            'comparison_period' => $this->comparisonPeriod,
            'currency' => $this->currency,
        ];
    }
}
