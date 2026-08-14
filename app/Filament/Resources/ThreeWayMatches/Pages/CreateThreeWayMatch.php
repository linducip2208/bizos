<?php

namespace App\Filament\Resources\ThreeWayMatches\Pages;

use App\Filament\Resources\ThreeWayMatches\ThreeWayMatchResource;
use Filament\Resources\Pages\CreateRecord;

class CreateThreeWayMatch extends CreateRecord
{
    protected static string $resource = ThreeWayMatchResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['company_id'] = auth()->user()->company_id ?? null;

        if (!empty($data['purchase_order_id'])) {
            $po = \App\Models\PurchaseOrder::with('items')->find($data['purchase_order_id']);
            $gr = isset($data['goods_receipt_id']) ? \App\Models\GoodsReceipt::with('items')->find($data['goods_receipt_id']) : null;
            $invoice = isset($data['invoice_id']) ? \App\Models\Invoice::with('invoiceItems')->find($data['invoice_id']) : null;

            if ($po) {
                $service = app(\App\Services\ThreeWayMatchService::class);
                $results = $service->performMatch($po, $gr, $invoice);

                $data['match_status'] = $results['match_status'];
                $data['po_total'] = $results['po_total'];
                $data['gr_total'] = $results['gr_total'];
                $data['invoice_total'] = $results['invoice_total'];
                $data['quantity_match'] = $results['quantity_match'];
                $data['price_match'] = $results['price_match'];
                $data['total_match'] = $results['total_match'];
                $data['variance_amount'] = $results['variance_amount'];
                $data['variance_percent'] = $results['variance_percent'];
                $data['mismatch_details'] = $results['mismatch_details'];
                $data['matched_by'] = auth()->id();
                $data['matched_at'] = now();
            }
        }

        return $data;
    }
}
