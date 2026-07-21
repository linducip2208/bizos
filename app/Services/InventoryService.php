<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryService
{
    public function reserveStock(int $productId, int $warehouseId, float $qty, string $refType, int $refId): StockReservation
    {
        return DB::transaction(function () use ($productId, $warehouseId, $qty, $refType, $refId) {
            $product = Product::findOrFail($productId);
            $companyId = $product->company_id;

            $availability = $this->checkStockAvailability($product, $qty, $warehouseId);

            if (!$availability['available'] || $availability['available_quantity'] < $qty) {
                throw new \RuntimeException(
                    "Stok tidak mencukupi. Tersedia: {$availability['available_quantity']}, Dibutuhkan: {$qty}"
                );
            }

            return StockReservation::create([
                'company_id' => $companyId,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'reference_type' => $refType,
                'reference_id' => $refId,
                'quantity' => $qty,
                'expires_at' => Carbon::now()->addHours(24),
            ]);
        });
    }

    public function releaseReservation(StockReservation $reservation): void
    {
        $reservation->delete();
    }

    public function autoReserve(Product $product, float $qty): ?StockReservation
    {
        $warehouses = Warehouse::where('company_id', $product->company_id)
            ->where('is_active', true)
            ->get();

        $remaining = $qty;

        foreach ($warehouses as $warehouse) {
            $availability = $this->checkStockAvailability($product, $remaining, $warehouse->id);

            if ($availability['available_quantity'] <= 0) {
                continue;
            }

            $reserveQty = min($remaining, $availability['available_quantity']);

            try {
                return $this->reserveStock(
                    $product->id,
                    $warehouse->id,
                    $reserveQty,
                    'auto_reserve',
                    $product->id
                );
            } catch (\RuntimeException $e) {
                continue;
            }
        }

        return null;
    }

    public function checkStockAvailability(Product $product, float $qty, ?int $warehouseId = null): array
    {
        $stockQuery = StockBalance::where('product_id', $product->id);

        if ($warehouseId) {
            $stockQuery->where('warehouse_id', $warehouseId);
        }

        $stockOnHand = (float) $stockQuery->sum('quantity');

        $reservedQty = StockReservation::where('product_id', $product->id)
            ->where('expires_at', '>', Carbon::now())
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->sum('quantity');

        $reservedQty = (float) $reservedQty;
        $availableQty = max(0, $stockOnHand - $reservedQty);

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'warehouse_id' => $warehouseId,
            'stock_on_hand' => $stockOnHand,
            'reserved_quantity' => $reservedQty,
            'available_quantity' => $availableQty,
            'requested_quantity' => $qty,
            'available' => $availableQty >= $qty,
            'shortage' => max(0, $qty - $availableQty),
        ];
    }

    public function transferStock(int $fromWarehouseId, int $toWarehouseId, int $productId, float $qty): void
    {
        DB::transaction(function () use ($fromWarehouseId, $toWarehouseId, $productId, $qty) {
            $product = Product::findOrFail($productId);
            $companyId = $product->company_id;

            $availability = $this->checkStockAvailability($product, $qty, $fromWarehouseId);

            if (!$availability['available']) {
                throw new \RuntimeException(
                    "Stok di gudang asal tidak mencukupi. Tersedia: {$availability['available_quantity']}, Dibutuhkan: {$qty}"
                );
            }

            $fromBalance = StockBalance::where('product_id', $productId)
                ->where('warehouse_id', $fromWarehouseId)
                ->first();

            if (!$fromBalance) {
                throw new \RuntimeException('Stok tidak ditemukan di gudang asal.');
            }

            $fromBalance->quantity = max(0, (float) $fromBalance->quantity - $qty);
            $fromBalance->save();

            StockMovement::create([
                'company_id' => $companyId,
                'product_id' => $productId,
                'warehouse_id' => $fromWarehouseId,
                'movement_type' => 'transfer_out',
                'reference_type' => 'stock_transfer',
                'reference_id' => $fromWarehouseId,
                'quantity_in' => 0,
                'quantity_out' => $qty,
                'unit_cost' => $fromBalance->average_cost ?? 0,
                'running_quantity' => $qty,
                'running_cost' => round($qty * ($fromBalance->average_cost ?? 0), 2),
                'movement_date' => now(),
                'notes' => "Transfer stok ke gudang #{$toWarehouseId}",
                'created_by' => auth()->user()?->employee_id,
            ]);

            $toBalance = StockBalance::firstOrNew([
                'company_id' => $companyId,
                'product_id' => $productId,
                'warehouse_id' => $toWarehouseId,
            ]);

            $oldQty = (float) ($toBalance->quantity ?? 0);
            $oldAvgCost = (float) ($toBalance->average_cost ?? 0);
            $unitCost = $fromBalance->average_cost ?? 0;
            $newQty = $oldQty + $qty;

            $toBalance->fill([
                'quantity' => $newQty,
                'average_cost' => $newQty > 0
                    ? round((($oldQty * $oldAvgCost) + ($qty * $unitCost)) / $newQty, 2)
                    : $unitCost,
                'last_cost' => $unitCost,
            ]);
            $toBalance->save();

            StockMovement::create([
                'company_id' => $companyId,
                'product_id' => $productId,
                'warehouse_id' => $toWarehouseId,
                'movement_type' => 'transfer_in',
                'reference_type' => 'stock_transfer',
                'reference_id' => $toWarehouseId,
                'quantity_in' => $qty,
                'quantity_out' => 0,
                'unit_cost' => $unitCost,
                'running_quantity' => $qty,
                'running_cost' => round($qty * $unitCost, 2),
                'movement_date' => now(),
                'notes' => "Transfer stok dari gudang #{$fromWarehouseId}",
                'created_by' => auth()->user()?->employee_id,
            ]);
        });
    }

    public function convertUnit(float $quantity, int $fromUnitId, int $toUnitId): float
    {
        if ($fromUnitId === $toUnitId) {
            return $quantity;
        }

        $conversion = \App\Models\UnitConversion::where('from_unit_id', $fromUnitId)
            ->where('to_unit_id', $toUnitId)
            ->first();

        if (!$conversion) {
            $reverseConversion = \App\Models\UnitConversion::where('from_unit_id', $toUnitId)
                ->where('to_unit_id', $fromUnitId)
                ->first();

            if ($reverseConversion && (float) $reverseConversion->multiplier > 0) {
                return $quantity / (float) $reverseConversion->multiplier;
            }

            throw new \RuntimeException('Konversi unit tidak ditemukan.');
        }

        return $quantity * (float) $conversion->multiplier;
    }

    public function getExpiringBatches(?int $companyId = null, int $daysThreshold = 30): array
    {
        $query = \App\Models\Batch::with(['product', 'warehouse'])
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->addDays($daysThreshold));

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->orderBy('expiry_date')
            ->get()
            ->map(fn ($batch) => [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'product_name' => $batch->product->name ?? '-',
                'warehouse_name' => $batch->warehouse->name ?? '-',
                'quantity' => (float) $batch->quantity,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'days_remaining' => Carbon::now()->diffInDays($batch->expiry_date, false),
            ])
            ->toArray();
    }
}
