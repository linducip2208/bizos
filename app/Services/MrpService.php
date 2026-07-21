<?php

namespace App\Services;

use App\Models\BillOfMaterial;
use App\Models\BomItem;
use App\Models\ProductionOrder;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockBalance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MrpService
{
    protected ManufacturingService $manufacturing;

    public function __construct(ManufacturingService $manufacturing)
    {
        $this->manufacturing = $manufacturing;
    }

    /**
     * Full MRP run for all products in a company.
     */
    public function runFullMrp(int $companyId, int $horizonDays = 30): array
    {
        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        $results = [];
        $alerts = [];

        foreach ($products as $product) {
            $mrp = $this->manufacturing->calculateMrp($product->id, $horizonDays);

            $netRequirements = collect($mrp['days'])->sum('net_requirements');
            if ($netRequirements > 0) {
                $alerts[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'current_stock' => $mrp['current_stock'],
                    'total_net_requirements' => round($netRequirements, 4),
                    'planned_releases' => $mrp['planned_order_releases'],
                ];
            }

            $results[$product->id] = $mrp;
        }

        return [
            'company_id' => $companyId,
            'horizon_days' => $horizonDays,
            'products_analyzed' => $products->count(),
            'products_with_shortage' => count($alerts),
            'alerts' => $alerts,
            'details' => $results,
        ];
    }

    /**
     * MRP for single product.
     */
    public function runSingleMrp(Product $product, int $horizonDays = 30): array
    {
        return $this->manufacturing->calculateMrp($product->id, $horizonDays);
    }

    /**
     * Generate purchase suggestions from MRP.
     */
    public function generatePurchaseSuggestions(int $companyId, int $horizonDays = 30): array
    {
        $mrpResult = $this->runFullMrp($companyId, $horizonDays);
        $suggestions = [];

        foreach ($mrpResult['alerts'] as $alert) {
            $firstRelease = $alert['planned_releases'][0] ?? null;
            if (!$firstRelease) continue;

            $supplier = $this->findBestSupplier($alert['product_id']);

            $suggestions[] = [
                'product_id' => $alert['product_id'],
                'product_name' => $alert['product_name'],
                'product_code' => $alert['product_code'],
                'current_stock' => $alert['current_stock'],
                'quantity_needed' => $firstRelease['quantity'],
                'suggested_supplier_id' => $supplier?->id,
                'suggested_supplier_name' => $supplier?->name ?? 'Belum ada supplier',
                'suggested_order_date' => Carbon::parse($firstRelease['date'])->subDays(7)->format('Y-m-d'),
                'required_date' => $firstRelease['date'],
            ];
        }

        return $suggestions;
    }

    protected function findBestSupplier(int $productId): ?\App\Models\Supplier
    {
        $item = PurchaseOrderItem::where('product_id', $productId)
            ->with('purchaseOrder.supplier')
            ->orderBy('created_at', 'desc')
            ->first();

        return $item?->purchaseOrder?->supplier;
    }

    /**
     * Generate production suggestions.
     */
    public function generateProductionSuggestions(int $companyId, int $horizonDays = 30): array
    {
        $suggestions = [];

        $boms = BillOfMaterial::with('product')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        foreach ($boms as $bom) {
            $product = $bom->product;
            if (!$product) continue;

            $mrp = $this->manufacturing->calculateMrp($product->id, $horizonDays);

            $netReq = collect($mrp['days'])->sum('net_requirements');
            if ($netReq <= 0) continue;

            $firstRelease = $mrp['planned_order_releases'][0] ?? null;
            $bomBatchQty = (float) $bom->quantity;
            $batches = $bomBatchQty > 0 ? ceil($firstRelease['quantity'] / $bomBatchQty) : 1;
            $produceQty = $batches * $bomBatchQty;

            if ($produceQty <= 0) continue;

            $canProduce = $this->checkMaterialAvailability($bom, $produceQty);

            $suggestions[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_code' => $product->code,
                'bom_id' => $bom->id,
                'bom_name' => $bom->name,
                'suggested_quantity' => round($produceQty, 4),
                'suggested_start_date' => Carbon::parse($firstRelease['date'])->subDays(3)->format('Y-m-d'),
                'required_date' => $firstRelease['date'],
                'material_availability' => $canProduce,
            ];
        }

        return $suggestions;
    }

    protected function checkMaterialAvailability(BillOfMaterial $bom, float $quantity): bool
    {
        $components = $this->manufacturing->explodeBom($bom, $quantity);

        foreach ($components as $comp) {
            $stock = StockBalance::where('product_id', $comp['product_id'])
                ->where('company_id', $bom->company_id)
                ->sum('quantity');

            if ((float) $stock < $comp['required_quantity']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get MRP exceptions / alerts.
     */
    public function getExceptions(int $companyId, int $horizonDays = 30): array
    {
        $now = Carbon::now();

        $lateOrders = ProductionOrder::where('company_id', $companyId)
            ->whereIn('status', ['planned', 'in_progress'])
            ->where('planned_end', '<', $now)
            ->with(['product', 'workCenter'])
            ->get()
            ->map(fn($po) => [
                'type' => 'late_order',
                'po_number' => $po->po_number,
                'product_name' => $po->product->name ?? 'Unknown',
                'planned_end' => $po->planned_end?->format('Y-m-d'),
                'days_late' => $po->planned_end ? $now->diffInDays($po->planned_end) : 0,
                'status' => $po->status,
            ])
            ->toArray();

        $stockBelowSafety = StockBalance::with('product')
            ->whereHas('product', fn($q) => $q->where('company_id', $companyId))
            ->whereRaw('quantity <= (SELECT min_stock FROM products WHERE products.id = stock_balances.product_id)')
            ->whereRaw('(SELECT min_stock FROM products WHERE products.id = stock_balances.product_id) > 0')
            ->get()
            ->map(fn($sb) => [
                'type' => 'stock_below_safety',
                'product_name' => $sb->product->name ?? 'Unknown',
                'product_code' => $sb->product->code ?? '-',
                'current_stock' => (float) $sb->quantity,
                'safety_stock' => (float) ($sb->product->min_stock ?? 0),
                'shortage' => round((float) ($sb->product->min_stock ?? 0) - (float) $sb->quantity, 4),
            ])
            ->toArray();

        $excessInventory = StockBalance::with('product')
            ->whereHas('product', fn($q) => $q->where('company_id', $companyId))
            ->whereRaw('quantity > (SELECT max_stock FROM products WHERE products.id = stock_balances.product_id)')
            ->whereRaw('(SELECT max_stock FROM products WHERE products.id = stock_balances.product_id) > 0')
            ->get()
            ->map(fn($sb) => [
                'type' => 'excess_inventory',
                'product_name' => $sb->product->name ?? 'Unknown',
                'product_code' => $sb->product->code ?? '-',
                'current_stock' => (float) $sb->quantity,
                'max_stock' => (float) ($sb->product->max_stock ?? 0),
                'excess' => round((float) $sb->quantity - (float) ($sb->product->max_stock ?? 0), 4),
            ])
            ->toArray();

        $capacityOverload = [];
        $workCenters = \App\Models\WorkCenter::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        foreach ($workCenters as $wc) {
            $util = app(ManufacturingService::class)->getCapacityUtilization($wc, 'weekly');
            if ($util['utilization_percent'] > 90) {
                $capacityOverload[] = [
                    'type' => 'capacity_overload',
                    'work_center' => $wc->name,
                    'utilization_percent' => $util['utilization_percent'],
                    'capacity_hours' => $util['capacity_hours'],
                    'planned_hours' => $util['planned_hours'],
                ];
            }
        }

        return [
            'company_id' => $companyId,
            'run_time' => $now->format('Y-m-d H:i:s'),
            'late_orders' => $lateOrders,
            'stock_below_safety' => $stockBelowSafety,
            'excess_inventory' => $excessInventory,
            'capacity_overload' => $capacityOverload,
            'total_exceptions' => count($lateOrders) + count($stockBelowSafety)
                + count($excessInventory) + count($capacityOverload),
        ];
    }
}
