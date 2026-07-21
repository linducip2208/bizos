<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::query()->with('employee');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['employee_id', 'date', 'status', 'work_type']);

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->query('employee_id'));
        }

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Attendance::query()->with('employee', 'shift');
        $this->applyCompanyScope($query, $request);
        $attendance = $query->find($id);

        if (! $attendance) {
            return $this->error('Absensi tidak ditemukan.', 404);
        }

        return $this->success($attendance);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'date' => 'required|date',
            'clock_in' => 'nullable|date_format:Y-m-d H:i:s',
            'clock_out' => 'nullable|date_format:Y-m-d H:i:s',
            'status' => 'nullable|string|max:20',
            'work_type' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:500',
        ]);

        $attendance = Attendance::create($validated);

        return $this->success($attendance, 'Absensi berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Attendance::query();
        $this->applyCompanyScope($query, $request);
        $attendance = $query->find($id);

        if (! $attendance) {
            return $this->error('Absensi tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'shift_id' => 'nullable|exists:shifts,id',
            'clock_in' => 'nullable|date_format:Y-m-d H:i:s',
            'clock_out' => 'nullable|date_format:Y-m-d H:i:s',
            'status' => 'nullable|string|max:20',
            'work_type' => 'nullable|string|max:30',
            'notes' => 'nullable|string|max:500',
        ]);

        $attendance->update($validated);

        return $this->success($attendance, 'Absensi berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Attendance::query();
        $this->applyCompanyScope($query, $request);
        $attendance = $query->find($id);

        if (! $attendance) {
            return $this->error('Absensi tidak ditemukan.', 404);
        }

        $attendance->delete();

        return $this->success(null, 'Absensi berhasil dihapus.');
    }
}
