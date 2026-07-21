<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::query()->with('invoice');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['invoice_id', 'payment_method_id', 'payment_date', 'status']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Payment::query()->with('invoice', 'paymentMethod');
        $this->applyCompanyScope($query, $request);
        $payment = $query->find($id);

        if (! $payment) {
            return $this->error('Pembayaran tidak ditemukan.', 404);
        }

        return $this->success($payment);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'amount' => 'required|numeric',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment = Payment::create($validated);

        return $this->success($payment, 'Pembayaran berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Payment::query();
        $this->applyCompanyScope($query, $request);
        $payment = $query->find($id);

        if (! $payment) {
            return $this->error('Pembayaran tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'amount' => 'nullable|numeric',
            'payment_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment->update($validated);

        return $this->success($payment, 'Pembayaran berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Payment::query();
        $this->applyCompanyScope($query, $request);
        $payment = $query->find($id);

        if (! $payment) {
            return $this->error('Pembayaran tidak ditemukan.', 404);
        }

        $payment->delete();

        return $this->success(null, 'Pembayaran berhasil dihapus.');
    }
}
