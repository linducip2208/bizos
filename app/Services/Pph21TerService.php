<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;

class Pph21TerService
{
    /**
     * PTKP 2025 (Penghasilan Tidak Kena Pajak) per tahun.
     */
    protected const PTKP_VALUES = [
        'TK/0' => 54000000,
        'TK/1' => 58500000,
        'TK/2' => 63000000,
        'TK/3' => 67500000,
        'K/0'  => 58500000,
        'K/1'  => 63000000,
        'K/2'  => 67500000,
        'K/3'  => 72000000,
    ];

    /**
     * Pemetaan kode PTKP ke kategori TER.
     */
    protected const PTKP_TO_TER = [
        'TK/0' => 'A',
        'TK/1' => 'A',
        'K/0'  => 'A',
        'TK/2' => 'B',
        'TK/3' => 'B',
        'K/1'  => 'B',
        'K/2'  => 'B',
        'K/3'  => 'C',
    ];

    /**
     * Tarif Pasal 17 (progresif tahunan).
     */
    protected const PASAL_17_BRACKETS = [
        ['limit' => 60000000,        'rate' => 0.05],
        ['limit' => 250000000,       'rate' => 0.15],
        ['limit' => 500000000,       'rate' => 0.25],
        ['limit' => 5000000000,      'rate' => 0.30],
        ['limit' => PHP_FLOAT_MAX,   'rate' => 0.35],
    ];

    /**
     * TER A – TK/0, TK/1, K/0
     * Bracket: [batasBawah, batasAtas, tarif]
     */
    protected const TER_A_BRACKETS = [
        [0,          5400000,  0.0000],
        [5400001,    5650000,  0.0025],
        [5650001,    5950000,  0.0050],
        [5950001,    6300000,  0.0075],
        [6300001,    6750000,  0.0100],
        [6750001,    7500000,  0.0125],
        [7500001,    8550000,  0.0150],
        [8550001,    9650000,  0.0175],
        [9650001,    10050000, 0.0200],
        [10050001,   10350000, 0.0225],
        [10350001,   10700000, 0.0250],
        [10700001,   11050000, 0.0300],
        [11050001,   11600000, 0.0350],
        [11600001,   12500000, 0.0400],
        [12500001,   13750000, 0.0500],
        [13750001,   15100000, 0.0600],
        [15100001,   16950000, 0.0700],
        [16950001,   19750000, 0.0800],
        [19750001,   24150000, 0.0900],
        [24150001,   26450000, 0.1000],
        [26450001,   28000000, 0.1100],
        [28000001,   30050000, 0.1200],
        [30050001,   32400000, 0.1300],
        [32400001,   35400000, 0.1400],
        [35400001,   39100000, 0.1500],
        [39100001,   43850000, 0.1600],
        [43850001,   47800000, 0.1700],
        [47800001,   51400000, 0.1800],
        [51400001,   56300000, 0.1900],
        [56300001,   62200000, 0.2000],
        [62200001,   68600000, 0.2100],
        [68600001,   77500000, 0.2200],
        [77500001,   89000000, 0.2300],
        [89000001,   103000000, 0.2400],
        [103000001,  125000000, 0.2500],
        [125000001,  157000000, 0.2600],
        [157000001,  206000000, 0.2700],
        [206000001,  337000000, 0.2800],
        [337000001,  454000000, 0.2900],
        [454000001,  550000000, 0.3000],
        [550000001,  695000000, 0.3100],
        [695000001,  910000000, 0.3200],
        [910000001,  1400000000,0.3300],
        [1400000001, PHP_FLOAT_MAX, 0.3400],
    ];

    /**
     * TER B – TK/2, TK/3, K/1, K/2
     */
    protected const TER_B_BRACKETS = [
        [0,          6200000,  0.0000],
        [6200001,    6500000,  0.0050],
        [6500001,    6850000,  0.0075],
        [6850001,    7300000,  0.0100],
        [7300001,    9200000,  0.0150],
        [9200001,    10750000, 0.0200],
        [10750001,   11250000, 0.0250],
        [11250001,   11600000, 0.0300],
        [11600001,   12600000, 0.0400],
        [12600001,   13600000, 0.0500],
        [13600001,   14950000, 0.0600],
        [14950001,   16400000, 0.0700],
        [16400001,   18450000, 0.0800],
        [18450001,   21050000, 0.0900],
        [21050001,   23950000, 0.1000],
        [23950001,   26400000, 0.1100],
        [26400001,   28800000, 0.1200],
        [28800001,   31700000, 0.1300],
        [31700001,   34600000, 0.1400],
        [34600001,   38000000, 0.1500],
        [38000001,   42000000, 0.1600],
        [42000001,   45900000, 0.1700],
        [45900001,   49600000, 0.1800],
        [49600001,   54100000, 0.1900],
        [54100001,   59100000, 0.2000],
        [59100001,   65300000, 0.2100],
        [65300001,   72100000, 0.2200],
        [72100001,   81000000, 0.2300],
        [81000001,   91600000, 0.2400],
        [91600001,   105400000,0.2500],
        [105400001,  126000000,0.2600],
        [126000001,  150000000,0.2700],
        [150000001,  177000000,0.2800],
        [177000001,  246000000,0.2900],
        [246000001,  330000000,0.3000],
        [330000001,  407000000,0.3100],
        [407000001,  498000000,0.3200],
        [498000001,  624000000,0.3300],
        [624000001,  815000000,0.3400],
        [815000001,  1400000000,0.3500],
        [1400000001, PHP_FLOAT_MAX, 0.3600],
    ];

    /**
     * TER C – K/3
     */
    protected const TER_C_BRACKETS = [
        [0,          6600000,  0.0000],
        [6600001,    6950000,  0.0075],
        [6950001,    7350000,  0.0100],
        [7350001,    7800000,  0.0125],
        [7800001,    8850000,  0.0150],
        [8850001,    9800000,  0.0175],
        [9800001,    10950000, 0.0200],
        [10950001,   11200000, 0.0300],
        [11200001,   12050000, 0.0400],
        [12050001,   12950000, 0.0500],
        [12950001,   14150000, 0.0600],
        [14150001,   15550000, 0.0700],
        [15550001,   17050000, 0.0800],
        [17050001,   19500000, 0.0900],
        [19500001,   22700000, 0.1000],
        [22700001,   26600000, 0.1100],
        [26600001,   30100000, 0.1200],
        [30100001,   33500000, 0.1300],
        [33500001,   37100000, 0.1400],
        [37100001,   41100000, 0.1500],
        [41100001,   45800000, 0.1600],
        [45800001,   50300000, 0.1700],
        [50300001,   54300000, 0.1800],
        [54300001,   58700000, 0.1900],
        [58700001,   63700000, 0.2000],
        [63700001,   69900000, 0.2100],
        [69900001,   78000000, 0.2200],
        [78000001,   88400000, 0.2300],
        [88400001,   100800000,0.2400],
        [100800001,  116000000,0.2500],
        [116000001,  136500000,0.2600],
        [136500001,  160500000,0.2700],
        [160500001,  189500000,0.2800],
        [189500001,  254000000,0.2900],
        [254000001,  345000000,0.3000],
        [345000001,  424000000,0.3100],
        [424000001,  518000000,0.3200],
        [518000001,  648000000,0.3300],
        [648000001,  850000000,0.3400],
        [850000001,  1400000000,0.3500],
        [1400000001, PHP_FLOAT_MAX, 0.3600],
    ];

    // ──────────────────────────────────────────────
    //  Public API
    // ──────────────────────────────────────────────

    /**
     * Hitung PPh21 TER bulanan.
     */
    public function calculateMonthlyTer(float $grossMonthlySalary, string $ptkpCode, string $period = 'monthly'): array
    {
        $ptkpCode = strtoupper(trim($ptkpCode));
        $ptkpYearly = self::PTKP_VALUES[$ptkpCode] ?? self::PTKP_VALUES['TK/0'];
        $terCategory = self::PTKP_TO_TER[$ptkpCode] ?? 'A';
        $terRate = $this->getTerRate($ptkpCode, $grossMonthlySalary);

        $pph21Monthly = round($grossMonthlySalary * $terRate);

        $annualGross = $grossMonthlySalary * 12;
        $pkpYearly = max(0, $annualGross - $ptkpYearly);
        $monthlyPkp = round($pkpYearly / 12);

        return [
            'ter_category'       => $terCategory,
            'ter_rate'           => $terRate,
            'ter_rate_percent'   => round($terRate * 100, 2),
            'pph21_amount'       => $pph21Monthly,
            'ptkp_amount'        => $ptkpYearly,
            'pkp_amount'         => $monthlyPkp,
            'pkp_yearly'         => $pkpYearly,
            'gross_monthly'      => $grossMonthlySalary,
            'gross_yearly'       => $annualGross,
            'ptkp_code'          => $ptkpCode,
            'period'             => $period,
            'details'            => [
                'ptkp_description'    => $this->getPtkpDescription($ptkpCode),
                'ter_category_reason' => $this->getTerCategoryReason($ptkpCode),
                'calculation'         => "Rp {$grossMonthlySalary} × {$terRate} = Rp {$pph21Monthly}",
            ],
        ];
    }

    /**
     * Dapatkan tarif TER berdasarkan PTKP dan gaji bruto bulanan.
     */
    public function getTerRate(string $ptkpCode, float $grossMonthlySalary): float
    {
        $terCategory = self::PTKP_TO_TER[strtoupper(trim($ptkpCode))] ?? 'A';
        $brackets = match ($terCategory) {
            'B' => self::TER_B_BRACKETS,
            'C' => self::TER_C_BRACKETS,
            default => self::TER_A_BRACKETS,
        };

        foreach ($brackets as $bracket) {
            if ($grossMonthlySalary >= $bracket[0] && $grossMonthlySalary <= $bracket[1]) {
                return $bracket[2];
            }
        }

        return 0.34;
    }

    /**
     * Rekonsiliasi tahunan: kurang bayar / lebih bayar.
     */
    public function calculateYearlyReconciliation(float $totalPph21TerPaid, float $yearlyGrossSalary, string $ptkpCode): array
    {
        $ptkpCode = strtoupper(trim($ptkpCode));
        $ptkpYearly = self::PTKP_VALUES[$ptkpCode] ?? self::PTKP_VALUES['TK/0'];
        $pkp = max(0, $yearlyGrossSalary - $ptkpYearly);

        $pph21Terutang = $this->calculatePasal17($pkp);

        $selisih = $pph21Terutang - $totalPph21TerPaid;
        $status = $selisih > 0 ? 'kurang_bayar' : ($selisih < 0 ? 'lebih_bayar' : 'nihil');

        return [
            'yearly_gross_salary'    => $yearlyGrossSalary,
            'ptkp_amount'            => $ptkpYearly,
            'ptkp_code'              => $ptkpCode,
            'yearly_pkp'             => $pkp,
            'yearly_pph21_terutang'  => $pph21Terutang,
            'total_pph21_ter_paid'   => $totalPph21TerPaid,
            'selisih'                => abs($selisih),
            'status'                 => $status,
            'status_label'           => match ($status) {
                'kurang_bayar' => 'Kurang Bayar',
                'lebih_bayar'  => 'Lebih Bayar',
                'nihil'        => 'Nihil',
                default        => '-',
            },
            'pasal17_breakdown'      => $this->getPasal17Breakdown($pkp),
        ];
    }

    /**
     * Generate data form 1721-A1 (DJP).
     */
    public function generate1721A1(Employee $employee, int $year): array
    {
        $ptkpCode = $employee->ptkp_code ?? 'TK/0';
        $ptkpYearly = self::PTKP_VALUES[$ptkpCode] ?? self::PTKP_VALUES['TK/0'];

        $monthlySalary = (float) $employee->basic_salary;

        $yearlyGross = $monthlySalary * 12;
        $yearlyPkp = max(0, $yearlyGross - $ptkpYearly);
        $pph21Terutang = $this->calculatePasal17($yearlyPkp);

        return [
            'tahun_pajak'                => $year,
            'masa_perolehan'             => '01-12',
            'npwp'                       => $employee->tax_number,
            'nik'                        => $employee->id_number,
            'nama'                       => trim($employee->first_name . ' ' . ($employee->last_name ?? '')),
            'alamat'                     => $employee->address,
            'jenis_kelamin'              => $employee->gender,
            'status_ptkp'                => $ptkpCode,
            'jabatan'                    => optional($employee->position)->name,
            'penghasilan_bruto'          => $yearlyGross,
            'ptkp'                       => $ptkpYearly,
            'pkp'                        => $yearlyPkp,
            'pph21_terutang'             => $pph21Terutang,
            'pph21_telah_dipotong'       => 0,
            'pph21_kurang_bayar'         => 0,
            'pph21_lebih_bayar'          => 0,
            'tanggal_bukti_potong'       => now()->format('d-m-Y'),
            'pemotong_npwp'              => null,
            'pemotong_nama'              => null,
            'pemotong_tanda_tangan'      => null,
        ];
    }

    /**
     * Gross-up: balik hitung gaji bruto dari take-home yang diinginkan.
     */
    public function calculateGrossUp(float $desiredTakeHome, string $ptkpCode): array
    {
        $ptkpCode = strtoupper(trim($ptkpCode));
        $terCategory = self::PTKP_TO_TER[$ptkpCode] ?? 'A';

        $estimatedGross = $desiredTakeHome;
        $iterations = 0;
        $maxIterations = 50;
        $tolerance = 100;

        while ($iterations < $maxIterations) {
            $terRate = $this->getTerRate($ptkpCode, $estimatedGross);
            $pph21 = round($estimatedGross * $terRate);
            $actualTakeHome = $estimatedGross - $pph21;
            $diff = $desiredTakeHome - $actualTakeHome;

            if (abs($diff) < $tolerance) {
                break;
            }

            $estimatedGross += $diff;
            $iterations++;
        }

        $terRate = $this->getTerRate($ptkpCode, $estimatedGross);
        $pph21 = round($estimatedGross * $terRate);
        $actualTakeHome = $estimatedGross - $pph21;

        return [
            'desired_take_home'  => $desiredTakeHome,
            'required_gross'     => round($estimatedGross),
            'ter_rate'           => $terRate,
            'ter_rate_percent'   => round($terRate * 100, 2),
            'pph21_amount'       => $pph21,
            'actual_take_home'   => $actualTakeHome,
            'ptkp_code'          => $ptkpCode,
            'ter_category'       => $terCategory,
        ];
    }

    // ──────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────

    protected function calculatePasal17(float $pkp): float
    {
        $tax = 0;
        $previousLimit = 0;

        foreach (self::PASAL_17_BRACKETS as $bracket) {
            $limit = $bracket['limit'];
            $rate = $bracket['rate'];

            if ($pkp > $previousLimit) {
                $taxableInBracket = min($pkp - $previousLimit, $limit - $previousLimit);
                $tax += $taxableInBracket * $rate;
            }

            $previousLimit = $limit;
            if ($pkp <= $limit) {
                break;
            }
        }

        return round($tax);
    }

    protected function getPasal17Breakdown(float $pkp): array
    {
        $breakdown = [];
        $previousLimit = 0;
        $sisa = $pkp;

        foreach (self::PASAL_17_BRACKETS as $i => $bracket) {
            if ($sisa <= 0) {
                break;
            }

            $limit = $bracket['limit'];
            $bracketRange = $limit - $previousLimit;
            $taxable = min($sisa, $bracketRange);
            $taxHere = round($taxable * $bracket['rate']);

            if ($taxable > 0) {
                $breakdown[] = [
                    'layer'        => $i + 1,
                    'range'        => "Rp " . number_format($previousLimit + 1) . " s.d. Rp " . number_format($limit),
                    'taxable'      => $taxable,
                    'rate'         => $bracket['rate'],
                    'rate_percent' => round($bracket['rate'] * 100, 1) . '%',
                    'tax'          => $taxHere,
                ];
            }

            $sisa -= $taxable;
            $previousLimit = $limit;
        }

        return $breakdown;
    }

    protected function getPtkpDescription(string $ptkpCode): string
    {
        return match (strtoupper($ptkpCode)) {
            'TK/0' => 'Tidak Kawin, 0 Tanggungan',
            'TK/1' => 'Tidak Kawin, 1 Tanggungan',
            'TK/2' => 'Tidak Kawin, 2 Tanggungan',
            'TK/3' => 'Tidak Kawin, 3 Tanggungan',
            'K/0'  => 'Kawin, 0 Tanggungan',
            'K/1'  => 'Kawin, 1 Tanggungan',
            'K/2'  => 'Kawin, 2 Tanggungan',
            'K/3'  => 'Kawin, 3 Tanggungan',
            default => 'Tidak Diketahui',
        };
    }

    protected function getTerCategoryReason(string $ptkpCode): string
    {
        $cat = self::PTKP_TO_TER[strtoupper($ptkpCode)] ?? 'A';

        return match ($cat) {
            'A' => 'TER A: TK/0, TK/1, K/0 — tanggungan ≤ 1',
            'B' => 'TER B: TK/2, TK/3, K/1, K/2 — tanggungan 2-3',
            'C' => 'TER C: K/3 — tanggungan > 3',
            default => 'Tidak diketahui',
        };
    }

    /**
     * Ambil semua bracket untuk kategori TER tertentu (untuk tampilan tabel di UI).
     */
    public static function getBrackets(string $category): array
    {
        return match (strtoupper($category)) {
            'A' => self::TER_A_BRACKETS,
            'B' => self::TER_B_BRACKETS,
            'C' => self::TER_C_BRACKETS,
            default => self::TER_A_BRACKETS,
        };
    }

    public static function getPtkpValues(): array
    {
        return self::PTKP_VALUES;
    }
}
