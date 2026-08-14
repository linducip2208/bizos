<?php

namespace App\Services;

use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\ThreeWayMatch;
use Illuminate\Support\Collection;

class ThreeWayMatchService
{
    public function performMatch(PurchaseOrder $po, ?GoodsReceipt $gr, ?Invoice $invoice): array
    {
        $mismatches = [];
        $quantityMatch = true;
        $priceMatch = true;

        $poTotal = (float) $po->total;
        $grTotal = $gr ? (float) $gr->items()->sum(\Illuminate\Support\Facades\DB::raw('quantity_accepted * unit_price')) : null;
        $invoiceTotal = $invoice ? (float) $invoice->total : null;

        $poItems = $po->items()->with('product')->get()->keyBy('product_id');

        if ($gr && $poItems->isNotEmpty()) {
            $grItems = $gr->items()->with('product')->get()->groupBy('product_id');

            foreach ($poItems as $productId => $poItem) {
                $grItem = $grItems->get($productId);
                $grQty = $grItem ? $grItem->sum('quantity_accepted') : 0;

                if ((float) $poItem->quantity !== (float) $grQty) {
                    $quantityMatch = false;
                    $mismatches[] = [
                        'item' => $poItem->item_name,
                        'field' => 'quantity',
                        'expected' => (float) $poItem->quantity,
                        'actual' => (float) $grQty,
                    ];
                }
            }
        }

        if ($invoice && $poItems->isNotEmpty()) {
            $invoiceItems = $invoice->invoiceItems()->get();
            $matchedInvoiceItemIds = [];

            foreach ($poItems as $productId => $poItem) {
                $invItem = $invoiceItems->first(function ($item) use ($poItem) {
                    return mb_strtolower($item->description) === mb_strtolower($poItem->item_name);
                });

                if (!$invItem) {
                    $priceMatch = false;
                    $mismatches[] = [
                        'item' => $poItem->item_name,
                        'field' => 'invoice_missing',
                        'expected' => (float) $poItem->unit_price,
                        'actual' => 0,
                    ];
                    continue;
                }

                $matchedInvoiceItemIds[] = $invItem->id;

                if ((float) $poItem->unit_price !== (float) $invItem->unit_price) {
                    $priceMatch = false;
                    $mismatches[] = [
                        'item' => $poItem->item_name,
                        'field' => 'price',
                        'expected' => (float) $poItem->unit_price,
                        'actual' => (float) $invItem->unit_price,
                    ];
                }
            }

            foreach ($invoiceItems as $invItem) {
                if (!in_array($invItem->id, $matchedInvoiceItemIds)) {
                    $priceMatch = false;
                    $mismatches[] = [
                        'item' => $invItem->description,
                        'field' => 'extra_item',
                        'expected' => 0,
                        'actual' => (float) $invItem->unit_price,
                    ];
                }
            }
        }

        $totalMatch = true;
        $varianceAmount = null;
        $variancePercent = null;

        if ($grTotal !== null && $invoiceTotal !== null) {
            $varianceAmount = abs($poTotal - $invoiceTotal);
            if ($poTotal > 0) {
                $variancePercent = ($varianceAmount / $poTotal) * 100;
            }

            $totalMatch = $varianceAmount <= 0.01;
        }

        if (!$quantityMatch || !$priceMatch || !$totalMatch) {
            if ($varianceAmount !== null && $variancePercent !== null && $variancePercent <= 5) {
                $matchStatus = 'partial_match';
            } else {
                $matchStatus = 'mismatch';
            }
        } else {
            $matchStatus = 'matched';
        }

        return [
            'match_status' => $matchStatus,
            'po_total' => $poTotal,
            'gr_total' => $grTotal,
            'invoice_total' => $invoiceTotal,
            'quantity_match' => $quantityMatch,
            'price_match' => $priceMatch,
            'total_match' => $totalMatch,
            'variance_amount' => $varianceAmount,
            'variance_percent' => $variancePercent,
            'mismatch_details' => $mismatches,
        ];
    }

    public function autoMatchOnInvoiceReceived(Invoice $invoice): ?ThreeWayMatch
    {
        if ($invoice->invoice_type !== 'purchase') {
            return null;
        }

        $po = null;
        $gr = null;

        if ($invoice->reference_entity === 'purchase_order' && $invoice->reference_id) {
            $po = PurchaseOrder::find($invoice->reference_id);
        }

        if ($po) {
            $gr = GoodsReceipt::where('purchase_order_id', $po->id)
                ->where('status', 'posted')
                ->latest()
                ->first();
        }

        if (!$po) {
            return null;
        }

        $results = $this->performMatch($po, $gr, $invoice);

        $match = ThreeWayMatch::create([
            'company_id' => $invoice->company_id,
            'purchase_order_id' => $po->id,
            'goods_receipt_id' => $gr?->id,
            'invoice_id' => $invoice->id,
            'match_status' => $results['match_status'],
            'po_total' => $results['po_total'],
            'gr_total' => $results['gr_total'],
            'invoice_total' => $results['invoice_total'],
            'quantity_match' => $results['quantity_match'],
            'price_match' => $results['price_match'],
            'total_match' => $results['total_match'],
            'variance_amount' => $results['variance_amount'],
            'variance_percent' => $results['variance_percent'],
            'mismatch_details' => $results['mismatch_details'],
            'resolution_status' => 'open',
            'matched_by' => auth()->id(),
            'matched_at' => now(),
            'created_by' => auth()->id(),
        ]);

        return $match;
    }

    public function getPendingMatches(): Collection
    {
        return ThreeWayMatch::where('resolution_status', 'open')
            ->whereIn('match_status', ['partial_match', 'mismatch'])
            ->with(['purchaseOrder.supplier', 'goodsReceipt', 'invoice'])
            ->latest()
            ->get();
    }
}
