<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;

/**
 * Kalkulasi lembur sesuai Kepmenakertrans No. KEP-102/MEN/VI/2004.
 */
class OvertimeCalculatorService
{
    /**
     * Rumus upah sejam: 1/173 × gaji bulanan.
     */
    public function getHourlyRate(float $monthlySalary): float
    {
        return round($monthlySalary / 173, 2);
    }

    /**
     * Hitung upah lembur.
     *
     * @param Employee $employee
     * @param float    $hoursWorked  Total jam kerja (termasuk break)
     * @param string   $dayType      workday|restday_5day|restday_6day|public_holiday_workday|public_holiday_restday
     * @param bool     $hasBreak     Apakah ada istirahat (min 4 jam → potong 1 jam)
     * @return array
     */
    public function calculateOvertime(Employee $employee, float $hoursWorked, string $dayType, bool $hasBreak = true): array
    {
        $monthlySalary = (float) $employee->basic_salary;
        $hourlyRate = $this->getHourlyRate($monthlySalary);

        $effectiveHours = $hoursWorked;

        if ($hasBreak && $hoursWorked >= 4) {
            $effectiveHours = max(0, $hoursWorked - 1);
        }

        $breakdown = $this->calculateBreakdown($effectiveHours, $dayType, $hourlyRate);
        $totalPay = array_sum(array_column($breakdown, 'amount'));

        return [
            'base_wage'             => $monthlySalary,
            'hourly_rate'           => $hourlyRate,
            'hours_worked'          => $hoursWorked,
            'effective_hours'       => $effectiveHours,
            'break_deducted'        => $hoursWorked - $effectiveHours,
            'day_type'              => $dayType,
            'day_type_label'        => $this->getDayTypeLabel($dayType),
            'overtime_breakdown'    => $breakdown,
            'total_overtime_pay'    => $totalPay,
        ];
    }

    // ──────────────────────────────────────────────
    //  Perhitungan per jam
    // ──────────────────────────────────────────────

    protected function calculateBreakdown(float $effectiveHours, string $dayType, float $hourlyRate): array
    {
        $breakdown = [];

        if ($effectiveHours <= 0) {
            return $breakdown;
        }

        $fullHours = (int) floor($effectiveHours);
        $remainingMinutes = ($effectiveHours - $fullHours) * 60;

        $rules = $this->getOvertimeRules($dayType);

        $hour = 1;

        while ($hour <= $fullHours) {
            $multiplier = $this->getMultiplierForHour($hour, $rules);
            $amount = round($hourlyRate * $multiplier, 2);
            $breakdown[] = [
                'hour'        => $hour,
                'hour_label'  => "Jam ke-{$hour}",
                'multiplier'  => $multiplier,
                'rate'        => $hourlyRate * $multiplier,
                'amount'      => $amount,
            ];
            $hour++;
        }

        if ($remainingMinutes > 0 && $fullHours < count($rules)) {
            $nextHour = $fullHours + 1;
            $multiplier = $this->getMultiplierForHour($nextHour, $rules);
            $fraction = $remainingMinutes / 60;
            $amount = round($hourlyRate * $multiplier * $fraction, 2);
            $breakdown[] = [
                'hour'        => $nextHour,
                'hour_label'  => "Jam ke-{$nextHour} ({$remainingMinutes} menit)",
                'multiplier'  => $multiplier,
                'rate'        => $hourlyRate * $multiplier,
                'amount'      => $amount,
            ];
        }

        return $breakdown;
    }

    protected function getMultiplierForHour(int $hour, array $rules): float
    {
        foreach ($rules as $rule) {
            if ($hour >= $rule['from'] && $hour <= $rule['to']) {
                return $rule['multiplier'];
            }
        }

        return 2.0;
    }

    /**
     * Aturan pengali per tipe hari.
     */
    protected function getOvertimeRules(string $dayType): array
    {
        return match ($dayType) {
            'workday' => [
                ['from' => 1,  'to' => 1,   'multiplier' => 1.5, 'label' => 'Jam pertama'],
                ['from' => 2,  'to' => 99,  'multiplier' => 2.0, 'label' => 'Jam berikutnya'],
            ],

            'restday_5day' => [
                ['from' => 1,  'to' => 8,   'multiplier' => 2.0, 'label' => 'Jam ke-1 s.d. 8'],
                ['from' => 9,  'to' => 9,   'multiplier' => 3.0, 'label' => 'Jam ke-9'],
                ['from' => 10, 'to' => 11,  'multiplier' => 4.0, 'label' => 'Jam ke-10 s.d. 11'],
                ['from' => 12, 'to' => 99,  'multiplier' => 2.0, 'label' => 'Jam berikutnya'],
            ],

            'restday_6day' => [
                ['from' => 1,  'to' => 7,   'multiplier' => 2.0, 'label' => 'Jam ke-1 s.d. 7'],
                ['from' => 8,  'to' => 8,   'multiplier' => 3.0, 'label' => 'Jam ke-8'],
                ['from' => 9,  'to' => 10,  'multiplier' => 4.0, 'label' => 'Jam ke-9 s.d. 10'],
                ['from' => 11, 'to' => 99,  'multiplier' => 2.0, 'label' => 'Jam berikutnya'],
            ],

            'public_holiday_workday' => [
                ['from' => 1,  'to' => 5,   'multiplier' => 2.0, 'label' => 'Jam ke-1 s.d. 5'],
                ['from' => 6,  'to' => 6,   'multiplier' => 3.0, 'label' => 'Jam ke-6'],
                ['from' => 7,  'to' => 8,   'multiplier' => 4.0, 'label' => 'Jam ke-7 s.d. 8'],
                ['from' => 9,  'to' => 99,  'multiplier' => 2.0, 'label' => 'Jam berikutnya'],
            ],

            'public_holiday_restday' => [
                ['from' => 1,  'to' => 5,   'multiplier' => 2.0, 'label' => 'Jam ke-1 s.d. 5'],
                ['from' => 6,  'to' => 6,   'multiplier' => 3.0, 'label' => 'Jam ke-6'],
                ['from' => 7,  'to' => 8,   'multiplier' => 4.0, 'label' => 'Jam ke-7 s.d. 8'],
                ['from' => 9,  'to' => 99,  'multiplier' => 2.0, 'label' => 'Jam berikutnya'],
            ],

            default => [
                ['from' => 1, 'to' => 1,  'multiplier' => 1.5],
                ['from' => 2, 'to' => 99, 'multiplier' => 2.0],
            ],
        };
    }

    protected function getDayTypeLabel(string $dayType): string
    {
        return match ($dayType) {
            'workday'                  => 'Hari Kerja',
            'restday_5day'             => 'Hari Istirahat (5 Hari Kerja)',
            'restday_6day'             => 'Hari Istirahat (6 Hari Kerja)',
            'public_holiday_workday'   => 'Hari Libur Nasional (Hari Kerja)',
            'public_holiday_restday'   => 'Hari Libur Nasional (Hari Istirahat)',
            default                    => 'Tidak Diketahui',
        };
    }

    public static function getDayTypes(): array
    {
        return [
            'workday'                => 'Hari Kerja',
            'restday_5day'           => 'Hari Istirahat (5 Hari Kerja)',
            'restday_6day'           => 'Hari Istirahat (6 Hari Kerja)',
            'public_holiday_workday' => 'Hari Libur Nasional pada Hari Kerja',
            'public_holiday_restday' => 'Hari Libur Nasional pada Hari Istirahat',
        ];
    }
}
