<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::query();
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['first_name', 'last_name', 'email', 'phone', 'status', 'employee_type', 'department_id', 'branch_id']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Employee::query();
        $this->applyCompanyScope($query, $request);
        $employee = $query->find($id);

        if (! $employee) {
            return $this->error('Karyawan tidak ditemukan.', 404);
        }

        return $this->success($employee);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'employee_code' => 'nullable|string|max:50',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:30',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'join_date' => 'nullable|date',
            'employee_type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:20',
            'basic_salary' => 'nullable|numeric',
        ]);

        $employee = Employee::create($validated);

        return $this->success($employee, 'Karyawan berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Employee::query();
        $this->applyCompanyScope($query, $request);
        $employee = $query->find($id);

        if (! $employee) {
            return $this->error('Karyawan tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'employee_code' => 'nullable|string|max:50',
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:30',
            'gender' => 'nullable|in:male,female',
            'birth_date' => 'nullable|date',
            'join_date' => 'nullable|date',
            'employee_type' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:20',
            'basic_salary' => 'nullable|numeric',
        ]);

        $employee->update($validated);

        return $this->success($employee, 'Karyawan berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Employee::query();
        $this->applyCompanyScope($query, $request);
        $employee = $query->find($id);

        if (! $employee) {
            return $this->error('Karyawan tidak ditemukan.', 404);
        }

        $employee->delete();

        return $this->success(null, 'Karyawan berhasil dihapus.');
    }
}
