<?php

namespace App\Services\Dashboard;

use App\Models\AuditLog;
use App\Models\DataBreach;
use App\Models\DpiaAssessment;
use App\Models\IsoIncident;
use App\Models\User;

final class ComplianceDashboardService extends AbstractDashboardService
{
    protected function build(DashboardFilter $filter, User $user): array
    {
        return ['cards' => [
            ['label' => 'Insiden ISO Terbuka', 'value' => $this->tenant(IsoIncident::query(), $filter, false)->whereNotIn('status', ['closed', 'resolved'])->count(), 'format' => 'number'],
            ['label' => 'Data Breach Terbuka', 'value' => $this->tenant(DataBreach::query(), $filter, false)->whereNotIn('status', ['closed', 'resolved'])->count(), 'format' => 'number'],
            ['label' => 'DPIA Aktif', 'value' => $this->tenant(DpiaAssessment::query(), $filter, false)->whereNotIn('status', ['approved', 'closed'])->count(), 'format' => 'number'],
            ['label' => 'Aktivitas Audit', 'value' => $this->tenant(AuditLog::query(), $filter, false)->whereBetween('created_at', [$filter->dateFrom, $filter->dateTo])->count(), 'format' => 'number'],
        ]];
    }
}
