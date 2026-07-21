<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Client::query();
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['name', 'email', 'phone', 'status', 'type']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Client::query()->with('contacts', 'deals', 'invoices');
        $this->applyCompanyScope($query, $request);
        $client = $query->find($id);

        if (! $client) {
            return $this->error('Klien tidak ditemukan.', 404);
        }

        return $this->success($client);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:20',
        ]);

        $client = Client::create($validated);

        return $this->success($client, 'Klien berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Client::query();
        $this->applyCompanyScope($query, $request);
        $client = $query->find($id);

        if (! $client) {
            return $this->error('Klien tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:20',
        ]);

        $client->update($validated);

        return $this->success($client, 'Klien berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Client::query();
        $this->applyCompanyScope($query, $request);
        $client = $query->find($id);

        if (! $client) {
            return $this->error('Klien tidak ditemukan.', 404);
        }

        $client->delete();

        return $this->success(null, 'Klien berhasil dihapus.');
    }
}
