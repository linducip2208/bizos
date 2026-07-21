<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Timesheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TimesheetApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Timesheet::query()->with('employee', 'entries');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['employee_id', 'status', 'start_date', 'end_date']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Timesheet::query()->with('employee', 'entries', 'approvedBy');
        $this->applyCompanyScope($query, $request);
        $timesheet = $query->find($id);

        if (! $timesheet) {
            return $this->error('Timesheet tidak ditemukan.', 404);
        }

        return $this->success($timesheet);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'nullable|string|max:20',
            'total_hours' => 'nullable|numeric',
        ]);

        $timesheet = Timesheet::create($validated);

        return $this->success($timesheet, 'Timesheet berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Timesheet::query();
        $this->applyCompanyScope($query, $request);
        $timesheet = $query->find($id);

        if (! $timesheet) {
            return $this->error('Timesheet tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'end_date' => 'nullable|date',
            'status' => 'nullable|string|max:20',
            'total_hours' => 'nullable|numeric',
        ]);

        $timesheet->update($validated);

        return $this->success($timesheet, 'Timesheet berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Timesheet::query();
        $this->applyCompanyScope($query, $request);
        $timesheet = $query->find($id);

        if (! $timesheet) {
            return $this->error('Timesheet tidak ditemukan.', 404);
        }

        $timesheet->delete();

        return $this->success(null, 'Timesheet berhasil dihapus.');
    }
}
