<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeAllowance;
use App\Models\EmployeeBonus;
use App\Models\EmployeeDeduction;
use App\Models\PayrollApproval;
use App\Models\PayrollPeriod;
use App\Models\PayrollSimulation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    protected PayrollIntegrationService $payrollIntegration;
    protected BpjsCalculatorService $bpjsCalculator;

    public function __construct()
    {
        $this->payrollIntegration = app(PayrollIntegrationService::class);
        $this->bpjsCalculator = app(BpjsCalculatorService::class);
    }

    public function simulatePayroll(array $employeeIds, array $changes = []): array
    {
        $employees = Employee::whereIn('id', $employeeIds)
            ->where('status', 'active')
            ->get();

        $results = [];
        $summary = [
            'total_gross' => 0,
            'total_deductions' => 0,
            'total_net' => 0,
            'total_employees' => $employees->count(),
            'details' => [],
        ];

        foreach ($employees as $employee) {
            $basicSalary = (float) ($employee->basic_salary ?? 0);

            if (isset($changes[$employee->id]['basic_salary'])) {
                $basicSalary = (float) $changes[$employee->id]['basic_salary'];
            }

            $allowanceTotal = $this->calculateEmployeeAllowances($employee, $changes);
            $deductionTotal = $this->calculateEmployeeDeductions($employee, $changes);
            $bonusTotal = $this->calculateEmployeeBonuses($employee, $changes);

            $grossSalary = $basicSalary + $allowanceTotal + $bonusTotal;
            $grossBeforeDeduction = $grossSalary - $deductionTotal;

            $riskGrade = $employee->bpjs_tk_risk_grade ?? 'medium';
            $bpjsCalc = $this->bpjsCalculator->calculateAllContributions($grossSalary, $riskGrade);

            $totalEmployeeBpjs =
                ($bpjsCalc['bpjs_tk']['jht']['employee_amount'] ?? 0) +
                ($bpjsCalc['bpjs_tk']['jp']['employee_amount'] ?? 0) +
                ($bpjsCalc['bpjs_kesehatan']['employee_amount'] ?? 0);

            $netSalary = $grossBeforeDeduction - $totalEmployeeBpjs;

            $detail = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'employee_code' => $employee->employee_code,
                'basic_salary' => round($basicSalary, 2),
                'allowances' => round($allowanceTotal, 2),
                'deductions' => round($deductionTotal, 2),
                'bonuses' => round($bonusTotal, 2),
                'bpjs_employee' => round($totalEmployeeBpjs, 2),
                'gross_salary' => round($grossSalary, 2),
                'net_salary' => round($netSalary, 2),
            ];

            $results[] = $detail;

            $summary['total_gross'] += $grossSalary;
            $summary['total_deductions'] += $deductionTotal + $totalEmployeeBpjs;
            $summary['total_net'] += $netSalary;
        }

        $summary['details'] = $results;
        $summary['total_gross'] = round($summary['total_gross'], 2);
        $summary['total_deductions'] = round($summary['total_deductions'], 2);
        $summary['total_net'] = round($summary['total_net'], 2);

        return $summary;
    }

    public function compareSimulations(int $sim1Id, int $sim2Id): array
    {
        $sim1 = PayrollSimulation::findOrFail($sim1Id);
        $sim2 = PayrollSimulation::findOrFail($sim2Id);

        $result1 = $sim1->result ?? [];
        $result2 = $sim2->result ?? [];

        $comparison = [
            'simulation_1' => ['id' => $sim1->id, 'name' => $sim1->name],
            'simulation_2' => ['id' => $sim2->id, 'name' => $sim2->name],
            'total_gross_diff' => round(((float) ($result2['total_gross'] ?? 0)) - ((float) ($result1['total_gross'] ?? 0)), 2),
            'total_deductions_diff' => round(((float) ($result2['total_deductions'] ?? 0)) - ((float) ($result1['total_deductions'] ?? 0)), 2),
            'total_net_diff' => round(((float) ($result2['total_net'] ?? 0)) - ((float) ($result1['total_net'] ?? 0)), 2),
            'employee_count_diff' => ((int) ($result2['total_employees'] ?? 0)) - ((int) ($result1['total_employees'] ?? 0)),
            'per_employee_diff' => [],
        ];

        $details1 = [];
        foreach ($result1['details'] ?? [] as $d) {
            $details1[$d['employee_id']] = $d;
        }

        $details2 = [];
        foreach ($result2['details'] ?? [] as $d) {
            $details2[$d['employee_id']] = $d;
        }

        $allEmployeeIds = array_unique(array_merge(array_keys($details1), array_keys($details2)));

        foreach ($allEmployeeIds as $empId) {
            $emp1 = $details1[$empId] ?? null;
            $emp2 = $details2[$empId] ?? null;

            $comparison['per_employee_diff'][] = [
                'employee_id' => $empId,
                'employee_name' => $emp2['employee_name'] ?? $emp1['employee_name'] ?? 'Unknown',
                'net_salary_1' => $emp1['net_salary'] ?? 0,
                'net_salary_2' => $emp2['net_salary'] ?? 0,
                'difference' => round(((float) ($emp2['net_salary'] ?? 0)) - ((float) ($emp1['net_salary'] ?? 0)), 2),
            ];
        }

        return $comparison;
    }

    public function generateApprovalFlow(PayrollPeriod $period): void
    {
        $approvalLevels = [
            1 => 'Manager',
            2 => 'Finance',
            3 => 'Director',
        ];

        foreach ($approvalLevels as $level => $roleName) {
            $approver = Employee::where('company_id', $period->company_id)
                ->whereHas('position', function ($q) use ($roleName) {
                    $q->where('name', 'like', "%{$roleName}%");
                })
                ->first();

            if ($approver) {
                PayrollApproval::updateOrCreate(
                    [
                        'payroll_period_id' => $period->id,
                        'level' => $level,
                    ],
                    [
                        'approver_id' => $approver->id,
                        'status' => 'pending',
                        'comment' => null,
                    ]
                );
            }
        }
    }

    protected function calculateEmployeeAllowances(Employee $employee, array $changes = []): float
    {
        $total = EmployeeAllowance::where('employee_id', $employee->id)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->where('effective_date', '<=', now()->toDateString())
            ->sum('amount');

        if (isset($changes[$employee->id]['allowance_override'])) {
            $total = (float) $changes[$employee->id]['allowance_override'];
        }

        return $total;
    }

    protected function calculateEmployeeDeductions(Employee $employee, array $changes = []): float
    {
        $total = EmployeeDeduction::where('employee_id', $employee->id)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->where('effective_date', '<=', now()->toDateString())
            ->sum('amount');

        if (isset($changes[$employee->id]['deduction_override'])) {
            $total = (float) $changes[$employee->id]['deduction_override'];
        }

        return $total;
    }

    protected function calculateEmployeeBonuses(Employee $employee, array $changes = []): float
    {
        $total = EmployeeBonus::where('employee_id', $employee->id)
            ->whereMonth('issued_at', now()->month)
            ->whereYear('issued_at', now()->year)
            ->sum('amount');

        if (isset($changes[$employee->id]['bonus_override'])) {
            $total = (float) $changes[$employee->id]['bonus_override'];
        }

        return $total;
    }
}
