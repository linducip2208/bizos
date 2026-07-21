<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Lead::query()->with('leadSource');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['name', 'email', 'phone', 'status', 'lead_source_id']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Lead::query()->with('leadSource', 'activities', 'deals');
        $this->applyCompanyScope($query, $request);
        $lead = $query->find($id);

        if (! $lead) {
            return $this->error('Lead tidak ditemukan.', 404);
        }

        return $this->success($lead);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'lead_source_id' => 'nullable|exists:lead_sources,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:2000',
        ]);

        $lead = Lead::create($validated);

        return $this->success($lead, 'Lead berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Lead::query();
        $this->applyCompanyScope($query, $request);
        $lead = $query->find($id);

        if (! $lead) {
            return $this->error('Lead tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:30',
            'company_name' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:2000',
        ]);

        $lead->update($validated);

        return $this->success($lead, 'Lead berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Lead::query();
        $this->applyCompanyScope($query, $request);
        $lead = $query->find($id);

        if (! $lead) {
            return $this->error('Lead tidak ditemukan.', 404);
        }

        $lead->delete();

        return $this->success(null, 'Lead berhasil dihapus.');
    }
}
