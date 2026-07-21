<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Leave;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Leave::query()->with('employee', 'leaveType');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['employee_id', 'leave_type_id', 'status']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Leave::query()->with('employee', 'leaveType', 'approvals');
        $this->applyCompanyScope($query, $request);
        $leave = $query->find($id);

        if (! $leave) {
            return $this->error('Cuti tidak ditemukan.', 404);
        }

        return $this->success($leave);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
            'status' => 'nullable|string|max:20',
        ]);

        $leave = Leave::create($validated);

        return $this->success($leave, 'Cuti berhasil diajukan.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Leave::query();
        $this->applyCompanyScope($query, $request);
        $leave = $query->find($id);

        if (! $leave) {
            return $this->error('Cuti tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:1000',
            'status' => 'nullable|string|max:20',
        ]);

        $leave->update($validated);

        return $this->success($leave, 'Cuti berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Leave::query();
        $this->applyCompanyScope($query, $request);
        $leave = $query->find($id);

        if (! $leave) {
            return $this->error('Cuti tidak ditemukan.', 404);
        }

        $leave->delete();

        return $this->success(null, 'Cuti berhasil dihapus.');
    }
}
