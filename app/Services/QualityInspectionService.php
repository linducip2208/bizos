<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptInspection;
use App\Models\GoodsReceiptItem;
use App\Models\QualityCheck;
use App\Models\StockBalance;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class QualityInspectionService
{
    public function createInspectionChecklist(GoodsReceipt $grn): array
    {
        $items = $grn->items()->with('product')->get();
        $standardChecks = QualityCheck::where('company_id', $grn->company_id)
            ->where('is_active', true)
            ->get();

        $checklist = [];
        foreach ($items as $item) {
            foreach ($standardChecks as $check) {
                $existing = GoodsReceiptInspection::where('goods_receipt_id', $grn->id)
                    ->where('grn_item_id', $item->id)
                    ->where('quality_check_id', $check->id)
                    ->first();

                if (!$existing) {
                    GoodsReceiptInspection::create([
                        'goods_receipt_id' => $grn->id,
                        'grn_item_id' => $item->id,
                        'quality_check_id' => $check->id,
                        'result' => 'pending',
                    ]);
                }

                $checklist[] = [
                    'grn_item_id' => $item->id,
                    'item_name' => $item->item_name,
                    'check_name' => $check->name,
                    'check_category' => $check->category,
                    'result' => $existing?->result ?? 'pending',
                ];
            }

            if ($standardChecks->isEmpty()) {
                foreach (['Dimensi', 'Visual', 'Fungsi', 'Kemasan'] as $checkName) {
                    $existing = GoodsReceiptInspection::where('goods_receipt_id', $grn->id)
                        ->where('grn_item_id', $item->id)
                        ->where('quality_check_id', null)
                        ->first();

                    if (!$existing) {
                        $check = QualityCheck::firstOrCreate(
                            [
                                'company_id' => $grn->company_id,
                                'name' => $checkName,
                            ],
                            [
                                'category' => 'standar',
                                'is_active' => true,
                            ]
                        );

                        GoodsReceiptInspection::create([
                            'goods_receipt_id' => $grn->id,
                            'grn_item_id' => $item->id,
                            'quality_check_id' => $check->id,
                            'result' => 'pending',
                        ]);
                    }

                    $checklist[] = [
                        'grn_item_id' => $item->id,
                        'item_name' => $item->item_name,
                        'check_name' => $checkName,
                        'check_category' => 'standar',
                        'result' => $existing?->result ?? 'pending',
                    ];
                }
            }
        }

        return $checklist;
    }

    public function recordInspection(int $goodsReceiptId, int $grnItemId, array $results): void
    {
        $item = GoodsReceiptItem::findOrFail($grnItemId);

        foreach ($results as $result) {
            $checkId = $result['quality_check_id'] ?? null;
            $inspectionResult = $result['result'] ?? 'pending';
            $notes = $result['notes'] ?? null;

            GoodsReceiptInspection::updateOrCreate(
                [
                    'goods_receipt_id' => $goodsReceiptId,
                    'grn_item_id' => $grnItemId,
                    'quality_check_id' => $checkId,
                ],
                [
                    'result' => $inspectionResult,
                    'notes' => $notes,
                    'inspected_by' => auth()->user()?->employee_id,
                    'inspected_at' => now(),
                ]
            );
        }
    }

    public function acceptItem(GoodsReceiptItem $item, float $acceptedQty, float $rejectedQty): void
    {
        DB::transaction(function () use ($item, $acceptedQty, $rejectedQty) {
            $totalReceived = (float) $item->quantity_received;

            if ($acceptedQty + $rejectedQty > $totalReceived) {
                throw new \InvalidArgumentException('Jumlah accepted + rejected melebihi quantity diterima');
            }

            $item->update([
                'quantity_accepted' => $acceptedQty,
                'quantity_rejected' => $rejectedQty,
            ]);

            if ($acceptedQty > 0 && $item->product_id) {
                $balance = StockBalance::firstOrNew([
                    'company_id' => $item->goodsReceipt->company_id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $item->goodsReceipt->warehouse_id,
                ]);

                $oldQty = $balance->quantity ?? 0;
                $oldAvgCost = $balance->average_cost ?? 0;
                $addedCost = (float) $item->unit_price;

                $newQty = $oldQty + $acceptedQty;
                $newAvgCost = $newQty > 0
                    ? round((($oldQty * $oldAvgCost) + ($acceptedQty * $addedCost)) / $newQty, 2)
                    : $addedCost;

                $balance->fill([
                    'quantity' => $newQty,
                    'average_cost' => $newAvgCost,
                    'last_cost' => $addedCost,
                ]);
                $balance->save();

                StockMovement::create([
                    'company_id' => $item->goodsReceipt->company_id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $item->goodsReceipt->warehouse_id,
                    'movement_type' => 'in',
                    'reference_type' => 'goods_receipt',
                    'reference_id' => $item->goodsReceipt->id,
                    'quantity_in' => $acceptedQty,
                    'quantity_out' => 0,
                    'unit_cost' => $addedCost,
                    'running_quantity' => $acceptedQty,
                    'running_cost' => round($acceptedQty * $addedCost, 2),
                    'notes' => 'QC Accepted - GRN #' . $item->goodsReceipt->grn_number,
                    'created_by' => auth()->user()?->employee_id ?? $item->goodsReceipt->received_by,
                    'movement_date' => now(),
                ]);
            }

            if ($rejectedQty > 0 && $item->notes !== null) {
                $item->update([
                    'notes' => ($item->notes ? $item->notes . ' | ' : '') . "Rejected: {$rejectedQty} units",
                ]);
            }
        });
    }

    public function getInspectionStats(GoodsReceipt $grn): array
    {
        $inspections = GoodsReceiptInspection::where('goods_receipt_id', $grn->id)
            ->with(['grnItem', 'qualityCheck'])
            ->get();

        $totalInspections = $inspections->count();
        $passCount = $inspections->where('result', 'pass')->count();
        $failCount = $inspections->where('result', 'fail')->count();
        $pendingCount = $inspections->where('result', 'pending')->count();

        $items = $grn->items;
        $totalItems = $items->count();
        $inspectedItems = $inspections->where('result', '!=', 'pending')
            ->pluck('grn_item_id')->unique()->count();

        return [
            'grn_id' => $grn->id,
            'grn_number' => $grn->grn_number,
            'total_inspections' => $totalInspections,
            'passed' => $passCount,
            'failed' => $failCount,
            'pending' => $pendingCount,
            'pass_rate' => $totalInspections > 0 ? round(($passCount / $totalInspections) * 100, 1) : 0,
            'total_items' => $totalItems,
            'items_inspected' => $inspectedItems,
            'items_pending' => $totalItems - $inspectedItems,
            'by_item' => $this->getStatsPerItem($grn),
            'by_check' => $this->getStatsPerCheck($inspections),
        ];
    }

    protected function getStatsPerItem(GoodsReceipt $grn): array
    {
        $items = $grn->items;
        $result = [];

        foreach ($items as $item) {
            $inspections = GoodsReceiptInspection::where('grn_item_id', $item->id)->get();
            $hasFail = $inspections->contains('result', 'fail');
            $allPass = $inspections->isNotEmpty() && $inspections->every(fn($i) => $i->result === 'pass');
            $allPending = $inspections->isNotEmpty() && $inspections->every(fn($i) => $i->result === 'pending');

            $status = 'pending';
            if ($allPass) $status = 'pass';
            if ($hasFail) $status = 'fail';

            $result[] = [
                'item_id' => $item->id,
                'item_name' => $item->item_name,
                'quantity_received' => (float) $item->quantity_received,
                'quantity_accepted' => (float) $item->quantity_accepted,
                'quantity_rejected' => (float) $item->quantity_rejected,
                'checks_total' => $inspections->count(),
                'checks_passed' => $inspections->where('result', 'pass')->count(),
                'checks_failed' => $inspections->where('result', 'fail')->count(),
                'overall_result' => $status,
            ];
        }

        return $result;
    }

    protected function getStatsPerCheck($inspections): array
    {
        $grouped = $inspections->groupBy('quality_check_id');
        $result = [];

        foreach ($grouped as $checkId => $items) {
            $check = $items->first()->qualityCheck;
            $result[] = [
                'check_id' => $checkId,
                'check_name' => $check?->name ?? 'Unknown',
                'total' => $items->count(),
                'passed' => $items->where('result', 'pass')->count(),
                'failed' => $items->where('result', 'fail')->count(),
            ];
        }

        return $result;
    }
}
