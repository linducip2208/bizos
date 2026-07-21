<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Kalkulasi THR sesuai PP 36/2021 & Permenaker 6/2016.
 */
class ThrCalculatorService
{
    /**
     * Hitung THR per karyawan.
     *
     * @param Employee     $employee
     * @param Carbon|null  $date  Tanggal perhitungan (default: H-7 Idul Fitri tahun berjalan)
     * @return array
     */
    public function calculateThr(Employee $employee, ?Carbon $date = null): array
    {
        $date = $date ?? $this->getThrDueDate((int) now()->format('Y'));
        $joinDate = Carbon::parse($employee->join_date);
        $monthsWorked = $this->calculateMonthsWorked($joinDate, $date);
        $monthlySalary = (float) $employee->basic_salary;

        if ($employee->employee_type === 'freelance' || $employee->employee_type === 'part_time') {
            return $this->calculateDailyWorkerThr($employee, $date);
        }

        if ($monthsWorked >= 12) {
            $thrAmount = $monthlySalary;
            $detail = '1 × gaji pokok bulanan (masa kerja ≥ 12 bulan)';
        } else {
            $thrAmount = round(($monthsWorked / 12) * $monthlySalary);
            $detail = "Pro-rata: {$monthsWorked}/12 × Rp " . number_format($monthlySalary) . " = Rp " . number_format($thrAmount);
        }

        return [
            'employee_id'        => $employee->id,
            'employee_name'      => trim($employee->first_name . ' ' . ($employee->last_name ?? '')),
            'employee_code'      => $employee->employee_code,
            'employee_type'      => $employee->employee_type,
            'join_date'          => $joinDate->format('d M Y'),
            'months_worked'      => $monthsWorked,
            'monthly_salary'     => $monthlySalary,
            'thr_amount'         => $thrAmount,
            'calculation_detail' => $detail,
            'due_date'           => $date->format('d M Y'),
        ];
    }

    /**
     * Hitung THR batch untuk banyak karyawan.
     */
    public function calculateBatchThr(Collection $employees, ?Carbon $date = null): array
    {
        $date = $date ?? $this->getThrDueDate((int) now()->format('Y'));
        $results = [];
        $totalThr = 0;

        foreach ($employees as $employee) {
            $result = $this->calculateThr($employee, $date);
            $results[] = $result;
            $totalThr += $result['thr_amount'];
        }

        return [
            'due_date'       => $date->format('d M Y'),
            'total_employees' => count($results),
            'total_thr'       => $totalThr,
            'details'         => $results,
        ];
    }

    /**
     * Tanggal jatuh tempo THR (H-7 hari raya keagamaan).
     */
    public function getThrDueDate(int $year): Carbon
    {
        $holiday = $this->getEidAlFitrEstimate($year);

        return $holiday->copy()->subDays(7);
    }

    // ──────────────────────────────────────────────
    //  Pekerja Harian (PP 36/2021)
    // ──────────────────────────────────────────────

    /**
     * THR pekerja harian lepas.
     * Rumus: (rata-rata upah sebulan) × (masa kerja bulan / 12).
     * Jika masa kerja >= 12 bulan: 1 × rata-rata upah sebulan.
     */
    protected function calculateDailyWorkerThr(Employee $employee, Carbon $date): array
    {
        $joinDate = Carbon::parse($employee->join_date);
        $monthsWorked = $this->calculateMonthsWorked($joinDate, $date);

        $dailyRate = (float) ($employee->overtime_rate ?? 0);
        if ($dailyRate <= 0) {
            $dailyRate = (float) $employee->basic_salary / 25;
        }

        $avgMonthly = round($dailyRate * 25);

        if ($monthsWorked >= 12) {
            $thrAmount = $avgMonthly;
            $detail = "1 × rata-rata upah bulanan Rp " . number_format($avgMonthly) . " (masa kerja ≥ 12 bulan)";
        } else {
            $thrAmount = round(($monthsWorked / 12) * $avgMonthly);
            $detail = "Pro-rata: {$monthsWorked}/12 × rata-rata upah Rp " . number_format($avgMonthly) . " = Rp " . number_format($thrAmount);
        }

        return [
            'employee_id'        => $employee->id,
            'employee_name'      => trim($employee->first_name . ' ' . ($employee->last_name ?? '')),
            'employee_code'      => $employee->employee_code,
            'employee_type'      => $employee->employee_type,
            'join_date'          => $joinDate->format('d M Y'),
            'months_worked'      => $monthsWorked,
            'monthly_salary'     => $avgMonthly,
            'daily_rate'         => $dailyRate,
            'thr_amount'         => $thrAmount,
            'calculation_detail' => $detail,
            'due_date'           => $date->format('d M Y'),
        ];
    }

    // ──────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────

    protected function calculateMonthsWorked(Carbon $joinDate, Carbon $date): int
    {
        $diff = $joinDate->diffInMonths($date);

        if ($joinDate->day > 15) {
            $diff = max(0, $diff - 1);
        }

        if ($joinDate->copy()->addMonth()->startOfMonth() > $date) {
            return 0;
        }

        return max(0, $diff);
    }

    /**
     * Estimasi Idul Fitri (1 Syawal) per tahun.
     * Gunakan perhitungan sederhana — produksi sebaiknya override manual.
     */
    protected function getEidAlFitrEstimate(int $year): Carbon
    {
        $estimates = [
            2025 => Carbon::create(2025, 3, 31),
            2026 => Carbon::create(2026, 3, 20),
            2027 => Carbon::create(2027, 3, 10),
            2028 => Carbon::create(2028, 2, 28),
            2029 => Carbon::create(2029, 2, 16),
            2030 => Carbon::create(2030, 2, 5),
        ];

        return $estimates[$year] ?? Carbon::create($year, 4, 15);
    }
}
