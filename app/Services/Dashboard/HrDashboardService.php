<?php

namespace App\Services\Dashboard;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Leave;
use App\Models\Payroll;
use App\Models\User;

final class HrDashboardService extends AbstractDashboardService
{
    protected function build(DashboardFilter $filter, User $user): array
    {
        $employees = $this->tenant(Employee::query(), $filter)->when($filter->departmentId, fn ($q) => $q->where('department_id', $filter->departmentId));

        $employeeIds = (clone $employees)->pluck('id');

        return ['cards' => [
            ['label' => 'Karyawan Aktif', 'value' => (clone $employees)->where('status', 'active')->count(), 'format' => 'number'],
            ['label' => 'Hadir Hari Ini', 'value' => Attendance::query()->whereIn('employee_id', $employeeIds)->whereDate('date', today())->whereIn('status', ['present', 'late'])->count(), 'format' => 'number'],
            ['label' => 'Cuti Menunggu', 'value' => Leave::query()->whereIn('employee_id', $employeeIds)->where('status', 'pending')->count(), 'format' => 'number'],
            ['label' => 'Payroll Periode', 'value' => (float) Payroll::query()->whereIn('employee_id', $employeeIds)->whereBetween('created_at', [$filter->dateFrom, $filter->dateTo])->sum('net_salary'), 'format' => 'currency'],
        ]];
    }
}
