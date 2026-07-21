<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\PosTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosTransactionApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = PosTransaction::query()->with('items');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['transaction_number', 'status', 'payment_method', 'transaction_date']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = PosTransaction::query()->with('items.product', 'payments', 'cashierShift');
        $this->applyCompanyScope($query, $request);
        $transaction = $query->find($id);

        if (! $transaction) {
            return $this->error('Transaksi POS tidak ditemukan.', 404);
        }

        return $this->success($transaction);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'transaction_number' => 'required|string|max:50',
            'transaction_date' => 'required|date',
            'subtotal' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'total' => 'required|numeric',
            'payment_method' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $transaction = PosTransaction::create($validated);

        return $this->success($transaction, 'Transaksi POS berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = PosTransaction::query();
        $this->applyCompanyScope($query, $request);
        $transaction = $query->find($id);

        if (! $transaction) {
            return $this->error('Transaksi POS tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $transaction->update($validated);

        return $this->success($transaction, 'Transaksi POS berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = PosTransaction::query();
        $this->applyCompanyScope($query, $request);
        $transaction = $query->find($id);

        if (! $transaction) {
            return $this->error('Transaksi POS tidak ditemukan.', 404);
        }

        $transaction->delete();

        return $this->success(null, 'Transaksi POS berhasil dihapus.');
    }
}
