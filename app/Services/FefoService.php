<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockBalance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FefoService
{
    public function getFefoBatch(Product $product, float $quantity, ?int $warehouseId = null): array
    {
        $query = StockBalance::where('product_id', $product->id)
            ->where('company_id', $product->company_id)
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->orderBy('expiry_date', 'asc')
            ->orderBy('lot_number', 'asc');

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $batches = $query->get();
        $allocated = [];
        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) break;

            $availableQty = (float) $batch->quantity;
            $takeQty = min($remaining, $availableQty);

            if ($takeQty > 0) {
                $allocated[] = [
                    'stock_balance_id' => $batch->id,
                    'warehouse_id' => $batch->warehouse_id,
                    'lot_number' => $batch->lot_number,
                    'expiry_date' => $batch->expiry_date ? Carbon::parse($batch->expiry_date)->format('Y-m-d') : null,
                    'manufacturing_date' => $batch->manufacturing_date ? Carbon::parse($batch->manufacturing_date)->format('Y-m-d') : null,
                    'allocated_quantity' => $takeQty,
                    'unit_cost' => (float) $batch->average_cost,
                    'days_to_expiry' => $batch->expiry_date ? max(0, Carbon::now()->diffInDays(Carbon::parse($batch->expiry_date), false)) : null,
                ];
                $remaining -= $takeQty;
            }
        }

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'requested_quantity' => $quantity,
            'allocated_quantity' => $quantity - $remaining,
            'unfulfilled_quantity' => max(0, $remaining),
            'batches' => $allocated,
        ];
    }

    public function allocateStock(Product $product, float $quantity, int $warehouseId): array
    {
        $allocation = $this->getFefoBatch($product, $quantity, $warehouseId);

        $batchIds = [];
        foreach ($allocation['batches'] as $batch) {
            $stockBalance = StockBalance::find($batch['stock_balance_id']);
            if ($stockBalance) {
                $newQty = max(0, (float) $stockBalance->quantity - $batch['allocated_quantity']);
                $stockBalance->update(['quantity' => $newQty]);
                $batchIds[] = $batch['stock_balance_id'];
            }
        }

        $allocation['updated_batch_ids'] = $batchIds;

        return $allocation;
    }

    public function getExpiringItems(int $warehouseId, int $days = 90): Collection
    {
        $cutoffDate = Carbon::now()->addDays($days);

        return StockBalance::with(['product', 'warehouse'])
            ->where('warehouse_id', $warehouseId)
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $cutoffDate)
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($batch) {
                $expiryDate = Carbon::parse($batch->expiry_date);
                $daysLeft = max(0, Carbon::now()->diffInDays($expiryDate, false));

                return [
                    'stock_balance_id' => $batch->id,
                    'product_id' => $batch->product_id,
                    'product_name' => $batch->product?->name,
                    'product_code' => $batch->product?->code,
                    'lot_number' => $batch->lot_number,
                    'quantity' => (float) $batch->quantity,
                    'expiry_date' => $expiryDate->format('Y-m-d'),
                    'days_to_expiry' => (int) $daysLeft,
                    'unit_cost' => (float) $batch->average_cost,
                    'total_value' => round((float) $batch->quantity * (float) $batch->average_cost, 2),
                    'warehouse_name' => $batch->warehouse?->name,
                    'severity' => match (true) {
                        $daysLeft <= 0 => 'expired',
                        $daysLeft <= 30 => 'critical',
                        $daysLeft <= 60 => 'warning',
                        default => 'watch',
                    },
                ];
            });
    }

    public function getFefoPickList(array $productIds, array $warehouseIds): array
    {
        $pickList = [];

        foreach ($productIds as $productId) {
            $batches = StockBalance::where('product_id', $productId)
                ->where('quantity', '>', 0)
                ->whereNotNull('expiry_date')
                ->whereIn('warehouse_id', $warehouseIds)
                ->orderBy('expiry_date', 'asc')
                ->get();

            foreach ($batches as $batch) {
                $pickList[] = [
                    'product_id' => $productId,
                    'warehouse_id' => $batch->warehouse_id,
                    'lot_number' => $batch->lot_number,
                    'quantity' => (float) $batch->quantity,
                    'expiry_date' => $batch->expiry_date ? Carbon::parse($batch->expiry_date)->format('Y-m-d') : null,
                    'pick_order' => count($pickList) + 1,
                ];
            }
        }

        return $pickList;
    }

    public function getBatchDetail(int $stockBalanceId): array
    {
        $batch = StockBalance::with(['product', 'product.category', 'warehouse'])->findOrFail($stockBalanceId);

        $expiryDate = $batch->expiry_date ? Carbon::parse($batch->expiry_date) : null;
        $daysToExpiry = $expiryDate ? (int) max(0, Carbon::now()->diffInDays($expiryDate, false)) : null;

        $status = 'active';
        if ($expiryDate) {
            if ($expiryDate->isPast()) $status = 'expired';
            elseif ($daysToExpiry <= 30) $status = 'critical';
            elseif ($daysToExpiry <= 60) $status = 'warning';
        }

        return [
            'id' => $batch->id,
            'product_name' => $batch->product?->name,
            'product_code' => $batch->product?->code,
            'product_category' => $batch->product?->category?->name,
            'warehouse_name' => $batch->warehouse?->name,
            'lot_number' => $batch->lot_number,
            'manufacturing_date' => $batch->manufacturing_date ? Carbon::parse($batch->manufacturing_date)->format('Y-m-d') : null,
            'expiry_date' => $expiryDate?->format('Y-m-d'),
            'days_to_expiry' => $daysToExpiry,
            'quantity' => (float) $batch->quantity,
            'average_cost' => (float) $batch->average_cost,
            'last_cost' => (float) $batch->last_cost,
            'total_value' => round((float) $batch->quantity * (float) $batch->average_cost, 2),
            'status' => $status,
        ];
    }
}
