<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::query()->with('phase');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['name', 'project_phase_id', 'status']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Project::query()->with('phase', 'members', 'tasks', 'milestones');
        $this->applyCompanyScope($query, $request);
        $project = $query->find($id);

        if (! $project) {
            return $this->error('Proyek tidak ditemukan.', 404);
        }

        return $this->success($project);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'project_phase_id' => 'nullable|exists:project_phases,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'priority' => 'nullable|string|max:20',
        ]);

        $project = Project::create($validated);

        return $this->success($project, 'Proyek berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Project::query();
        $this->applyCompanyScope($query, $request);
        $project = $query->find($id);

        if (! $project) {
            return $this->error('Proyek tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'priority' => 'nullable|string|max:20',
        ]);

        $project->update($validated);

        return $this->success($project, 'Proyek berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Project::query();
        $this->applyCompanyScope($query, $request);
        $project = $query->find($id);

        if (! $project) {
            return $this->error('Proyek tidak ditemukan.', 404);
        }

        $project->delete();

        return $this->success(null, 'Proyek berhasil dihapus.');
    }
}
