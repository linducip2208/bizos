<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Invoice::query()->with('client');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['client_id', 'invoice_number', 'status', 'invoice_date', 'due_date']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Invoice::query()->with('client', 'items', 'payments');
        $this->applyCompanyScope($query, $request);
        $invoice = $query->find($id);

        if (! $invoice) {
            return $this->error('Invoice tidak ditemukan.', 404);
        }

        return $this->success($invoice);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => 'required|string|max:50',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'subtotal' => 'required|numeric',
            'tax_amount' => 'nullable|numeric',
            'total' => 'required|numeric',
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoice = Invoice::create($validated);

        return $this->success($invoice, 'Invoice berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Invoice::query();
        $this->applyCompanyScope($query, $request);
        $invoice = $query->find($id);

        if (! $invoice) {
            return $this->error('Invoice tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'due_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoice->update($validated);

        return $this->success($invoice, 'Invoice berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Invoice::query();
        $this->applyCompanyScope($query, $request);
        $invoice = $query->find($id);

        if (! $invoice) {
            return $this->error('Invoice tidak ditemukan.', 404);
        }

        $invoice->delete();

        return $this->success(null, 'Invoice berhasil dihapus.');
    }
}
