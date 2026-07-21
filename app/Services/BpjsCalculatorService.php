<?php

namespace App\Services;

use App\Models\BpjsConfig;

/**
 * Kalkulasi BPJS Kesehatan & Ketenagakerjaan sesuai PP 2024.
 */
class BpjsCalculatorService
{
    protected const DEFAULT_CEILING_KESEHATAN = 12000000;
    protected const DEFAULT_CEILING_JP = 12000000;

    // ──────────────────────────────────────────────
    //  Public API
    // ──────────────────────────────────────────────

    /**
     * Hitung seluruh iuran BPJS (Kes + TK).
     */
    public function calculateAllContributions(float $monthlySalary, string $riskGrade = 'medium'): array
    {
        $bpjsKes = $this->calculateBpjsKesehatan($monthlySalary);
        $bpjsTk = $this->calculateBpjsTk($monthlySalary, $riskGrade);

        $totalEmployer = $bpjsKes['employer_amount'] + $bpjsTk['total_employer'];
        $totalEmployee = $bpjsKes['employee_amount'] + $bpjsTk['total_employee'];
        $grandTotal = $totalEmployer + $totalEmployee;

        return [
            'salary'          => $monthlySalary,
            'risk_grade'      => $riskGrade,
            'bpjs_kesehatan'  => $bpjsKes,
            'bpjs_tk'         => $bpjsTk,
            'total_employer'  => $totalEmployer,
            'total_employee'  => $totalEmployee,
            'grand_total'     => $grandTotal,
        ];
    }

    /**
     * BPJS Kesehatan: Pemberi Kerja 4%, Pekerja 1%.
     * Ceiling: Rp 12.000.000.
     */
    public function calculateBpjsKesehatan(float $salary): array
    {
        $ceiling = $this->getCeiling('kesehatan');
        $base = min($salary, $ceiling);

        $employerRate = 0.04;
        $employeeRate = 0.01;

        $employerAmount = round($base * $employerRate);
        $employeeAmount = round($base * $employeeRate);
        $total = $employerAmount + $employeeAmount;

        $tier = $this->determineKesTier($salary);

        return [
            'component'        => 'BPJS Kesehatan',
            'base_salary'      => $base,
            'original_salary'  => $salary,
            'ceiling'          => $ceiling,
            'is_capped'        => $salary > $ceiling,
            'employer_rate'    => $employerRate,
            'employee_rate'    => $employeeRate,
            'employer_amount'  => $employerAmount,
            'employee_amount'  => $employeeAmount,
            'total'            => $total,
            'tier'             => $tier,
            'tier_label'       => $this->getTierLabel($tier),
        ];
    }

    /**
     * BPJS Ketenagakerjaan: JKK, JKM, JHT, JP.
     */
    public function calculateBpjsTk(float $salary, string $riskGrade = 'medium'): array
    {
        $jkk = $this->calculateJkk($salary, $riskGrade);
        $jkm = $this->calculateJkm($salary);
        $jht = $this->calculateJht($salary);
        $jp = $this->calculateJp($salary);

        $totalEmployer = $jkk['employer_amount'] + $jkm['employer_amount'] + $jht['employer_amount'] + $jp['employer_amount'];
        $totalEmployee = $jkk['employee_amount'] + $jkm['employee_amount'] + $jht['employee_amount'] + $jp['employee_amount'];

        return [
            'jkk'               => $jkk,
            'jkm'               => $jkm,
            'jht'               => $jht,
            'jp'                => $jp,
            'total_employer'    => $totalEmployer,
            'total_employee'    => $totalEmployee,
            'grand_total'       => $totalEmployer + $totalEmployee,
            'risk_grade'        => $riskGrade,
        ];
    }

    // ──────────────────────────────────────────────
    //  Per-komponen
    // ──────────────────────────────────────────────

    /**
     * JKK – Jaminan Kecelakaan Kerja (ditanggung pemberi kerja).
     */
    public function calculateJkk(float $salary, string $riskGrade = 'medium'): array
    {
        $rates = $this->getJkkRates();
        $rate = $rates[$riskGrade] ?? $rates['medium'];

        return [
            'component'        => 'JKK (Jaminan Kecelakaan Kerja)',
            'risk_grade'       => $riskGrade,
            'employer_rate'    => $rate,
            'employer_amount'  => round($salary * $rate),
            'employee_amount'  => 0,
        ];
    }

    /**
     * JKM – Jaminan Kematian (ditanggung pemberi kerja).
     */
    public function calculateJkm(float $salary): array
    {
        $rate = 0.003;

        return [
            'component'        => 'JKM (Jaminan Kematian)',
            'employer_rate'    => $rate,
            'employer_amount'  => round($salary * $rate),
            'employee_amount'  => 0,
        ];
    }

    /**
     * JHT – Jaminan Hari Tua (3,7% pemberi kerja, 2% pekerja).
     */
    public function calculateJht(float $salary): array
    {
        $employerRate = 0.037;
        $employeeRate = 0.02;

        return [
            'component'        => 'JHT (Jaminan Hari Tua)',
            'employer_rate'    => $employerRate,
            'employee_rate'    => $employeeRate,
            'employer_amount'  => round($salary * $employerRate),
            'employee_amount'  => round($salary * $employeeRate),
        ];
    }

    /**
     * JP – Jaminan Pensiun (2% pemberi kerja, 1% pekerja).
     * Ceiling: Rp 12.000.000.
     */
    public function calculateJp(float $salary): array
    {
        $ceiling = $this->getCeiling('jp');
        $base = min($salary, $ceiling);

        $employerRate = 0.02;
        $employeeRate = 0.01;

        return [
            'component'        => 'JP (Jaminan Pensiun)',
            'base_salary'      => $base,
            'ceiling'          => $ceiling,
            'is_capped'        => $salary > $ceiling,
            'employer_rate'    => $employerRate,
            'employee_rate'    => $employeeRate,
            'employer_amount'  => round($base * $employerRate),
            'employee_amount'  => round($base * $employeeRate),
        ];
    }

    // ──────────────────────────────────────────────
    //  Ceiling & Konfigurasi
    // ──────────────────────────────────────────────

    public function getCeiling(string $component): float
    {
        $config = BpjsConfig::where('bpjs_type', $component)
            ->where('is_active', true)
            ->latest('effective_year')
            ->first();

        if ($config && $config->max_salary_cap) {
            return (float) $config->max_salary_cap;
        }

        return match ($component) {
            'kesehatan' => self::DEFAULT_CEILING_KESEHATAN,
            'jp'        => self::DEFAULT_CEILING_JP,
            default     => PHP_FLOAT_MAX,
        };
    }

    public function updateCeiling(string $component, float $newCeiling): void
    {
        BpjsConfig::where('bpjs_type', $component)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        BpjsConfig::create([
            'bpjs_type'       => $component,
            'max_salary_cap'  => $newCeiling,
            'effective_year'  => (int) now()->format('Y'),
            'is_active'       => true,
        ]);
    }

    // ──────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────

    protected function getJkkRates(): array
    {
        return [
            'very_low'  => 0.0024,
            'low'       => 0.0054,
            'medium'    => 0.0089,
            'high'      => 0.0127,
            'very_high' => 0.0174,
        ];
    }

    protected function determineKesTier(float $salary): string
    {
        if ($salary < 4000000) {
            return 'III';
        }
        if ($salary <= 8000000) {
            return 'II';
        }
        return 'I';
    }

    protected function getTierLabel(string $tier): string
    {
        return match ($tier) {
            'I'   => 'Kelas I (Rawat Inap VIP)',
            'II'  => 'Kelas II (Rawat Inap Standar)',
            'III' => 'Kelas III (Rawat Inap Dasar)',
            default => '-',
        };
    }

    public static function getRiskGrades(): array
    {
        return [
            'very_low'  => 'Sangat Rendah (0,24%) — Administrasi/Perkantoran',
            'low'       => 'Rendah (0,54%) — Ringan',
            'medium'    => 'Sedang (0,89%) — Menengah',
            'high'      => 'Tinggi (1,27%) — Berat',
            'very_high' => 'Sangat Tinggi (1,74%) — Sangat Berat',
        ];
    }
}
