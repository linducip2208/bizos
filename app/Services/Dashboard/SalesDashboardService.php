<?php

namespace App\Services\Dashboard;

use App\Models\Client;
use App\Models\Deal;
use App\Models\SalesOrder;
use App\Models\User;

final class SalesDashboardService extends AbstractDashboardService
{
    protected function build(DashboardFilter $filter, User $user): array
    {
        $deals = $this->tenant(Deal::query(), $filter, false);
        $orders = $this->tenant(SalesOrder::query(), $filter);

        return ['cards' => [
            ['label' => 'Pipeline Aktif', 'value' => (float) (clone $deals)->whereNotIn('status', ['won', 'lost'])->sum('expected_value'), 'format' => 'currency'],
            ['label' => 'Deal Menang', 'value' => (clone $deals)->where('status', 'won')->whereBetween('actual_close_date', [$filter->dateFrom, $filter->dateTo])->count(), 'format' => 'number'],
            ['label' => 'Sales Order', 'value' => (clone $orders)->whereBetween('order_date', [$filter->dateFrom, $filter->dateTo])->count(), 'format' => 'number'],
            ['label' => 'Klien Aktif', 'value' => $this->tenant(Client::query(), $filter, false)->where('is_active', true)->count(), 'format' => 'number'],
        ]];
    }
}
