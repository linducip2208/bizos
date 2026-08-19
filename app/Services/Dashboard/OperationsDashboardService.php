<?php

namespace App\Services\Dashboard;

use App\Models\DeliveryOrder;
use App\Models\MaintenanceRequest;
use App\Models\ProductionOrder;
use App\Models\QualityCheck;
use App\Models\User;

final class OperationsDashboardService extends AbstractDashboardService
{
    protected function build(DashboardFilter $filter, User $user): array
    {
        return ['cards' => [
            ['label' => 'Produksi Aktif', 'value' => $this->tenant(ProductionOrder::query(), $filter, false)->whereIn('status', ['planned', 'in_progress'])->count(), 'format' => 'number'],
            ['label' => 'Checklist QC Aktif', 'value' => $this->tenant(QualityCheck::query(), $filter, false)->where('is_active', true)->count(), 'format' => 'number'],
            ['label' => 'Maintenance Terbuka', 'value' => $this->tenant(MaintenanceRequest::query(), $filter, false)->whereNotIn('status', ['completed', 'cancelled'])->count(), 'format' => 'number'],
            ['label' => 'Delivery Berjalan', 'value' => $this->tenant(DeliveryOrder::query(), $filter)->whereNotIn('status', ['delivered', 'cancelled'])->count(), 'format' => 'number'],
        ]];
    }
}
