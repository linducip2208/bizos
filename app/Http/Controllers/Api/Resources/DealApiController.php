<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Deal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DealApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Deal::query()->with('lead', 'pipelineStage');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['name', 'lead_id', 'pipeline_stage_id', 'status']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Deal::query()->with('lead', 'pipelineStage', 'client');
        $this->applyCompanyScope($query, $request);
        $deal = $query->find($id);

        if (! $deal) {
            return $this->error('Deal tidak ditemukan.', 404);
        }

        return $this->success($deal);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'lead_id' => 'nullable|exists:leads,id',
            'pipeline_stage_id' => 'required|exists:pipeline_stages,id',
            'name' => 'required|string|max:255',
            'amount' => 'nullable|numeric',
            'expected_close_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
        ]);

        $deal = Deal::create($validated);

        return $this->success($deal, 'Deal berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Deal::query();
        $this->applyCompanyScope($query, $request);
        $deal = $query->find($id);

        if (! $deal) {
            return $this->error('Deal tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'pipeline_stage_id' => 'nullable|exists:pipeline_stages,id',
            'name' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'expected_close_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
        ]);

        $deal->update($validated);

        return $this->success($deal, 'Deal berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Deal::query();
        $this->applyCompanyScope($query, $request);
        $deal = $query->find($id);

        if (! $deal) {
            return $this->error('Deal tidak ditemukan.', 404);
        }

        $deal->delete();

        return $this->success(null, 'Deal berhasil dihapus.');
    }
}
