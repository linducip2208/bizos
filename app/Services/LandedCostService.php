<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\StockBalance;
use Illuminate\Support\Facades\DB;

class LandedCostService
{
    public function calculateLandedCost(GoodsReceipt $grn, array $costs): array
    {
        $items = $grn->items()->with('product')->get();
        $totalItemCost = $items->sum(function ($item) {
            return (float) $item->unit_price * (float) $item->quantity_accepted;
        });

        $totalAdditionalCosts = 0;
        $costDetails = [];

        $costTypes = [
            'freight' => 'Biaya Pengiriman',
            'insurance' => 'Asuransi',
            'customs_duty' => 'Bea Masuk',
            'pph22_import' => 'PPh 22 Impor',
            'handling' => 'Biaya Handling',
            'other' => 'Biaya Lainnya',
        ];

        foreach ($costTypes as $key => $label) {
            if (isset($costs[$key]) && $costs[$key] > 0) {
                $amount = (float) $costs[$key];
                $totalAdditionalCosts += $amount;
                $costDetails[] = [
                    'type' => $key,
                    'label' => $label,
                    'amount' => $amount,
                ];
            }
        }

        $allocations = [];
        foreach ($items as $item) {
            $itemCost = (float) $item->unit_price * (float) $item->quantity_accepted;
            $ratio = $totalItemCost > 0 ? $itemCost / $totalItemCost : 0;

            $allocatedCost = round($totalAdditionalCosts * $ratio, 2);
            $landedUnitCost = (float) $item->quantity_accepted > 0
                ? round(($itemCost + $allocatedCost) / (float) $item->quantity_accepted, 2)
                : (float) $item->unit_price;

            $allocations[] = [
                'item_id' => $item->id,
                'product_name' => $item->item_name,
                'quantity' => (float) $item->quantity_accepted,
                'original_unit_price' => (float) $item->unit_price,
                'original_total' => $itemCost,
                'allocation_ratio' => round($ratio * 100, 2),
                'allocated_cost' => $allocatedCost,
                'landed_unit_cost' => $landedUnitCost,
                'landed_total' => round($itemCost + $allocatedCost, 2),
            ];
        }

        return [
            'grn_id' => $grn->id,
            'grn_number' => $grn->grn_number,
            'total_item_cost' => $totalItemCost,
            'cost_details' => $costDetails,
            'total_additional_costs' => $totalAdditionalCosts,
            'total_landed_cost' => round($totalItemCost + $totalAdditionalCosts, 2),
            'allocations' => $allocations,
        ];
    }

    public function allocateToItems(GoodsReceipt $grn, array $landedCosts): void
    {
        $calc = $this->calculateLandedCost($grn, $landedCosts);

        DB::transaction(function () use ($grn, $calc) {
            foreach ($calc['allocations'] as $allocation) {
                GoodsReceiptItem::where('id', $allocation['item_id'])->update([
                    'unit_price' => $allocation['landed_unit_cost'],
                ]);
            }

            $this->updateStockBalances($grn, $calc);
        });
    }

    public function generateJournal(GoodsReceipt $grn, array $landedCosts): Journal
    {
        $calc = $this->calculateLandedCost($grn, $landedCosts);

        $journal = DB::transaction(function () use ($grn, $calc) {
            $journal = Journal::create([
                'company_id' => $grn->company_id,
                'journal_number' => 'GRN-' . $grn->grn_number,
                'journal_date' => $grn->receipt_date ?? now()->toDateString(),
                'journal_type' => 'goods_receipt',
                'description' => 'Penerimaan barang #' . $grn->grn_number . ' (dengan landed cost)',
                'total_debit' => $calc['total_landed_cost'],
                'total_credit' => $calc['total_landed_cost'],
                'reference_type' => 'goods_receipt',
                'reference_id' => $grn->id,
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            $inventoryCoa = Coa::where('code', 'LIKE', '1-1%')
                ->where('company_id', $grn->company_id)
                ->where('is_active', true)
                ->first();

            if (!$inventoryCoa) {
                $inventoryCoa = Coa::where('company_id', $grn->company_id)
                    ->where('is_active', true)
                    ->first();
            }

            $apCoa = Coa::where('code', 'LIKE', '2-1%')
                ->where('company_id', $grn->company_id)
                ->where('is_active', true)
                ->first();

            if ($inventoryCoa) {
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $inventoryCoa->id,
                    'debit' => $calc['total_landed_cost'],
                    'credit' => 0,
                    'description' => 'Penerimaan barang #' . $grn->grn_number,
                ]);
            }

            if ($apCoa) {
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $apCoa->id,
                    'debit' => 0,
                    'credit' => $calc['total_landed_cost'],
                    'description' => 'Hutang kepada supplier #' . $grn->grn_number,
                ]);
            }

            $pph22Coa = Coa::where('code', 'LIKE', '1-1%')
                ->where('name', 'like', '%pph%')
                ->where('company_id', $grn->company_id)
                ->where('is_active', true)
                ->first();

            $pph22Amount = $landedCosts['pph22_import'] ?? 0;
            if ($pph22Coa && $pph22Amount > 0) {
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $pph22Coa->id,
                    'debit' => $pph22Amount,
                    'credit' => 0,
                    'description' => 'PPh 22 Impor dibayar dimuka - #' . $grn->grn_number,
                ]);

                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $apCoa->id,
                    'debit' => 0,
                    'credit' => $pph22Amount,
                    'description' => 'Hutang PPh 22 - #' . $grn->grn_number,
                ]);
            }

            return $journal;
        });

        return $journal;
    }

    protected function updateStockBalances(GoodsReceipt $grn, array $calc): void
    {
        foreach ($calc['allocations'] as $allocation) {
            $item = GoodsReceiptItem::with('product')->find($allocation['item_id']);
            if (!$item || !$item->product_id) continue;

            $balance = StockBalance::firstOrNew(
                [
                    'company_id' => $grn->company_id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $grn->warehouse_id,
                ]
            );

            $oldQty = $balance->quantity ?? 0;
            $oldAvgCost = $balance->average_cost ?? 0;
            $addedQty = $allocation['quantity'];
            $addedCost = $allocation['landed_unit_cost'];

            $newQty = $oldQty + $addedQty;
            $newAvgCost = $newQty > 0
                ? round((($oldQty * $oldAvgCost) + ($addedQty * $addedCost)) / $newQty, 2)
                : $addedCost;

            $balance->fill([
                'quantity' => $newQty,
                'average_cost' => $newAvgCost,
                'last_cost' => $addedCost,
            ]);
            $balance->save();
        }
    }
}
