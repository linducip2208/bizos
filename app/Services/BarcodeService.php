<?php

namespace App\Services;

use App\Models\DeliveryItem;
use App\Models\DeliveryOrder;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseOrderItem;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use Illuminate\Support\Facades\DB;

class BarcodeService
{
    public function lookupProduct(string $barcode): ?Product
    {
        $product = Product::where('code', $barcode)
            ->where('is_active', true)
            ->first();

        if ($product) {
            return $product->load(['category', 'variants']);
        }

        $variant = ProductVariant::where('sku', $barcode)->first();
        if ($variant) {
            return Product::where('id', $variant->product_id)
                ->where('is_active', true)
                ->first()
                ?->load(['category', 'variants']);
        }

        return DB::table('product_barcodes')
            ->where('barcode', $barcode)
            ->where('is_active', true)
            ->first()
            ? Product::where('id', function ($query) use ($barcode) {
                $query->select('product_id')
                    ->from('product_barcodes')
                    ->where('barcode', $barcode)
                    ->limit(1);
            })
            ->where('is_active', true)
            ->first()
            ?->load(['category', 'variants'])
            : null;
    }

    public function processReceivingScan(string $barcode, int $goodsReceiptId): array
    {
        $product = $this->lookupProduct($barcode);
        if (!$product) {
            return ['found' => false, 'message' => 'Produk tidak ditemukan untuk barcode ini.'];
        }

        $gr = GoodsReceipt::with(['items', 'purchaseOrder.items'])->find($goodsReceiptId);
        if (!$gr) {
            return ['found' => false, 'message' => 'Goods Receipt tidak ditemukan.'];
        }

        $poItem = $gr->purchaseOrder?->items?->first(fn($item) => $item->product_id === $product->id);
        $expectedQty = $poItem ? (float) $poItem->quantity : 0;
        $receivedQty = $poItem ? (float) $poItem->received_qty : 0;

        $grItem = $gr->items->first(fn($item) => $item->product_id === $product->id);
        $alreadyReceived = $grItem ? (float) $grItem->quantity_received : 0;

        return [
            'found' => true,
            'product' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'unit' => $product->unit,
                'photo' => $product->photo ? asset('storage/' . $product->photo) : null,
            ],
            'po_item' => $poItem ? [
                'id' => $poItem->id,
                'expected_qty' => $expectedQty,
                'already_received_po' => $receivedQty,
                'remaining_po' => max(0, $expectedQty - $receivedQty),
            ] : null,
            'goods_receipt_item' => $grItem ? [
                'id' => $grItem->id,
                'already_received' => $alreadyReceived,
            ] : null,
            'remaining' => $poItem ? max(0, $expectedQty - $receivedQty) : null,
        ];
    }

    public function processPickScan(string $barcode, int $deliveryOrderId): array
    {
        $product = $this->lookupProduct($barcode);
        if (!$product) {
            return ['found' => false, 'message' => 'Produk tidak ditemukan untuk barcode ini.'];
        }

        $delivery = DeliveryOrder::with(['items'])->find($deliveryOrderId);
        if (!$delivery) {
            return ['found' => false, 'message' => 'Delivery Order tidak ditemukan.'];
        }

        $deliveryItem = $delivery->items->first(fn($item) => $item->product_id === $product->id);

        if (!$deliveryItem) {
            return [
                'found' => true,
                'valid' => false,
                'product' => [
                    'id' => $product->id,
                    'code' => $product->code,
                    'name' => $product->name,
                    'unit' => $product->unit,
                ],
                'message' => 'Produk ini TIDAK ADA di delivery order ini. Cek kembali.',
            ];
        }

        return [
            'found' => true,
            'valid' => true,
            'product' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'unit' => $product->unit,
                'photo' => $product->photo ? asset('storage/' . $product->photo) : null,
            ],
            'delivery_item' => [
                'id' => $deliveryItem->id,
                'quantity' => (float) $deliveryItem->quantity,
                'unit' => $deliveryItem->unit,
            ],
            'delivery' => [
                'id' => $delivery->id,
                'do_number' => $delivery->do_number,
                'customer_name' => $delivery->customer_name,
                'delivery_address' => $delivery->delivery_address,
                'status' => $delivery->status,
            ],
        ];
    }

    public function processOpnameScan(string $barcode, int $stockOpnameId): array
    {
        $product = $this->lookupProduct($barcode);
        if (!$product) {
            return ['found' => false, 'message' => 'Produk tidak ditemukan untuk barcode ini.'];
        }

        $stockOpname = StockOpname::with(['items', 'warehouse'])->find($stockOpnameId);
        if (!$stockOpname) {
            return ['found' => false, 'message' => 'Stock Opname tidak ditemukan.'];
        }

        $systemBalance = DB::table('stock_balances')
            ->where('product_id', $product->id)
            ->where('warehouse_id', $stockOpname->warehouse_id)
            ->value('quantity') ?? 0;

        $existingItem = $stockOpname->items()->where('product_id', $product->id)->first();

        return [
            'found' => true,
            'product' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'unit' => $product->unit,
                'photo' => $product->photo ? asset('storage/' . $product->photo) : null,
            ],
            'opname' => [
                'id' => $stockOpname->id,
                'opname_number' => $stockOpname->opname_number,
                'warehouse' => $stockOpname->warehouse?->name,
                'opname_date' => $stockOpname->opname_date?->format('Y-m-d'),
            ],
            'system_quantity' => (float) $systemBalance,
            'existing_entry' => $existingItem ? [
                'id' => $existingItem->id,
                'system_quantity' => (float) $existingItem->system_quantity,
                'physical_quantity' => (float) $existingItem->physical_quantity,
                'difference' => (float) $existingItem->difference,
                'notes' => $existingItem->notes,
            ] : null,
            'status' => $existingItem ? 'already_scanned' : 'new',
        ];
    }

    public function generateLabel(Product $product): array
    {
        $variants = $product->variants;
        $labels = [];

        $labels[] = [
            'barcode_data' => $product->code,
            'product_name' => $product->name,
            'price' => (float) $product->selling_price,
            'label_format' => 'product_standard',
            'variant' => null,
        ];

        foreach ($variants as $variant) {
            $labels[] = [
                'barcode_data' => $variant->sku,
                'product_name' => $product->name . ' - ' . $variant->name,
                'price' => (float) ($product->selling_price + $variant->price_adjustment),
                'label_format' => 'variant_standard',
                'variant' => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'sku' => $variant->sku,
                ],
            ];
        }

        return [
            'product' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
            ],
            'labels' => $labels,
        ];
    }
}
