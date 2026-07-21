<?php

namespace App\Http\Controllers\Api\Resources;

use App\Http\Controllers\Api\ApiBaseController;
use App\Models\Payroll;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayrollApiController extends ApiBaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Payroll::query()->with('employee');
        $this->applyCompanyScope($query, $request);
        $this->applyFilters($query, $request, ['employee_id', 'payroll_period_id', 'status']);

        return $this->paginatedResponse($query, $request);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $query = Payroll::query()->with('employee', 'period', 'items');
        $this->applyCompanyScope($query, $request);
        $payroll = $query->find($id);

        if (! $payroll) {
            return $this->error('Payroll tidak ditemukan.', 404);
        }

        return $this->success($payroll);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'payroll_period_id' => 'nullable|exists:payroll_periods,id',
            'basic_salary' => 'required|numeric',
            'total_allowances' => 'nullable|numeric',
            'total_deductions' => 'nullable|numeric',
            'net_salary' => 'required|numeric',
            'status' => 'nullable|string|max:20',
        ]);

        $payroll = Payroll::create($validated);

        return $this->success($payroll, 'Payroll berhasil dibuat.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $query = Payroll::query();
        $this->applyCompanyScope($query, $request);
        $payroll = $query->find($id);

        if (! $payroll) {
            return $this->error('Payroll tidak ditemukan.', 404);
        }

        $validated = $request->validate([
            'total_allowances' => 'nullable|numeric',
            'total_deductions' => 'nullable|numeric',
            'net_salary' => 'nullable|numeric',
            'status' => 'nullable|string|max:20',
        ]);

        $payroll->update($validated);

        return $this->success($payroll, 'Payroll berhasil diubah.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $query = Payroll::query();
        $this->applyCompanyScope($query, $request);
        $payroll = $query->find($id);

        if (! $payroll) {
            return $this->error('Payroll tidak ditemukan.', 404);
        }

        $payroll->delete();

        return $this->success(null, 'Payroll berhasil dihapus.');
    }
}
