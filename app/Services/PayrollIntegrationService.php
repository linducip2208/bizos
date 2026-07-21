<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSalaryComponent;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\SalaryComponent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollIntegrationService
{
    protected BpjsCalculatorService $bpjsCalculator;

    public function __construct()
    {
        $this->bpjsCalculator = app(BpjsCalculatorService::class);
    }

    public function calculateFromAttendance(Employee $employee, string $periodStart, string $periodEnd): array
    {
        $periodStart = Carbon::parse($periodStart)->startOfDay();
        $periodEnd = Carbon::parse($periodEnd)->endOfDay();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->get();

        $presentDays = $attendances->whereIn('status', ['present', 'late'])->count();
        $lateDays = $attendances->where('status', 'late')->count();
        $absentDays = $attendances->where('status', 'absent')->count();
        $leaveDays = $attendances->where('status', 'leave')->count();
        $halfDays = $attendances->where('status', 'half_day')->count();

        $totalLateMinutes = $attendances->sum('late_minutes');
        $totalOvertimeMinutes = $attendances->sum('overtime_minutes');

        $overtimeHours = round($totalOvertimeMinutes / 60, 2);
        $overtimeRate = (float) ($employee->overtime_rate ?? 0);
        $hourlyRate = (float) ($employee->hourly_rate ?? 0);
        $overtimePay = $overtimeRate > 0
            ? round($overtimeHours * $overtimeRate, 2)
            : round($overtimeHours * $hourlyRate * 1.5, 2);

        $lateDeduction = $this->calculateLateDeduction($employee, $periodStart, $periodEnd);

        return [
            'attendance_days' => $attendances->count(),
            'present_days' => $presentDays + ($halfDays * 0.5),
            'late_days' => $lateDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'half_days' => $halfDays,
            'total_late_minutes' => $totalLateMinutes,
            'overtime_minutes' => $totalOvertimeMinutes,
            'overtime_hours' => $overtimeHours,
            'overtime_pay' => $overtimePay,
            'late_deduction' => $lateDeduction,
            'late_deduction_amount' => $lateDeduction,
        ];
    }

    public function generatePayrollData(PayrollPeriod $period): array
    {
        $periodStart = Carbon::parse($period->start_date)->startOfDay();
        $periodEnd = Carbon::parse($period->end_date)->endOfDay();
        $companyId = $period->company_id;

        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        $results = [];
        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;

        foreach ($employees as $employee) {
            $attendanceData = $this->calculateFromAttendance($employee, $periodStart, $periodEnd);
            $leaveData = $this->calculateLeaveDeduction($employee, $periodStart, $periodEnd);
            $salaryComponents = $this->getEmployeeSalaryComponents($employee);

            $basicSalary = (float) ($employee->basic_salary ?? 0);
            $workDaysInMonth = Carbon::parse($periodStart)->daysInMonth;

            $perDayRate = $workDaysInMonth > 0 ? $basicSalary / $workDaysInMonth : 0;

            $absentDeduction = round($perDayRate * $attendanceData['absent_days'], 2);
            $unpaidLeaveDeduction = $leaveData['deduction_amount'];
            $lateDeduction = $attendanceData['late_deduction_amount'];

            $incomeComponents = $salaryComponents->where('salaryComponent.type', 'income');
            $deductionComponents = $salaryComponents->where('salaryComponent.type', 'deduction');

            $totalIncome = $incomeComponents->sum('amount') + $attendanceData['overtime_pay'];
            $totalDeduction = $deductionComponents->sum('amount') + $absentDeduction + $unpaidLeaveDeduction + $lateDeduction;

            $grossSalary = $basicSalary + $totalIncome;
            $netBeforeBpjs = $grossSalary - $totalDeduction;

            $riskGrade = $employee->bpjs_tk_risk_grade ?? 'medium';
            $bpjsCalc = $this->bpjsCalculator->calculateAllContributions($grossSalary, $riskGrade);

            $pph21Amount = 0;
            $bpjsTkJht = $bpjsCalc['bpjs_tk']['jht']['employee_amount'];
            $bpjsTkJp = $bpjsCalc['bpjs_tk']['jp']['employee_amount'];
            $bpjsTkJkk = $bpjsCalc['bpjs_tk']['jkk']['employer_amount'];
            $bpjsTkJkm = $bpjsCalc['bpjs_tk']['jkm']['employer_amount'];
            $bpjsKes = $bpjsCalc['bpjs_kesehatan']['employee_amount'];

            $totalEmployeeBpjs = $bpjsTkJht + $bpjsTkJp + $bpjsKes;
            $netSalary = $netBeforeBpjs - $pph21Amount - $totalEmployeeBpjs;

            $payrollData = [
                'employee_id' => $employee->id,
                'gross_salary' => round($grossSalary, 2),
                'total_income_components' => round($totalIncome, 2),
                'total_deduction_components' => round($totalDeduction, 2),
                'pph21_amount' => round($pph21Amount, 2),
                'bpjs_tk_jht' => round($bpjsTkJht, 2),
                'bpjs_tk_jp' => round($bpjsTkJp, 2),
                'bpjs_tk_jkk' => round($bpjsTkJkk, 2),
                'bpjs_tk_jkm' => round($bpjsTkJkm, 2),
                'bpjs_kes' => round($bpjsKes, 2),
                'net_salary' => round($netSalary, 2),
                'attendance_days' => $attendanceData['attendance_days'],
                'leave_days' => $leaveData['unpaid_leave_days'],
                'overtime_hours' => $attendanceData['overtime_hours'],
                'overtime_pay' => $attendanceData['overtime_pay'],
                'status' => 'draft',
                'items' => [],
            ];

            $payrollData['items'][] = [
                'salary_component_id' => null,
                'name' => 'Gaji Pokok',
                'type' => 'income',
                'amount' => $basicSalary,
            ];

            foreach ($incomeComponents as $comp) {
                $payrollData['items'][] = [
                    'salary_component_id' => $comp->salary_component_id,
                    'name' => $comp->salaryComponent->name ?? 'Komponen Penghasilan',
                    'type' => 'income',
                    'amount' => (float) $comp->amount,
                ];
            }

            if ($attendanceData['overtime_pay'] > 0) {
                $payrollData['items'][] = [
                    'salary_component_id' => null,
                    'name' => 'Upah Lembur',
                    'type' => 'income',
                    'amount' => $attendanceData['overtime_pay'],
                ];
            }

            foreach ($deductionComponents as $comp) {
                $payrollData['items'][] = [
                    'salary_component_id' => $comp->salary_component_id,
                    'name' => $comp->salaryComponent->name ?? 'Komponen Potongan',
                    'type' => 'deduction',
                    'amount' => (float) $comp->amount,
                ];
            }

            if ($absentDeduction > 0) {
                $payrollData['items'][] = [
                    'salary_component_id' => null,
                    'name' => 'Potongan Absen (' . $attendanceData['absent_days'] . ' hari)',
                    'type' => 'deduction',
                    'amount' => $absentDeduction,
                ];
            }

            if ($unpaidLeaveDeduction > 0) {
                $payrollData['items'][] = [
                    'salary_component_id' => null,
                    'name' => 'Potongan Cuti Tidak Dibayar (' . $leaveData['unpaid_leave_days'] . ' hari)',
                    'type' => 'deduction',
                    'amount' => $unpaidLeaveDeduction,
                ];
            }

            if ($lateDeduction > 0) {
                $payrollData['items'][] = [
                    'salary_component_id' => null,
                    'name' => 'Potongan Keterlambatan (' . $attendanceData['total_late_minutes'] . ' menit)',
                    'type' => 'deduction',
                    'amount' => $lateDeduction,
                ];
            }

            if ($bpjsTkJht > 0) {
                $payrollData['items'][] = [
                    'salary_component_id' => null,
                    'name' => 'BPJS TK - JHT (Karyawan)',
                    'type' => 'deduction',
                    'amount' => $bpjsTkJht,
                ];
            }

            if ($bpjsTkJp > 0) {
                $payrollData['items'][] = [
                    'salary_component_id' => null,
                    'name' => 'BPJS TK - JP (Karyawan)',
                    'type' => 'deduction',
                    'amount' => $bpjsTkJp,
                ];
            }

            if ($bpjsKes > 0) {
                $payrollData['items'][] = [
                    'salary_component_id' => null,
                    'name' => 'BPJS Kesehatan (Karyawan)',
                    'type' => 'deduction',
                    'amount' => $bpjsKes,
                ];
            }

            $results[] = $payrollData;

            $totalGross += $grossSalary;
            $totalDeductions += $totalDeduction + $totalEmployeeBpjs;
            $totalNet += $netSalary;
        }

        return [
            'payrolls' => $results,
            'total_gross' => round($totalGross, 2),
            'total_deductions' => round($totalDeductions, 2),
            'total_net' => round($totalNet, 2),
            'total_employees' => count($results),
        ];
    }

    public function generateAndSaveFromAttendance(PayrollPeriod $period): array
    {
        $data = $this->generatePayrollData($period);

        DB::transaction(function () use ($period, $data) {
            Payroll::where('period_id', $period->id)->delete();

            foreach ($data['payrolls'] as $payrollData) {
                $items = $payrollData['items'];
                unset($payrollData['items']);

                $payrollData['period_id'] = $period->id;
                $payroll = Payroll::create($payrollData);

                foreach ($items as $item) {
                    $item['payroll_id'] = $payroll->id;
                    PayrollItem::create($item);
                }

                $periodStart = Carbon::parse($period->start_date);
                $periodEnd = Carbon::parse($period->end_date);
                Attendance::where('employee_id', $payrollData['employee_id'])
                    ->whereBetween('date', [$periodStart, $periodEnd])
                    ->whereNull('payroll_period_id')
                    ->update(['payroll_period_id' => $period->id]);
            }

            $period->update([
                'total_gross' => $data['total_gross'],
                'total_deductions' => $data['total_deductions'],
                'total_net' => $data['total_net'],
                'total_employees' => $data['total_employees'],
                'status' => 'processing',
            ]);
        });

        return $data;
    }

    public function calculateLateDeduction(Employee $employee, string $periodStart, string $periodEnd): float
    {
        $periodStart = Carbon::parse($periodStart);
        $periodEnd = Carbon::parse($periodEnd);

        $totalLateMinutes = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->sum('late_minutes');

        if ($totalLateMinutes <= 0) {
            return 0;
        }

        $basicSalary = (float) ($employee->basic_salary ?? 0);
        $workdays = $periodStart->daysInMonth;
        $hourlyRate = $workdays > 0 ? $basicSalary / ($workdays * 8) : 0;
        $deductionRate = $hourlyRate / 60;

        $deduction = AttendanceConfig::where('company_id', $employee->company_id)
            ->where('key', 'late_deduction_per_minute')
            ->value('value');

        if ($deduction && is_numeric($deduction)) {
            return round($totalLateMinutes * (float) $deduction, 2);
        }

        return round($totalLateMinutes * $deductionRate, 2);
    }

    public function calculateAbsentDeduction(Employee $employee, string $periodStart, string $periodEnd): float
    {
        $periodStart = Carbon::parse($periodStart);
        $periodEnd = Carbon::parse($periodEnd);

        $absentDays = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->where('status', 'absent')
            ->count();

        if ($absentDays <= 0) {
            return 0;
        }

        $basicSalary = (float) ($employee->basic_salary ?? 0);
        $workdays = $periodStart->daysInMonth;
        $perDayRate = $workdays > 0 ? $basicSalary / $workdays : 0;

        return round($perDayRate * $absentDays, 2);
    }

    public function getLeaveDays(Employee $employee, string $periodStart, string $periodEnd): array
    {
        $periodStart = Carbon::parse($periodStart);
        $periodEnd = Carbon::parse($periodEnd);

        $leaves = Leave::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->where(function ($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('start_date', [$periodStart, $periodEnd])
                    ->orWhereBetween('end_date', [$periodStart, $periodEnd])
                    ->orWhere(function ($q) use ($periodStart, $periodEnd) {
                        $q->where('start_date', '<=', $periodStart)
                            ->where('end_date', '>=', $periodEnd);
                    });
            })
            ->with('leaveType')
            ->get();

        $paidLeaveDays = 0;
        $unpaidLeaveDays = 0;

        foreach ($leaves as $leave) {
            $leaveStart = max(Carbon::parse($leave->start_date), $periodStart);
            $leaveEnd = min(Carbon::parse($leave->end_date), $periodEnd);
            $daysInPeriod = $leaveStart->diffInDays($leaveEnd) + 1;

            if ($leave->leaveType && $leave->leaveType->is_paid) {
                $paidLeaveDays += $daysInPeriod;
            } else {
                $unpaidLeaveDays += $daysInPeriod;
            }
        }

        return [
            'paid_leave_days' => $paidLeaveDays,
            'unpaid_leave_days' => $unpaidLeaveDays,
        ];
    }

    public function calculateLeaveDeduction(Employee $employee, string $periodStart, string $periodEnd): array
    {
        $periodStart = Carbon::parse($periodStart);
        $periodEnd = Carbon::parse($periodEnd);

        $leaveData = $this->getLeaveDays($employee, $periodStart, $periodEnd);
        $basicSalary = (float) ($employee->basic_salary ?? 0);
        $workdays = $periodStart->daysInMonth;
        $perDayRate = $workdays > 0 ? $basicSalary / $workdays : 0;

        $deductionAmount = round($perDayRate * $leaveData['unpaid_leave_days'], 2);

        return [
            'paid_leave_days' => $leaveData['paid_leave_days'],
            'unpaid_leave_days' => $leaveData['unpaid_leave_days'],
            'per_day_rate' => round($perDayRate, 2),
            'deduction_amount' => $deductionAmount,
        ];
    }

    public function getOvertimeHours(Employee $employee, string $periodStart, string $periodEnd): array
    {
        $periodStart = Carbon::parse($periodStart);
        $periodEnd = Carbon::parse($periodEnd);

        $overtimes = Overtime::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->get();

        $totalMinutes = $overtimes->sum('duration_minutes');
        $totalHours = round($totalMinutes / 60, 2);

        $rate = (float) ($employee->overtime_rate ?? 0);
        $hourlyRate = (float) ($employee->hourly_rate ?? 0);
        $totalPay = $rate > 0
            ? round($totalHours * $rate, 2)
            : round($totalHours * $hourlyRate * 1.5, 2);

        return [
            'overtime_count' => $overtimes->count(),
            'total_minutes' => $totalMinutes,
            'total_hours' => $totalHours,
            'total_pay' => $totalPay,
        ];
    }

    protected function getEmployeeSalaryComponents(Employee $employee): Collection
    {
        return EmployeeSalaryComponent::with('salaryComponent')
            ->where('employee_id', $employee->id)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->where('effective_date', '<=', now()->toDateString())
            ->get();
    }
}
