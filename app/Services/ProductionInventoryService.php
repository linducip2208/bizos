<?php

namespace App\Services;

use App\Models\ProductionOrder;
use App\Models\ProductionOrderMaterial;
use App\Models\Product;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class ProductionInventoryService
{
    /**
     * Ketika production order selesai: auto-update stock.
     * 1. Deduct raw materials dari stock
     * 2. Add finished goods ke stock
     * 3. Auto-update StockBalance untuk setiap produk
     * 4. Create StockMovement records untuk semua perubahan
     */
    public function onProductionComplete(ProductionOrder $order): void
    {
        DB::transaction(function () use ($order) {
            if ($order->status !== 'in_progress') {
                throw new \InvalidArgumentException('Hanya production order dengan status "in_progress" yang dapat diselesaikan.');
            }

            $producedQty = (float) $order->produced_quantity;
            $rejectedQty = (float) $order->rejected_quantity;

            $this->consumeMaterials($order);
            $this->receiveFinishedGoods($order, $producedQty);

            $order->update([
                'status' => 'completed',
                'actual_end' => now(),
            ]);
        });
    }

    /**
     * Konsumsi material: kurangi stok raw material sesuai issued_quantity.
     */
    public function consumeMaterials(ProductionOrder $order): void
    {
        $materials = $order->materials()->where('status', '!=', 'complete')->get();

        foreach ($materials as $material) {
            $qty = (float) ($material->issued_quantity ?: $material->required_quantity);
            if ($qty <= 0) continue;

            $product = Product::find($material->product_id);
            if (!$product) continue;

            $companyId = $order->company_id;

            $balance = StockBalance::where('company_id', $companyId)
                ->where('product_id', $material->product_id)
                ->first();

            if ($balance) {
                $currentQty = (float) $balance->quantity;
                $newQty = max(0, $currentQty - $qty);
                $balance->update(['quantity' => $newQty]);
            }

            $unitCost = $balance->average_cost ?? (float) ($product->purchase_price ?? 0);
            $runningCost = round($qty * $unitCost, 2);

            StockMovement::create([
                'company_id' => $companyId,
                'product_id' => $material->product_id,
                'movement_type' => 'out',
                'reference_type' => 'production_order',
                'reference_id' => $order->id,
                'quantity_in' => 0,
                'quantity_out' => $qty,
                'unit_cost' => $unitCost,
                'running_quantity' => $qty,
                'running_cost' => $runningCost,
                'notes' => 'Konsumsi material untuk PO #' . $order->po_number,
                'created_by' => auth()->id(),
                'movement_date' => now(),
            ]);

            $material->update(['status' => 'complete']);
        }
    }

    /**
     * Penerimaan barang jadi: tambah stok finished goods.
     */
    public function receiveFinishedGoods(ProductionOrder $order, float $qty): void
    {
        if ($qty <= 0) return;

        $companyId = $order->company_id;

        $balance = StockBalance::firstOrNew([
            'company_id' => $companyId,
            'product_id' => $order->product_id,
        ]);

        $productionCost = $this->calculateProductionCost($order);
        $unitCost = $qty > 0 ? round($productionCost / $qty, 2) : 0;

        $oldQty = (float) ($balance->quantity ?? 0);
        $oldAvgCost = (float) ($balance->average_cost ?? 0);
        $newQty = $oldQty + $qty;
        $newAvgCost = $newQty > 0
            ? round((($oldQty * $oldAvgCost) + ($qty * $unitCost)) / $newQty, 2)
            : $unitCost;

        $balance->fill([
            'quantity' => $newQty,
            'average_cost' => $newAvgCost,
            'last_cost' => $unitCost,
        ]);
        $balance->save();

        StockMovement::create([
            'company_id' => $companyId,
            'product_id' => $order->product_id,
            'movement_type' => 'in',
            'reference_type' => 'production_order',
            'reference_id' => $order->id,
            'quantity_in' => $qty,
            'quantity_out' => 0,
            'unit_cost' => $unitCost,
            'running_quantity' => $qty,
            'running_cost' => round($qty * $unitCost, 2),
            'notes' => 'Hasil produksi PO #' . $order->po_number,
            'created_by' => auth()->id(),
            'movement_date' => now(),
        ]);

        $product = Product::find($order->product_id);
        if ($product) {
            $product->update(['stock' => (float) $product->stock + $qty]);
        }
    }

    /**
     * Cek ketersediaan material sebelum mulai produksi.
     * Returns: {available: bool, shortages: [{product, code, required, available, shortage}]}
     */
    public function checkMaterialAvailability(ProductionOrder $order): array
    {
        $materials = $order->materials()->with('product')->get();
        $shortages = [];
        $available = true;

        foreach ($materials as $material) {
            $required = (float) ($material->required_quantity - $material->issued_quantity);
            if ($required <= 0) continue;

            $balance = StockBalance::where('product_id', $material->product_id)
                ->where('company_id', $order->company_id)
                ->first();

            $onHand = (float) ($balance->quantity ?? 0);
            $shortage = max(0, $required - $onHand);

            if ($shortage > 0) {
                $available = false;
                $shortages[] = [
                    'product_id' => $material->product_id,
                    'product_name' => $material->product->name ?? 'Unknown',
                    'product_code' => $material->product->code ?? '-',
                    'required' => round($required, 4),
                    'available' => round($onHand, 4),
                    'shortage' => round($shortage, 4),
                ];
            }
        }

        return [
            'available' => $available,
            'shortages' => $shortages,
            'total_materials' => $materials->count(),
            'shortage_count' => count($shortages),
        ];
    }

    /**
     * Auto-trigger purchase requisition jika material tidak mencukupi.
     */
    public function createPurchaseRequisitionForShortages(ProductionOrder $order): ?PurchaseRequisition
    {
        $check = $this->checkMaterialAvailability($order);

        if ($check['available']) {
            return null;
        }

        return DB::transaction(function () use ($order, $check) {
            $prNumber = $this->generatePrNumber($order->company_id);

            $pr = PurchaseRequisition::create([
                'company_id' => $order->company_id,
                'pr_number' => $prNumber,
                'department_id' => null,
                'requested_by' => auth()->id(),
                'date_required' => now()->addDays(7),
                'notes' => 'Auto-generated dari PO #' . $order->po_number . ' — kekurangan material produksi.',
                'status' => 'draft',
            ]);

            foreach ($check['shortages'] as $shortage) {
                PurchaseRequisitionItem::create([
                    'purchase_requisition_id' => $pr->id,
                    'product_id' => $shortage['product_id'],
                    'item_name' => $shortage['product_name'],
                    'specification' => 'Material untuk PO #' . $order->po_number,
                    'unit' => 'pcs',
                    'quantity' => $shortage['shortage'],
                    'estimated_price' => 0,
                    'notes' => 'Kekurangan: butuh ' . $shortage['required'] . ', tersedia ' . $shortage['available'],
                ]);
            }

            return $pr;
        });
    }

    /**
     * Hitung biaya produksi dari material + labor.
     */
    protected function calculateProductionCost(ProductionOrder $order): float
    {
        $materialCost = 0;
        foreach ($order->materials as $mat) {
            $balance = StockBalance::where('product_id', $mat->product_id)
                ->where('company_id', $order->company_id)
                ->first();
            $qty = (float) ($mat->issued_quantity ?: $mat->required_quantity);
            $materialCost += $qty * (float) ($balance->average_cost ?? 0);
        }

        $laborCost = 0;
        if ($order->work_center_id) {
            $wc = $order->workCenter;
            if ($wc) {
                foreach ($order->operations as $op) {
                    $routingOp = $op->routingOperation;
                    if ($routingOp) {
                        $totalMinutes = (float) $routingOp->setup_time_minutes
                            + ((float) $routingOp->run_time_minutes_per_unit * (float) $order->produced_quantity);
                        $hours = $totalMinutes / 60;
                        $laborCost += $hours * (float) $wc->hourly_cost;
                    }
                }
            }
        }

        return $materialCost + $laborCost;
    }

    protected function generatePrNumber(int $companyId): string
    {
        $prefix = 'PR-' . date('Ymd');
        $last = PurchaseRequisition::where('company_id', $companyId)
            ->where('pr_number', 'like', $prefix . '%')
            ->orderBy('pr_number', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->pr_number, -4);
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        return $prefix . '-' . $newNum;
    }
}
