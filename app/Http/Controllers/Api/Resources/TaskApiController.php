<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Task::query()->with('project', 'assignees');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['title', 'project_id', 'status', 'priority']);

        if (! $request->has('project_id')) {
            $query->whereHas('project', function ($q) use ($request) {
                $this->applyCompanyScope($q, $request);
            });
        }

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Task::query()->with('project', 'assignees', 'comments', 'attachments', 'dependencies');
        $this->applyCompanyScope($query, $request);
        $task = $query->find($id);

        if (! $task) {
            return $this->error('Tugas tidak ditemukan.', 404);
        }

        return $this->success($task);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:500',
            'description' => 'nullable|string|max:5000',
            'status' => 'nullable|string|max:20',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric',
        ]);

        $task = Task::create($validated);

        return $this->success($task, 'Tugas berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Task::query();
        $this->applyCompanyScope($query, $request);
        $task = $query->find($id);

        if (! $task) {
            return $this->error('Tugas tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:500',
            'description' => 'nullable|string|max:5000',
            'status' => 'nullable|string|max:20',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric',
        ]);

        $task->update($validated);

        return $this->success($task, 'Tugas berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Task::query();
        $this->applyCompanyScope($query, $request);
        $task = $query->find($id);

        if (! $task) {
            return $this->error('Tugas tidak ditemukan.', 404);
        }

        $task->delete();

        return $this->success(null, 'Tugas berhasil dihapus.');
    }
}
