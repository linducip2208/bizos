<?php

namespace App\Services;

use App\Models\BillOfMaterial;
use App\Models\BomItem;
use App\Models\Machine;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderMaterial;
use App\Models\ProductionOrderOperation;
use App\Models\ProductionPlan;
use App\Models\ProductionQcCheck;
use App\Models\RoutingOperation;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\SubcontractOrder;
use App\Models\WasteLog;
use App\Models\WorkCenter;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManufacturingService
{
    /**
     * BOM explosion: flatten multi-level BOM to all leaf-level components.
     */
    public function explodeBom(BillOfMaterial $bom, float $quantity = 1): array
    {
        $components = [];
        $bomItems = $bom->bomItems()->with('product')->get();

        foreach ($bomItems as $item) {
            $subBom = BillOfMaterial::where('product_id', $item->product_id)
                ->where('is_active', true)
                ->first();

            $qtyPerUnit = (float) $item->quantity_per_unit;
            $scrapFactor = 1 + ((float) $item->scrap_percent / 100);
            $requiredQty = $quantity * $qtyPerUnit * $scrapFactor;

            if ($subBom) {
                $subComponents = $this->explodeBom($subBom, $requiredQty);
                $components = array_merge($components, $subComponents);
            } else {
                $components[] = [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? 'Unknown',
                    'product_code' => $item->product->code ?? '-',
                    'quantity_per_unit' => $qtyPerUnit,
                    'required_quantity' => round($requiredQty, 4),
                    'unit' => $item->unit,
                    'is_critical' => $item->is_critical,
                    'bom_item_id' => $item->id,
                ];
            }
        }

        return $components;
    }

    /**
     * MRP calculation for a single product over a horizon.
     */
    public function calculateMrp(int $productId, int $horizonDays = 30): array
    {
        $product = \App\Models\Product::findOrFail($productId);
        $startDate = Carbon::now()->startOfDay();
        $endDate = $startDate->copy()->addDays($horizonDays);

        $stockBalance = StockBalance::where('product_id', $productId)
            ->where('company_id', $product->company_id)
            ->sum('quantity');
        $stockBalance = (float) $stockBalance;

        $days = [];
        $onHand = $stockBalance;
        $plannedReleases = [];

        for ($d = 0; $d <= $horizonDays; $d++) {
            $date = $startDate->copy()->addDays($d);
            $dateStr = $date->format('Y-m-d');

            $grossReq = $this->getGrossRequirement($productId, $date);
            $scheduledReceipts = $this->getScheduledReceipts($productId, $date);

            $projectedOnHand = $onHand + $scheduledReceipts - $grossReq;

            $netReq = 0;
            if ($projectedOnHand < 0) {
                $netReq = abs($projectedOnHand);

                $plannedReleases[] = [
                    'date' => $dateStr,
                    'quantity' => round($netReq, 4),
                    'product_id' => $productId,
                    'product_name' => $product->name,
                ];

                $projectedOnHand = 0;
            }

            $days[] = [
                'date' => $dateStr,
                'gross_requirements' => round($grossReq, 4),
                'scheduled_receipts' => round($scheduledReceipts, 4),
                'projected_on_hand' => round($projectedOnHand, 4),
                'net_requirements' => round(max(0, $netReq), 4),
            ];

            $onHand = $projectedOnHand;
        }

        return [
            'product_id' => $productId,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'current_stock' => $stockBalance,
            'horizon_days' => $horizonDays,
            'days' => $days,
            'planned_order_releases' => $plannedReleases,
        ];
    }

    protected function getGrossRequirement(int $productId, Carbon $date): float
    {
        $salesDemand = PosTransactionItem::where('product_id', $productId)
            ->whereHas('transaction', fn($q) => $q->whereDate('transaction_date', $date))
            ->sum('quantity');

        $productionDemand = ProductionOrderMaterial::where('product_id', $productId)
            ->whereHas('productionOrder', fn($q) => $q->whereIn('status', ['planned', 'in_progress']))
            ->sum(DB::raw('required_quantity - issued_quantity'));

        return (float) $salesDemand + (float) $productionDemand;
    }

    protected function getScheduledReceipts(int $productId, Carbon $date): float
    {
        $poReceipts = \App\Models\PurchaseOrderItem::where('product_id', $productId)
            ->whereHas('purchaseOrder', fn($q) => $q
                ->whereIn('status', ['ordered', 'partial'])
                ->whereDate('expected_date', $date))
            ->sum(DB::raw('quantity - IFNULL(quantity_received, 0)'));

        $productionReceipts = ProductionOrder::where('product_id', $productId)
            ->whereIn('status', ['planned', 'in_progress'])
            ->whereDate('planned_end', $date)
            ->sum(DB::raw('planned_quantity - produced_quantity'));

        return (float) $poReceipts + (float) $productionReceipts;
    }

    /**
     * Create Production Order from Sales Order.
     */
    public function createFromSalesOrder(PosTransaction $order): ProductionOrder
    {
        return DB::transaction(function () use ($order) {
            $items = $order->items()->with('product')->get();

            $productionOrders = [];

            foreach ($items as $item) {
                $product = $item->product;
                $bom = BillOfMaterial::where('product_id', $product->id)
                    ->where('is_active', true)
                    ->first();

                if (!$bom) continue;

                $poNumber = $this->generatePoNumber($order->company_id);

                $productionOrder = ProductionOrder::create([
                    'company_id' => $order->company_id,
                    'po_number' => $poNumber,
                    'product_id' => $product->id,
                    'bom_id' => $bom->id,
                    'work_center_id' => null,
                    'planned_quantity' => $item->quantity,
                    'status' => 'draft',
                    'notes' => 'Auto-generated dari Sales Order #' . $order->receipt_number,
                    'created_by' => auth()->user()?->employee_id,
                ]);

                $this->generateProductionMaterials($productionOrder);

                $productionOrders[] = $productionOrder;
            }

            return $productionOrders[0] ?? null;
        });
    }

    protected function generatePoNumber(int $companyId): string
    {
        $prefix = 'PO-' . date('Ymd');
        $last = ProductionOrder::where('company_id', $companyId)
            ->where('po_number', 'like', $prefix . '%')
            ->orderBy('po_number', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->po_number, -4);
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        return $prefix . '-' . $newNum;
    }

    /**
     * Generate material requirements for a production order from BOM.
     */
    public function generateProductionMaterials(ProductionOrder $order): array
    {
        $bom = $order->bom;
        if (!$bom) return [];

        $components = $this->explodeBom($bom, (float) $order->planned_quantity);
        $materials = [];

        foreach ($components as $comp) {
            $material = ProductionOrderMaterial::create([
                'production_order_id' => $order->id,
                'bom_item_id' => $comp['bom_item_id'],
                'product_id' => $comp['product_id'],
                'required_quantity' => $comp['required_quantity'],
                'status' => 'pending',
            ]);
            $materials[] = $material;
        }

        return $materials;
    }

    /**
     * Start a production order.
     */
    public function startProduction(ProductionOrder $order): void
    {
        if ($order->status !== 'planned') {
            throw new \InvalidArgumentException('Hanya production order dengan status "planned" yang dapat dimulai.');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status' => 'in_progress',
                'actual_start' => now(),
            ]);

            ProductionOrderOperation::where('production_order_id', $order->id)
                ->where('status', 'pending')
                ->first()?->update([
                    'status' => 'in_progress',
                    'actual_start' => now(),
                ]);
        });
    }

    /**
     * Complete a production order.
     */
    public function completeProduction(ProductionOrder $order, float $producedQty, float $rejectedQty): void
    {
        if (!in_array($order->status, ['in_progress'])) {
            throw new \InvalidArgumentException('Hanya production order yang sedang berjalan yang dapat diselesaikan.');
        }

        DB::transaction(function () use ($order, $producedQty, $rejectedQty) {
            $order->update([
                'produced_quantity' => $producedQty,
                'rejected_quantity' => $rejectedQty,
                'status' => 'completed',
                'actual_end' => now(),
            ]);

            ProductionOrderOperation::where('production_order_id', $order->id)
                ->update([
                    'status' => 'completed',
                    'actual_end' => now(),
                ]);

            $this->reduceRawMaterialStock($order);
            $this->increaseFinishedGoodStock($order, $producedQty);

            if ($rejectedQty > 0) {
                WasteLog::create([
                    'production_order_id' => $order->id,
                    'product_id' => $order->product_id,
                    'quantity' => $rejectedQty,
                    'unit' => 'pcs',
                    'waste_type' => 'reject',
                    'reason' => 'Produk reject saat produksi',
                    'cost_impact' => $this->estimateRejectCost($order, $rejectedQty),
                    'reported_by' => auth()->user()?->employee_id,
                ]);
            }
        });
    }

    protected function reduceRawMaterialStock(ProductionOrder $order): void
    {
        $materials = $order->materials()->get();

        foreach ($materials as $material) {
            $qty = (float) $material->issued_quantity;
            if ($qty <= 0) continue;

            $balance = StockBalance::where('product_id', $material->product_id)
                ->where('company_id', $order->company_id)
                ->first();

            if ($balance) {
                $balance->quantity = max(0, (float) $balance->quantity - $qty);
                $balance->save();
            }

            StockMovement::create([
                'company_id' => $order->company_id,
                'product_id' => $material->product_id,
                'movement_type' => 'out',
                'reference_type' => 'production_order',
                'reference_id' => $order->id,
                'quantity_in' => 0,
                'quantity_out' => $qty,
                'unit_cost' => $balance->average_cost ?? 0,
                'running_quantity' => $qty,
                'running_cost' => round($qty * ($balance->average_cost ?? 0), 2),
                'notes' => 'Material untuk PO #' . $order->po_number,
                'created_by' => auth()->user()?->employee_id,
                'movement_date' => now(),
            ]);

            $material->update(['status' => 'complete']);
        }
    }

    protected function increaseFinishedGoodStock(ProductionOrder $order, float $qty): void
    {
        if ($qty <= 0) return;

        $balance = StockBalance::firstOrNew([
            'company_id' => $order->company_id,
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
            'company_id' => $order->company_id,
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
            'created_by' => auth()->user()?->employee_id,
            'movement_date' => now(),
        ]);
    }

    protected function calculateProductionCost(ProductionOrder $order): float
    {
        $materialCost = 0;
        $materials = $order->materials()->get();
        foreach ($materials as $mat) {
            $balance = StockBalance::where('product_id', $mat->product_id)
                ->where('company_id', $order->company_id)
                ->first();
            $materialCost += (float) $mat->issued_quantity * (float) ($balance->average_cost ?? 0);
        }

        $laborCost = 0;
        if ($order->work_center_id) {
            $wc = $order->workCenter;
            if ($wc) {
                $ops = $order->operations()->get();
                foreach ($ops as $op) {
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

    protected function estimateRejectCost(ProductionOrder $order, float $qty): float
    {
        $totalCost = $this->calculateProductionCost($order);
        $totalQty = (float) $order->produced_quantity + $qty;
        return $totalQty > 0 ? round(($totalCost / $totalQty) * $qty, 2) : 0;
    }

    /**
     * Auto-generate production schedule (forward/backward scheduling).
     */
    public function generateSchedule(ProductionOrder $order, string $direction = 'forward'): array
    {
        $routingOps = RoutingOperation::where('product_id', $order->product_id)
            ->orderBy('sequence')
            ->get();

        if ($routingOps->isEmpty()) {
            return [];
        }

        $schedule = [];
        $currentTime = $direction === 'forward'
            ? Carbon::now()->startOfDay()->addHours(8)
            : Carbon::parse($order->planned_end ?? now()->addDays(7));

        $ops = $direction === 'forward' ? $routingOps : $routingOps->reverse();

        foreach ($ops as $routingOp) {
            $setupMinutes = (float) $routingOp->setup_time_minutes;
            $runMinutesPerUnit = (float) $routingOp->run_time_minutes_per_unit;
            $totalMinutes = $setupMinutes + ($runMinutesPerUnit * (float) $order->planned_quantity);

            if ($direction === 'forward') {
                $start = $currentTime->copy();
                $end = $start->copy()->addMinutes($totalMinutes);
                $currentTime = $end->copy();
            } else {
                $end = $currentTime->copy();
                $start = $end->copy()->subMinutes($totalMinutes);
                $currentTime = $start->copy();
            }

            $poOp = ProductionOrderOperation::updateOrCreate(
                [
                    'production_order_id' => $order->id,
                    'routing_operation_id' => $routingOp->id,
                ],
                [
                    'work_center_id' => $routingOp->work_center_id,
                    'planned_start' => $start,
                    'planned_end' => $end,
                    'status' => 'pending',
                ]
            );

            $schedule[] = [
                'operation_id' => $poOp->id,
                'operation_name' => $routingOp->operation_name,
                'work_center' => $routingOp->workCenter->name ?? 'Unknown',
                'planned_start' => $start->format('Y-m-d H:i'),
                'planned_end' => $end->format('Y-m-d H:i'),
                'duration_minutes' => round($totalMinutes, 1),
            ];
        }

        $first = $schedule[0] ?? null;
        $last = $schedule[count($schedule) - 1] ?? null;
        if ($first && $last) {
            $order->update([
                'planned_start' => $first['planned_start'],
                'planned_end' => $last['planned_end'],
                'status' => $order->status === 'draft' ? 'planned' : $order->status,
            ]);
        }

        return $schedule;
    }

    /**
     * Calculate OEE (Overall Equipment Effectiveness).
     */
    public function calculateOee(WorkCenter $wc, string $period = 'weekly'): array
    {
        $dates = match ($period) {
            'daily' => [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()],
            'weekly' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'monthly' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
        };

        [$from, $to] = $dates;

        $operations = ProductionOrderOperation::where('work_center_id', $wc->id)
            ->whereBetween('actual_start', [$from, $to])
            ->orWhereBetween('actual_end', [$from, $to])
            ->get();

        $plannedMinutes = 0;
        $actualMinutes = 0;
        $goodQuantity = 0;
        $totalQuantity = 0;
        $plannedDowntime = 0;

        $workDays = $from->diffInWeekdays($to) ?: 1;
        $availableMinutes = $workDays * (float) $wc->capacity_per_day * 60;

        foreach ($operations as $op) {
            if ($op->planned_start && $op->planned_end) {
                $plannedMinutes += Carbon::parse($op->planned_start)->diffInMinutes(Carbon::parse($op->planned_end));
            }
            if ($op->actual_start && $op->actual_end) {
                $actualMinutes += Carbon::parse($op->actual_start)->diffInMinutes(Carbon::parse($op->actual_end));
            }
        }

        $relatedOrders = ProductionOrder::where('work_center_id', $wc->id)
            ->whereBetween('actual_start', [$from, $to])
            ->get();
        foreach ($relatedOrders as $order) {
            $totalQuantity += (float) $order->planned_quantity;
            $goodQuantity += (float) $order->produced_quantity - (float) $order->rejected_quantity;
        }

        $operatingTime = $availableMinutes - $plannedDowntime;
        $availability = $operatingTime > 0 ? min(100, ($actualMinutes / $operatingTime) * 100) : 0;

        $idealCycleTime = $actualMinutes > 0 ? round($actualMinutes / max($totalQuantity, 1), 2) : 0;
        $performance = $actualMinutes > 0 ? min(100, ($idealCycleTime * $totalQuantity / $actualMinutes) * 100) : 0;
        $performance = min(100, $performance);

        $quality = $totalQuantity > 0 ? ($goodQuantity / $totalQuantity) * 100 : 100;

        $oee = ($availability * $performance * $quality) / 10000;

        return [
            'work_center' => $wc->name,
            'period' => $period,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'availability_percent' => round($availability, 2),
            'performance_percent' => round($performance, 2),
            'quality_percent' => round($quality, 2),
            'oee_percent' => round($oee, 2),
            'available_minutes' => round($availableMinutes, 1),
            'planned_minutes' => round($plannedMinutes, 1),
            'actual_minutes' => round($actualMinutes, 1),
            'total_quantity' => $totalQuantity,
            'good_quantity' => round($goodQuantity, 4),
        ];
    }

    /**
     * Send production to subcontract.
     */
    public function sendToSubcontract(ProductionOrder $order, int $supplierId): SubcontractOrder
    {
        return DB::transaction(function () use ($order, $supplierId) {
            $sub = SubcontractOrder::create([
                'company_id' => $order->company_id,
                'supplier_id' => $supplierId,
                'product_id' => $order->product_id,
                'quantity_sent' => $order->planned_quantity,
                'sent_date' => now(),
                'expected_return' => now()->addDays(7),
                'status' => 'sent',
                'notes' => 'Dari PO #' . $order->po_number,
            ]);

            $order->update(['status' => 'in_progress']);

            return $sub;
        });
    }

    /**
     * Receive from subcontract.
     */
    public function receiveFromSubcontract(SubcontractOrder $sub): void
    {
        DB::transaction(function () use ($sub) {
            $sub->update([
                'status' => 'received',
                'actual_return' => now(),
            ]);
        });
    }

    /**
     * Waste analysis.
     */
    public function analyzeWaste(string $period = 'monthly'): array
    {
        $dates = match ($period) {
            'daily' => [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()],
            'weekly' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'monthly' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'yearly' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };

        [$from, $to] = $dates;

        $logs = WasteLog::with('product')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $wasteByType = $logs->groupBy('waste_type')->map(fn($g) => [
            'count' => $g->count(),
            'quantity' => round($g->sum('quantity'), 4),
            'cost_impact' => round($g->sum('cost_impact'), 2),
        ])->toArray();

        $wasteByProduct = $logs->groupBy('product_id')->map(fn($g) => [
            'product_name' => $g->first()->product->name ?? 'Unknown',
            'count' => $g->count(),
            'quantity' => round($g->sum('quantity'), 4),
            'cost_impact' => round($g->sum('cost_impact'), 2),
        ])->sortByDesc('cost_impact')->take(10)->values()->toArray();

        $costImpactTotal = round($logs->sum('cost_impact'), 2);

        $trend = [];
        if ($period === 'monthly') {
            for ($i = 6; $i >= 0; $i--) {
                $m = Carbon::now()->subMonths($i);
                $mlogs = WasteLog::whereMonth('created_at', $m->month)
                    ->whereYear('created_at', $m->year)
                    ->get();
                $trend[] = [
                    'month' => $m->format('Y-m'),
                    'quantity' => round($mlogs->sum('quantity'), 4),
                    'cost_impact' => round($mlogs->sum('cost_impact'), 2),
                ];
            }
        }

        return [
            'period' => $period,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'waste_by_type' => $wasteByType,
            'waste_by_product' => $wasteByProduct,
            'cost_impact_total' => $costImpactTotal,
            'trend' => $trend,
        ];
    }

    /**
     * Capacity planning / utilization.
     */
    public function getCapacityUtilization(WorkCenter $wc, string $period = 'weekly'): array
    {
        $dates = match ($period) {
            'daily' => [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()],
            'weekly' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'monthly' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
        };

        [$from, $to] = $dates;
        $workDays = max(1, $from->diffInWeekdays($to));

        $capacityHours = $workDays * (float) $wc->capacity_per_day;

        $plannedMinutes = ProductionOrderOperation::where('work_center_id', $wc->id)
            ->whereBetween('planned_start', [$from, $to])
            ->orWhereBetween('planned_end', [$from, $to])
            ->get()
            ->sum(function ($op) {
                if ($op->planned_start && $op->planned_end) {
                    return Carbon::parse($op->planned_start)->diffInMinutes(Carbon::parse($op->planned_end));
                }
                return 0;
            });

        $actualMinutes = ProductionOrderOperation::where('work_center_id', $wc->id)
            ->whereBetween('actual_start', [$from, $to])
            ->orWhereBetween('actual_end', [$from, $to])
            ->get()
            ->sum(function ($op) {
                if ($op->actual_start && $op->actual_end) {
                    return Carbon::parse($op->actual_start)->diffInMinutes(Carbon::parse($op->actual_end));
                }
                return 0;
            });

        $plannedHours = $plannedMinutes / 60;
        $actualHours = $actualMinutes / 60;

        return [
            'work_center' => $wc->name,
            'period' => $period,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'work_days' => $workDays,
            'capacity_hours' => round($capacityHours, 1),
            'planned_hours' => round($plannedHours, 1),
            'actual_hours' => round($actualHours, 1),
            'utilization_percent' => $capacityHours > 0
                ? round(($actualHours / $capacityHours) * 100, 2)
                : 0,
        ];
    }

    /**
     * Buat Production Order dari Production Plan.
     */
    public function createProductionOrderFromPlan(ProductionPlan $plan): ProductionOrder
    {
        return DB::transaction(function () use ($plan) {
            if (!in_array($plan->status, ['confirmed', 'in_progress'])) {
                throw new \InvalidArgumentException(
                    'Hanya production plan dengan status "confirmed" atau "in_progress" yang dapat dibuatkan PO.'
                );
            }

            $bom = BillOfMaterial::where('product_id', $plan->product_id)
                ->where('is_active', true)
                ->first();

            $poNumber = $this->generatePoNumber($plan->company_id);

            $productionOrder = ProductionOrder::create([
                'company_id' => $plan->company_id,
                'po_number' => $poNumber,
                'product_id' => $plan->product_id,
                'bom_id' => $bom?->id,
                'production_plan_id' => $plan->id,
                'planned_quantity' => $plan->planned_quantity,
                'planned_start' => $plan->start_date,
                'planned_end' => $plan->end_date,
                'status' => 'draft',
                'notes' => $plan->notes ?? 'Auto-generated dari Production Plan #' . $plan->id,
                'created_by' => auth()->user()?->employee_id,
            ]);

            $plan->update(['status' => 'in_progress']);

            if ($bom) {
                $this->generateProductionMaterials($productionOrder);
            }

            return $productionOrder;
        });
    }

    /**
     * Hitung kapasitas Work Center untuk periode tertentu.
     */
    public function calculateCapacity(WorkCenter $wc, string $period = 'weekly'): array
    {
        $dates = match ($period) {
            'daily' => [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()],
            'weekly' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'monthly' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
        };

        [$from, $to] = $dates;
        $workDays = max(1, $from->diffInWeekdays($to));
        $totalCapacityHours = $workDays * (float) $wc->capacity_per_day;

        $machines = Machine::where('work_center_id', $wc->id)
            ->where('status', 'active')
            ->get();

        $machineCapacityHours = $machines->sum(function ($machine) use ($workDays) {
            return $workDays * 8 * ((float) $machine->capacity_per_hour / 100);
        });

        $plannedOps = ProductionOrderOperation::where('work_center_id', $wc->id)
            ->whereBetween('planned_start', [$from, $to])
            ->orWhereBetween('planned_end', [$from, $to])
            ->get();

        $plannedHours = $plannedOps->sum(function ($op) {
            if ($op->planned_start && $op->planned_end) {
                return Carbon::parse($op->planned_start)->diffInMinutes(Carbon::parse($op->planned_end)) / 60;
            }
            return 0;
        });

        $remainingCapacity = max(0, $totalCapacityHours - $plannedHours);

        return [
            'work_center' => $wc->name,
            'period' => $period,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'work_days' => $workDays,
            'total_capacity_hours' => round($totalCapacityHours, 1),
            'machine_count' => $machines->count(),
            'machine_capacity_hours' => round($machineCapacityHours, 1),
            'planned_hours' => round($plannedHours, 1),
            'remaining_capacity_hours' => round($remainingCapacity, 1),
            'utilization_percent' => $totalCapacityHours > 0
                ? round(($plannedHours / $totalCapacityHours) * 100, 2)
                : 0,
        ];
    }

    /**
     * Hitung efisiensi mesin berdasarkan jam operasi aktual vs kapasitas.
     */
    public function getMachineEfficiency(Machine $machine): float
    {
        $orders = ProductionOrder::where('machine_id', $machine->id)
            ->whereIn('status', ['completed', 'in_progress'])
            ->whereNotNull('actual_start')
            ->whereNotNull('actual_end')
            ->get();

        if ($orders->isEmpty()) {
            return 0;
        }

        $totalPlannedHours = 0;
        $totalActualHours = 0;

        foreach ($orders as $order) {
            if ($order->planned_start && $order->planned_end) {
                $totalPlannedHours += Carbon::parse($order->planned_start)
                    ->diffInMinutes(Carbon::parse($order->planned_end)) / 60;
            }
            if ($order->actual_start && $order->actual_end) {
                $totalActualHours += Carbon::parse($order->actual_start)
                    ->diffInMinutes(Carbon::parse($order->actual_end)) / 60;
            }
        }

        if ($totalPlannedHours <= 0) {
            return 0;
        }

        $efficiency = ($totalPlannedHours / max($totalActualHours, 1)) * 100;

        return round(min(100, $efficiency), 2);
    }
}
