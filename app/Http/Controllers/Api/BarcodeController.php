<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BarcodeService;
use Illuminate\Http\Request;

class BarcodeController extends Controller
{
    protected BarcodeService $barcodeService;

    public function __construct(BarcodeService $barcodeService)
    {
        $this->barcodeService = $barcodeService;
    }

    public function lookup(Request $request, string $barcode)
    {
        $product = $this->barcodeService->lookupProduct($barcode);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan untuk barcode: ' . $barcode,
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk ditemukan.',
            'data' => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'description' => $product->description,
                'unit' => $product->unit,
                'selling_price' => (float) $product->selling_price,
                'stock' => (float) $product->stock,
                'photo' => $product->photo ? asset('storage/' . $product->photo) : null,
                'category' => $product->category?->name,
                'variants' => $product->variants->map(fn($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'sku' => $v->sku,
                    'stock' => (float) $v->stock,
                ]),
            ],
        ]);
    }

    public function receive(Request $request)
    {
        $request->validate([
            'barcode' => ['required', 'string', 'max:255'],
            'goods_receipt_id' => ['required', 'integer', 'exists:goods_receipts,id'],
        ]);

        $result = $this->barcodeService->processReceivingScan(
            $request->barcode,
            $request->goods_receipt_id
        );

        if (!$result['found']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hasil pemindaian penerimaan.',
            'data' => $result,
        ]);
    }

    public function pick(Request $request)
    {
        $request->validate([
            'barcode' => ['required', 'string', 'max:255'],
            'delivery_order_id' => ['required', 'integer', 'exists:delivery_orders,id'],
        ]);

        $result = $this->barcodeService->processPickScan(
            $request->barcode,
            $request->delivery_order_id
        );

        if (!$result['found']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 404);
        }

        $httpCode = $result['valid'] ? 200 : 422;

        return response()->json([
            'success' => $result['valid'],
            'message' => $result['valid'] ? 'Item valid untuk pengiriman.' : $result['message'],
            'data' => $result,
        ], $httpCode);
    }

    public function opname(Request $request)
    {
        $request->validate([
            'barcode' => ['required', 'string', 'max:255'],
            'stock_opname_id' => ['required', 'integer', 'exists:stock_opnames,id'],
        ]);

        $result = $this->barcodeService->processOpnameScan(
            $request->barcode,
            $request->stock_opname_id
        );

        if (!$result['found']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Hasil pemindaian stock opname.',
            'data' => $result,
        ]);
    }

    public function label(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $product = \App\Models\Product::find($request->product_id);
        $result = $this->barcodeService->generateLabel($product);

        return response()->json([
            'success' => true,
            'message' => 'Data label barcode.',
            'data' => $result,
        ]);
    }

    public function labels(Request $request)
    {
        $request->validate([
            'product_ids' => ['required', 'array', 'min:1', 'max:100'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $results = [];
        $products = \App\Models\Product::whereIn('id', $request->product_ids)
            ->where('is_active', true)
            ->get();

        foreach ($products as $product) {
            $results[] = $this->barcodeService->generateLabel($product);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data label barcode untuk ' . count($results) . ' produk.',
            'data' => $results,
        ]);
    }
}
