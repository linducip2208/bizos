<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\SalesReturn;
use Illuminate\Support\Facades\DB;

class SalesService
{
    public function createQuotation(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $data['subtotal'] = 0;
            $data['tax_amount'] = 0;
            $data['discount_amount'] = 0;
            $data['total'] = 0;

            $quotation = Quotation::create($data);

            foreach ($items as $i => $item) {
                $item['quotation_id'] = $quotation->id;
                $item['sort_order'] = $i + 1;
                $item['subtotal'] = $this->calcItemSubtotal($item);

                $qi = QuotationItem::create($item);

                $quotation->subtotal += $qi->subtotal;
                $quotation->tax_amount += $qi->subtotal * ($qi->tax_rate / 100);
                $quotation->discount_amount += $qi->subtotal * ($qi->discount_percent / 100);
            }

            $quotation->total = $quotation->subtotal + $quotation->tax_amount - $quotation->discount_amount;
            $quotation->save();

            return $quotation->fresh(['items']);
        });
    }

    public function convertQuotationToOrder(Quotation $quotation): SalesOrder
    {
        if ($quotation->status !== 'accepted') {
            throw new \RuntimeException('Quotation harus berstatus "accepted" untuk dikonversi.');
        }

        $quotation->load('items');

        return DB::transaction(function () use ($quotation) {
            $order = SalesOrder::create([
                'company_id' => $quotation->company_id,
                'client_id' => $quotation->client_id,
                'quotation_id' => $quotation->id,
                'subtotal' => $quotation->subtotal,
                'tax' => $quotation->tax_amount,
                'discount' => $quotation->discount_amount,
                'shipping_cost' => 0,
                'total' => $quotation->total,
                'order_date' => now()->format('Y-m-d'),
                'expected_delivery' => $quotation->valid_until,
                'status' => 'confirmed',
                'notes' => "Dikonversi dari Quotation {$quotation->quotation_number}",
                'created_by' => auth()->id(),
            ]);

            foreach ($quotation->items as $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'delivered_qty' => 0,
                    'unit_price' => $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                    'discount_percent' => $item->discount_percent,
                    'subtotal' => $item->subtotal,
                ]);
            }

            $quotation->update(['status' => 'converted']);

            return $order->fresh(['items']);
        });
    }

    public function createOrderFromDeal(Deal $deal): SalesOrder
    {
        if ($deal->status !== 'menang') {
            throw new \RuntimeException('Deal harus berstatus "menang" untuk membuat Sales Order.');
        }

        if (!$deal->client_id) {
            throw new \RuntimeException('Deal tidak memiliki klien terkait.');
        }

        return DB::transaction(function () use ($deal) {
            $amount = $deal->expected_value;

            $order = SalesOrder::create([
                'company_id' => $deal->company_id,
                'client_id' => $deal->client_id,
                'subtotal' => $amount,
                'tax' => round($amount * 0.11, 2),
                'total' => round($amount * 1.11, 2),
                'order_date' => now()->format('Y-m-d'),
                'status' => 'confirmed',
                'notes' => "Dibuat dari Deal: {$deal->title}",
                'created_by' => auth()->id(),
            ]);

            SalesOrderItem::create([
                'sales_order_id' => $order->id,
                'description' => $deal->title,
                'quantity' => 1,
                'unit_price' => $amount,
                'tax_rate' => 11,
                'subtotal' => $amount,
            ]);

            return $order->fresh(['items']);
        });
    }

    public function createInvoiceFromOrder(SalesOrder $order): SalesInvoice
    {
        if (!in_array($order->status, ['confirmed', 'in_progress', 'shipped', 'delivered'])) {
            throw new \RuntimeException('Sales Order harus dalam status yang valid untuk membuat invoice.');
        }

        $order->load('items');

        return DB::transaction(function () use ($order) {
            $invoice = SalesInvoice::create([
                'company_id' => $order->company_id,
                'sales_order_id' => $order->id,
                'client_id' => $order->client_id,
                'due_date' => now()->addDays(30)->format('Y-m-d'),
                'subtotal' => $order->subtotal,
                'tax' => $order->tax,
                'total' => $order->total,
                'paid_amount' => 0,
                'status' => 'draft',
            ]);

            $order->update(['status' => 'invoiced']);

            return $invoice->fresh();
        });
    }

    public function processReturn(SalesReturn $ret): void
    {
        if ($ret->status !== 'draft') {
            throw new \RuntimeException('Return hanya bisa diproses dari status draft.');
        }

        DB::transaction(function () use ($ret) {
            $ret->update(['status' => 'received']);
        });
    }

    public function voidReturn(SalesReturn $ret): void
    {
        if (!in_array($ret->status, ['draft', 'received'])) {
            throw new \RuntimeException('Return hanya bisa dibatalkan dari status draft/received.');
        }

        DB::transaction(function () use ($ret) {
            $ret->delete();
        });
    }

    private function calcItemSubtotal(array $item): float
    {
        $qty = (float) ($item['quantity'] ?? 1);
        $price = (float) ($item['unit_price'] ?? 0);
        $disc = (float) ($item['discount_percent'] ?? 0);

        $line = $qty * $price;
        return round($line * (1 - $disc / 100), 2);
    }
}
