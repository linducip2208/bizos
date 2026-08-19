<?php

namespace App\Services\Dashboard;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\StockBalance;
use App\Models\User;

final class ProcurementDashboardService extends AbstractDashboardService
{
    protected function build(DashboardFilter $filter, User $user): array
    {
        return ['cards' => [
            ['label' => 'PR Menunggu', 'value' => $this->tenant(PurchaseRequisition::query(), $filter)->where('status', 'submitted')->count(), 'format' => 'number'],
            ['label' => 'PO Aktif', 'value' => $this->tenant(PurchaseOrder::query(), $filter)->whereIn('status', ['approved', 'partially_received'])->count(), 'format' => 'number'],
            ['label' => 'Nilai PO Periode', 'value' => (float) $this->tenant(PurchaseOrder::query(), $filter)->whereBetween('order_date', [$filter->dateFrom, $filter->dateTo])->sum('total'), 'format' => 'currency'],
            ['label' => 'Stok Minimum', 'value' => StockBalance::query()->where('stock_balances.company_id', $filter->companyId)->join('products', 'products.id', '=', 'stock_balances.product_id')->whereColumn('stock_balances.quantity', '<=', 'products.min_stock')->count(), 'format' => 'number'],
            ['label' => 'Produk Aktif', 'value' => $this->tenant(Product::query(), $filter, false)->where('is_active', true)->count(), 'format' => 'number'],
        ]];
    }
}
