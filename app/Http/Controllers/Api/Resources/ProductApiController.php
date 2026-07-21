<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->with('category');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['name', 'sku', 'product_category_id', 'is_active']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Product::query()->with('category', 'variants');
        $this->applyCompanyScope($query, $request);
        $product = $query->find($id);

        if (! $product) {
            return $this->error('Produk tidak ditemukan.', 404);
        }

        return $this->success($product);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'price' => 'nullable|numeric',
            'cost' => 'nullable|numeric',
            'unit' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $product = Product::create($validated);

        return $this->success($product, 'Produk berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Product::query();
        $this->applyCompanyScope($query, $request);
        $product = $query->find($id);

        if (! $product) {
            return $this->error('Produk tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'price' => 'nullable|numeric',
            'cost' => 'nullable|numeric',
            'unit' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $product->update($validated);

        return $this->success($product, 'Produk berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Product::query();
        $this->applyCompanyScope($query, $request);
        $product = $query->find($id);

        if (! $product) {
            return $this->error('Produk tidak ditemukan.', 404);
        }

        $product->delete();

        return $this->success(null, 'Produk berhasil dihapus.');
    }
}
