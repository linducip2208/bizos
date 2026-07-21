<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Client;
use App\Models\Company;
use App\Models\Deal;
use App\Models\Employee;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\PosTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\User;
use App\Models\WorkCenter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class AdvancedAnalyticsService
{
    public function employeeRetentionCohort(int $companyId): array
    {
        $employees = Employee::where('company_id', $companyId)
            ->select('id', 'join_date', 'termination_date', 'status')
            ->get();

        $byCohort = [];
        foreach ($employees as $emp) {
            if (!$emp->join_date) continue;
            $hireMonth = Carbon::parse($emp->join_date)->format('Y-m');
            $termDate = $emp->termination_date ? Carbon::parse($emp->termination_date) : null;

            if (!isset($byCohort[$hireMonth])) {
                $byCohort[$hireMonth] = ['hired' => 0, 'terminations' => [], 'active' => 0];
            }
            $byCohort[$hireMonth]['hired']++;

            if ($termDate) {
                $monthDiff = Carbon::parse($hireMonth . '-01')->diffInMonths($termDate);
                if (!isset($byCohort[$hireMonth]['terminations'][$monthDiff])) {
                    $byCohort[$hireMonth]['terminations'][$monthDiff] = 0;
                }
                $byCohort[$hireMonth]['terminations'][$monthDiff]++;
            } else {
                $byCohort[$hireMonth]['active']++;
            }
        }

        ksort($byCohort);
        $cohortMonths = array_keys($byCohort);
        $maxPeriods = 0;

        foreach ($cohortMonths as $cohort) {
            $periods = Carbon::parse($cohort . '-01')->diffInMonths(now()) + 1;
            $maxPeriods = max($maxPeriods, $periods);
        }

        $result = [];
        foreach ($cohortMonths as $cohort) {
            $data = $byCohort[$cohort];
            $row = ['cohort' => $cohort, 'hired' => $data['hired']];
            $remaining = $data['hired'];

            for ($m = 0; $m < $maxPeriods; $m++) {
                $terminated = $data['terminations'][$m] ?? 0;
                $remaining -= $terminated;
                $row['month_' . $m] = $data['hired'] > 0 ? round(($remaining / $data['hired']) * 100, 1) : 0;
            }
            $result[] = $row;
        }

        return [
            'cohorts' => $result,
            'max_periods' => $maxPeriods,
        ];
    }

    public function customerRetentionCohort(int $companyId): array
    {
        $clients = Client::where('company_id', $companyId)
            ->select('id', 'created_at')
            ->get();

        $transactions = PosTransaction::where('company_id', $companyId)
            ->select('id', 'client_id', 'transaction_date')
            ->get()
            ->groupBy('client_id');

        $byCohort = [];
        foreach ($clients as $client) {
            $acqMonth = Carbon::parse($client->created_at)->format('Y-m');
            if (!isset($byCohort[$acqMonth])) {
                $byCohort[$acqMonth] = ['acquired' => 0, 'repeat_months' => []];
            }
            $byCohort[$acqMonth]['acquired']++;

            $clientTxns = $transactions[$client->id] ?? collect();
            $monthsWithTxns = [];
            foreach ($clientTxns as $txn) {
                $txnMonth = Carbon::parse($txn->transaction_date)->format('Y-m');
                if ($txnMonth > $acqMonth) {
                    $monthsWithTxns[$txnMonth] = true;
                }
            }

            foreach (array_keys($monthsWithTxns) as $m) {
                $monthIndex = Carbon::parse($acqMonth . '-01')->diffInMonths(Carbon::parse($m . '-01'));
                if (!isset($byCohort[$acqMonth]['repeat_months'][$monthIndex])) {
                    $byCohort[$acqMonth]['repeat_months'][$monthIndex] = 0;
                }
                $byCohort[$acqMonth]['repeat_months'][$monthIndex]++;
            }
        }

        ksort($byCohort);
        $cohortMonths = array_keys($byCohort);
        $maxPeriods = count($cohortMonths) > 0
            ? Carbon::parse($cohortMonths[0] . '-01')->diffInMonths(now()) + 1
            : 0;

        $result = [];
        foreach ($cohortMonths as $cohort) {
            $data = $byCohort[$cohort];
            $row = ['cohort' => $cohort, 'acquired' => $data['acquired']];

            for ($m = 1; $m <= $maxPeriods; $m++) {
                $repeat = $data['repeat_months'][$m] ?? 0;
                $row['month_' . $m] = $data['acquired'] > 0 ? round(($repeat / $data['acquired']) * 100, 1) : 0;
            }
            $result[] = $row;
        }

        return [
            'cohorts' => $result,
            'max_periods' => $maxPeriods,
        ];
    }

    public function recruitmentFunnel(string $period = 'month'): array
    {
        $dateQuery = $this->getPeriodQuery($period);

        $jobPosted = \App\Models\JobPosting::when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $applied = Candidate::when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $screened = Candidate::where('status', '!=', 'applied')->when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $interviewed = \App\Models\Interview::when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $offered = Candidate::where('status', 'offered')->when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $hired = Candidate::where('status', 'hired')->when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();

        return [
            'job_posted' => $jobPosted,
            'applied' => $applied,
            'screened' => $screened,
            'interviewed' => $interviewed,
            'offered' => $offered,
            'hired' => $hired,
            'stages' => [
                ['name' => 'Lowongan', 'count' => $jobPosted],
                ['name' => 'Melamar', 'count' => $applied],
                ['name' => 'Screening', 'count' => $screened],
                ['name' => 'Interview', 'count' => $interviewed],
                ['name' => 'Offering', 'count' => $offered],
                ['name' => 'Diterima', 'count' => $hired],
            ],
            'conversion_rates' => [
                'apply_rate' => $jobPosted > 0 ? round(($applied / $jobPosted) * 100, 1) : 0,
                'screen_rate' => $applied > 0 ? round(($screened / $applied) * 100, 1) : 0,
                'interview_rate' => $screened > 0 ? round(($interviewed / $screened) * 100, 1) : 0,
                'offer_rate' => $interviewed > 0 ? round(($offered / $interviewed) * 100, 1) : 0,
                'hire_rate' => $offered > 0 ? round(($hired / $offered) * 100, 1) : 0,
                'overall_rate' => $applied > 0 ? round(($hired / $applied) * 100, 1) : 0,
            ],
        ];
    }

    public function salesFunnel(string $period = 'month'): array
    {
        $dateQuery = $this->getPeriodQuery($period);

        $leadsGenerated = Lead::when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $qualified = Lead::whereIn('status', ['qualified', 'hot'])->when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $proposalSent = Lead::where('status', 'proposal')->when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $negotiation = Lead::where('status', 'negotiation')->when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $won = Deal::where('status', 'won')->when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $lost = Deal::where('status', 'lost')->when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();

        return [
            'leads_generated' => $leadsGenerated,
            'qualified' => $qualified,
            'proposal_sent' => $proposalSent,
            'negotiation' => $negotiation,
            'won' => $won,
            'lost' => $lost,
            'stages' => [
                ['name' => 'Lead', 'count' => $leadsGenerated],
                ['name' => 'Kualifikasi', 'count' => $qualified],
                ['name' => 'Proposal', 'count' => $proposalSent],
                ['name' => 'Negosiasi', 'count' => $negotiation],
                ['name' => 'Won', 'count' => $won],
                ['name' => 'Lost', 'count' => $lost],
            ],
            'conversion_rates' => [
                'qualification_rate' => $leadsGenerated > 0 ? round(($qualified / $leadsGenerated) * 100, 1) : 0,
                'proposal_rate' => $qualified > 0 ? round(($proposalSent / $qualified) * 100, 1) : 0,
                'negotiation_rate' => $proposalSent > 0 ? round(($negotiation / $proposalSent) * 100, 1) : 0,
                'win_rate' => ($won + $lost) > 0 ? round(($won / ($won + $lost)) * 100, 1) : 0,
            ],
        ];
    }

    public function purchaseFunnel(string $period = 'month'): array
    {
        $dateQuery = $this->getPeriodQuery($period);

        $prCreated = PurchaseRequisition::when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $poCreated = PurchaseOrder::when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $grnCompleted = GoodsReceipt::when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $invoiceReceived = Invoice::where('invoice_type', 'purchase')->when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();
        $paid = Invoice::where('invoice_type', 'purchase')->where('status', 'paid')->when($dateQuery['start'], fn($q) => $q->where('created_at', '>=', $dateQuery['start']))->count();

        return [
            'pr_created' => $prCreated,
            'po_created' => $poCreated,
            'grn_completed' => $grnCompleted,
            'invoice_received' => $invoiceReceived,
            'paid' => $paid,
            'stages' => [
                ['name' => 'PR', 'count' => $prCreated],
                ['name' => 'PO', 'count' => $poCreated],
                ['name' => 'GRN', 'count' => $grnCompleted],
                ['name' => 'Invoice', 'count' => $invoiceReceived],
                ['name' => 'Lunas', 'count' => $paid],
            ],
            'conversion_rates' => [
                'pr_to_po' => $prCreated > 0 ? round(($poCreated / $prCreated) * 100, 1) : 0,
                'po_to_grn' => $poCreated > 0 ? round(($grnCompleted / $poCreated) * 100, 1) : 0,
                'grn_to_invoice' => $grnCompleted > 0 ? round(($invoiceReceived / $grnCompleted) * 100, 1) : 0,
                'invoice_to_paid' => $invoiceReceived > 0 ? round(($paid / $invoiceReceived) * 100, 1) : 0,
            ],
        ];
    }

    public function calculateRfm(int $companyId): array
    {
        $now = now();
        $transactions = PosTransaction::where('company_id', $companyId)
            ->whereIn('status', ['completed', 'paid'])
            ->get();

        $clientData = [];
        foreach ($transactions as $txn) {
            $cid = $txn->client_id ?? 0;
            if ($cid === 0) continue;
            if (!isset($clientData[$cid])) {
                $clientData[$cid] = ['recent' => null, 'frequency' => 0, 'monetary' => 0];
            }
            $date = Carbon::parse($txn->transaction_date);
            if (!$clientData[$cid]['recent'] || $date->gt($clientData[$cid]['recent'])) {
                $clientData[$cid]['recent'] = $date;
            }
            $clientData[$cid]['frequency']++;
            $clientData[$cid]['monetary'] += $txn->grand_total;
        }

        if (empty($clientData)) return [];

        $recencies = array_map(fn($d) => $d['recent']->diffInDays($now), $clientData);
        $frequencies = array_column($clientData, 'frequency');
        $monetaries = array_column($clientData, 'monetary');

        $rBounds = $this->quantileBounds($recencies, 5);
        $fBounds = $this->quantileBounds($frequencies, 5);
        $mBounds = $this->quantileBounds($monetaries, 5);

        $result = [];
        $clients = Client::whereIn('id', array_keys($clientData))->get()->keyBy('id');

        foreach ($clientData as $cid => $data) {
            $recencyDays = (int) $data['recent']->diffInDays($now);
            $rScore = 5 - $this->getScore($recencyDays, $rBounds);
            $fScore = $this->getScore($data['frequency'], $fBounds);
            $mScore = $this->getScore($data['monetary'], $mBounds);
            $segment = $this->classifyRfmSegment($rScore, $fScore, $mScore);

            $client = $clients[$cid] ?? null;
            $result[] = [
                'client_id' => $cid,
                'client_name' => $client ? ($client->name ?? $client->company_name ?? 'Unknown') : 'Unknown',
                'recency_days' => $recencyDays,
                'frequency' => $data['frequency'],
                'monetary_total' => round($data['monetary']),
                'r_score' => $rScore,
                'f_score' => $fScore,
                'm_score' => $mScore,
                'rfm_score' => $rScore + $fScore + $mScore,
                'segment' => $segment,
            ];
        }

        usort($result, fn($a, $b) => $b['rfm_score'] <=> $a['rfm_score']);

        return [
            'data' => $result,
            'segment_summary' => $this->summarizeSegments($result),
        ];
    }

    protected function classifyRfmSegment(int $r, int $f, int $m): string
    {
        $total = $r + $f + $m;
        if ($total >= 13) return 'Champions';
        if ($total >= 10) return 'Loyal';
        if ($total >= 7) return 'Potential';
        if ($r <= 2 && $f >= 3) return 'At Risk';
        if ($f <= 1 && $r >= 4) return 'New';
        if ($total <= 4) return 'Lost';
        return 'Potential';
    }

    protected function summarizeSegments(array $data): array
    {
        $summary = [];
        foreach ($data as $row) {
            $seg = $row['segment'];
            if (!isset($summary[$seg])) {
                $summary[$seg] = ['count' => 0, 'total_revenue' => 0];
            }
            $summary[$seg]['count']++;
            $summary[$seg]['total_revenue'] += $row['monetary_total'];
        }
        $result = [];
        foreach ($summary as $seg => $s) {
            $result[] = ['segment' => $seg, 'count' => $s['count'], 'total_revenue' => round($s['total_revenue'])];
        }
        return $result;
    }

    protected function quantileBounds(array $values, int $bins): array
    {
        sort($values);
        $n = count($values);
        if ($n < $bins) {
            $step = max($values) / $bins;
            return array_map(fn($i) => $i * $step, range(1, $bins - 1));
        }

        $bounds = [];
        for ($i = 1; $i < $bins; $i++) {
            $idx = (int) floor(($i / $bins) * $n);
            $bounds[] = $values[max(0, min($n - 1, $idx))];
        }
        return $bounds;
    }

    protected function getScore(float $value, array $bounds): int
    {
        foreach ($bounds as $i => $bound) {
            if ($value <= $bound) return $i + 1;
        }
        return count($bounds) + 1;
    }

    public function simulateSalaryIncrease(float $increasePercent, int $companyId): array
    {
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->where('basic_salary', '>', 0)
            ->get();

        $currentTotal = $employees->sum('basic_salary');
        $newTotal = 0;
        $pph21Impact = 0;

        foreach ($employees as $emp) {
            $newSalary = $emp->basic_salary * (1 + $increasePercent / 100);
            $newTotal += $newSalary;

            $oldTaxable = max(0, ($emp->basic_salary * 12) - 54_000_000);
            $newTaxable = max(0, ($newSalary * 12) - 54_000_000);

            $oldTax = $this->estimatePph21($oldTaxable);
            $newTax = $this->estimatePph21($newTaxable);
            $pph21Impact += ($newTax - $oldTax);
        }

        $increaseAmount = $newTotal - $currentTotal;

        $revenue = PosTransaction::where('company_id', $companyId)
            ->where('transaction_date', '>=', now()->subMonths(12))
            ->sum('grand_total');

        $impactPercent = $revenue > 0 ? round(($increaseAmount / ($revenue / 12)) * 100, 2) : 0;

        return [
            'current_payroll_total' => round($currentTotal),
            'new_payroll_total' => round($newTotal),
            'increase_amount' => round($increaseAmount),
            'increase_percent' => $increasePercent,
            'affected_employees' => $employees->count(),
            'avg_current_salary' => $employees->count() > 0 ? round($currentTotal / $employees->count()) : 0,
            'avg_new_salary' => $employees->count() > 0 ? round($newTotal / $employees->count()) : 0,
            'pph21_impact' => round($pph21Impact),
            'impact_on_net_profit_percent' => $impactPercent,
            'monthly_budget_increase' => round($increaseAmount),
            'annual_budget_increase' => round($increaseAmount * 12),
        ];
    }

    protected function estimatePph21(float $taxableIncome): float
    {
        $tax = 0;
        if ($taxableIncome <= 0) return 0;

        $brackets = [
            [0, 60_000_000, 0.05],
            [60_000_000, 250_000_000, 0.15],
            [250_000_000, 500_000_000, 0.25],
            [500_000_000, 5_000_000_000, 0.30],
            [5_000_000_000, PHP_INT_MAX, 0.35],
        ];

        foreach ($brackets as [$low, $high, $rate]) {
            if ($taxableIncome <= 0) break;
            $slice = min($taxableIncome, $high - $low);
            if ($slice > 0) {
                $tax += $slice * $rate;
                $taxableIncome -= $slice;
            }
        }

        return $tax / 12;
    }

    public function simulateNewMachine(int $workCenterId, float $additionalCapacity): array
    {
        $wc = WorkCenter::findOrFail($workCenterId);
        $currentCapacity = $wc->capacity_per_hour ?? 1;

        $newTotalCapacity = $currentCapacity + $additionalCapacity;
        $additionalDaily = $additionalCapacity * 8;
        $additionalMonthly = $additionalDaily * 22;
        $additionalYearly = $additionalMonthly * 12;

        $avgRevenuePerUnit = PosTransaction::avg('grand_total') ?? 100000;

        $additionalRevenueMonthly = $additionalMonthly * ($avgRevenuePerUnit * 0.3);
        $estimatedInvestment = $additionalCapacity * 5_000_000;
        $roiMonths = $additionalRevenueMonthly > 0 ? ceil($estimatedInvestment / $additionalRevenueMonthly) : 999;
        $breakEvenUnits = $additionalRevenueMonthly > 0 ? ceil($estimatedInvestment / ($avgRevenuePerUnit * 0.3)) : 999999;

        return [
            'work_center' => $wc->name ?? 'WC-' . $workCenterId,
            'current_capacity_per_hour' => $currentCapacity,
            'new_capacity_per_hour' => $newTotalCapacity,
            'capacity_increase_percent' => round(($additionalCapacity / max(1, $currentCapacity)) * 100, 1),
            'additional_daily_production' => round($additionalDaily),
            'additional_monthly_production' => round($additionalMonthly),
            'additional_yearly_production' => round($additionalYearly),
            'estimated_investment' => $estimatedInvestment,
            'estimated_monthly_revenue' => round($additionalRevenueMonthly),
            'roi_months' => $roiMonths,
            'break_even_units' => $breakEvenUnits,
        ];
    }

    public function simulatePriceChange(int $productId, float $newPrice): array
    {
        $product = Product::findOrFail($productId);
        $currentPrice = $product->selling_price ?? ($product->price ?? 0);

        $currentVolume = PosTransaction::whereHas('items', fn($q) => $q->where('product_id', $productId))
            ->where('transaction_date', '>=', now()->subMonths(12))
            ->count();

        $currentRevenue = $currentVolume * $currentPrice;
        $estimatedNewVolume = $currentVolume / (1 + max(0, ($newPrice - $currentPrice) / max(1, $currentPrice)));
        $newRevenue = $estimatedNewVolume * $newPrice;
        $revenueChange = $newRevenue - $currentRevenue;

        $cost = $product->purchase_price ?? ($product->cost_price ?? ($currentPrice * 0.6));
        $currentProfit = $currentVolume * ($currentPrice - $cost);
        $newProfit = $estimatedNewVolume * ($newPrice - $cost);
        $profitChange = $newProfit - $currentProfit;

        $breakEvenVolume = $newPrice > 0 ? ceil($currentProfit / ($newPrice - $cost)) : 0;

        return [
            'product_name' => $product->name,
            'current_price' => $currentPrice,
            'new_price' => $newPrice,
            'price_change_percent' => $currentPrice > 0 ? round((($newPrice - $currentPrice) / $currentPrice) * 100, 1) : 0,
            'current_monthly_volume' => round($currentVolume / 12),
            'estimated_monthly_volume' => round($estimatedNewVolume / 12),
            'volume_change_percent' => $currentVolume > 0 ? round((($estimatedNewVolume - $currentVolume) / $currentVolume) * 100, 1) : 0,
            'current_revenue' => round($currentRevenue),
            'new_revenue' => round($newRevenue),
            'revenue_change' => round($revenueChange),
            'current_profit' => round($currentProfit),
            'new_profit' => round($newProfit),
            'profit_change' => round($profitChange),
            'current_margin_percent' => $currentPrice > 0 ? round((($currentPrice - $cost) / $currentPrice) * 100, 1) : 0,
            'new_margin_percent' => $newPrice > 0 ? round((($newPrice - $cost) / $newPrice) * 100, 1) : 0,
            'break_even_volume' => $breakEvenVolume,
        ];
    }

    public function findCorrelations(int $companyId): array
    {
        $correlations = [];
        $employees = Employee::where('company_id', $companyId)->where('status', 'active')->count();

        $overtimeData = DB::table('overtimes')
            ->join('employees', 'overtimes.employee_id', '=', 'employees.id')
            ->where('employees.company_id', $companyId)
            ->where('employees.status', 'active')
            ->select('overtimes.employee_id', DB::raw('COUNT(*) as overtime_count'))
            ->groupBy('overtimes.employee_id')
            ->pluck('overtime_count', 'employee_id');

        $turnover = Employee::where('company_id', $companyId)
            ->where('status', 'terminated')
            ->whereNotNull('termination_date')
            ->where('termination_date', '>=', now()->subYear())
            ->count();

        if ($employees > 0) {
            $avgOvertime = $overtimeData->avg();
            $turnoverRate = round(($turnover / $employees) * 100, 1);
            if ($avgOvertime > 0) {
                $correlations[] = [
                    'title' => 'Jam Lembur vs Turnover Karyawan',
                    'x' => 'Jam Lembur Rata-rata',
                    'y' => 'Turnover Rate',
                    'r' => round($this->pearsonCorrelation($overtimeData->values()->toArray(), $this->turnoverPerEmployee()), 2),
                    'interpretation' => $avgOvertime > 20 ? 'Jam lembur tinggi berkorelasi dengan turnover. Pertimbangkan redistribusi beban kerja.' : 'Jam lembur dalam batas wajar.',
                    'strength' => abs($avgOvertime) > 0.5 ? 'kuat' : 'sedang',
                ];
            }

            $trainingHours = DB::table('course_enrollments')
                ->join('employees', 'course_enrollments.user_id', '=', 'employees.id')
                ->where('employees.company_id', $companyId)
                ->where('course_enrollments.status', 'completed')
                ->select('course_enrollments.user_id', DB::raw('COUNT(*) as courses_completed'))
                ->groupBy('course_enrollments.user_id')
                ->pluck('courses_completed', 'user_id');

            $dealsByUser = Deal::where('company_id', $companyId)
                ->where('status', 'won')
                ->select('assigned_to', DB::raw('COUNT(*) as deal_count'))
                ->groupBy('assigned_to')
                ->pluck('deal_count', 'assigned_to');

            if ($trainingHours->count() > 2 && $dealsByUser->count() > 2) {
                $trainingValues = [];
                $dealValues = [];
                foreach ($trainingHours as $userId => $courses) {
                    $trainingValues[] = $courses;
                    $dealValues[] = $dealsByUser[$userId] ?? 0;
                }
                if (count($trainingValues) > 1) {
                    $correlations[] = [
                        'title' => 'Pelatihan vs Performa Penjualan',
                        'x' => 'Kursus Diselesaikan',
                        'y' => 'Deal Dimenangkan',
                        'r' => round($this->pearsonCorrelation($trainingValues, $dealValues), 2),
                        'interpretation' => 'Investasi pelatihan menunjukkan hubungan positif dengan performa penjualan.',
                        'strength' => 'sedang',
                    ];
                }
            }

            $attendanceData = DB::table('attendances')
                ->join('employees', 'attendances.employee_id', '=', 'employees.id')
                ->where('employees.company_id', $companyId)
                ->where('employees.status', 'active')
                ->where('attendances.date', '>=', now()->subMonths(3))
                ->select('attendances.employee_id', DB::raw('COUNT(*) as attendance_days'))
                ->groupBy('attendances.employee_id')
                ->pluck('attendance_days', 'employee_id');

            $performanceData = DB::table('performance_review_scores')
                ->join('performance_reviews', 'performance_review_scores.performance_review_id', '=', 'performance_reviews.id')
                ->where('performance_reviews.created_at', '>=', now()->subYear())
                ->select('performance_reviews.employee_id', DB::raw('AVG(performance_review_scores.score) as avg_score'))
                ->groupBy('performance_reviews.employee_id')
                ->pluck('avg_score', 'employee_id');

            if ($attendanceData->count() > 3 && $performanceData->count() > 3) {
                $attendanceValues = [];
                $perfValues = [];
                foreach ($attendanceData as $empId => $days) {
                    if (isset($performanceData[$empId])) {
                        $attendanceValues[] = $days;
                        $perfValues[] = $performanceData[$empId];
                    }
                }
                if (count($attendanceValues) > 3) {
                    $correlations[] = [
                        'title' => 'Kehadiran vs Skor Kinerja',
                        'x' => 'Hari Hadir (3 bulan)',
                        'y' => 'Rata-rata Skor Kinerja',
                        'r' => round($this->pearsonCorrelation($attendanceValues, $perfValues), 2),
                        'interpretation' => 'Ada hubungan positif antara tingkat kehadiran dengan skor kinerja.',
                        'strength' => 'kuat',
                    ];
                }
            }
        }

        return [
            'correlations' => $correlations,
            'sample_size' => $employees,
            'period' => '12 bulan terakhir',
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    protected function turnoverPerEmployee(): array
    {
        return []; // Placeholder - simplified
    }

    protected function pearsonCorrelation(array $x, array $y): float
    {
        $n = min(count($x), count($y));
        if ($n < 2) return 0;

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;
        $sumY2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
            $sumY2 += $y[$i] * $y[$i];
        }

        $numerator = ($n * $sumXY) - ($sumX * $sumY);
        $denom = sqrt(($n * $sumX2 - $sumX * $sumX) * ($n * $sumY2 - $sumY * $sumY));

        return $denom != 0 ? round($numerator / $denom, 3) : 0;
    }

    public function detectTrend(string $metric, array $historicalData): array
    {
        $values = array_values($historicalData);
        $n = count($values);

        if ($n < 4) {
            return [
                'trend_direction' => 'insufficient_data',
                'trend_strength' => 0,
                'seasonality_pattern' => 'unknown',
                'forecast_next_4_periods' => [],
                'confidence' => 0,
            ];
        }

        $x = range(1, $n);
        $slope = $this->linearRegressionSlope($x, $values);

        $direction = 'flat';
        if ($slope > 0.5) $direction = 'naik';
        elseif ($slope < -0.5) $direction = 'turun';

        $yHat = array_map(fn($xi) => $this->predictLinear($xi, $x, $values), $x);
        $r2 = $this->rSquared($values, $yHat);

        $seasonal = $this->detectSeasonality($values);

        $forecast = [];
        for ($i = $n + 1; $i <= $n + 4; $i++) {
            $forecast[] = round($this->predictLinear($i, $x, $values), 2);
        }

        $sd = $this->standardDeviation($values);

        return [
            'trend_direction' => $direction,
            'trend_strength' => $r2 > 0.7 ? 'kuat' : ($r2 > 0.4 ? 'sedang' : 'lemah'),
            'slope' => round($slope, 4),
            'r_squared' => round($r2, 4),
            'seasonality_pattern' => $seasonal ? 'terdeteksi (periode ' . $seasonal . ')' : 'tidak terdeteksi',
            'forecast_next_4_periods' => $forecast,
            'confidence' => round(min(95, $r2 * 100), 1),
            'volatility' => round($sd / max(1, array_sum($values) / $n) * 100, 1),
        ];
    }

    protected function linearRegressionSlope(array $x, array $y): float
    {
        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
        }

        $denom = ($n * $sumX2) - ($sumX * $sumX);
        if ($denom == 0) return 0;

        return (($n * $sumXY) - ($sumX * $sumY)) / $denom;
    }

    protected function predictLinear(float $xi, array $x, array $y): float
    {
        $slope = $this->linearRegressionSlope($x, $y);
        $intercept = (array_sum($y) / count($y)) - ($slope * (array_sum($x) / count($x)));
        return $slope * $xi + $intercept;
    }

    protected function rSquared(array $actual, array $predicted): float
    {
        $n = count($actual);
        $meanActual = array_sum($actual) / $n;

        $ssRes = 0;
        $ssTot = 0;
        for ($i = 0; $i < $n; $i++) {
            $ssRes += pow($actual[$i] - $predicted[$i], 2);
            $ssTot += pow($actual[$i] - $meanActual, 2);
        }

        return $ssTot > 0 ? max(0, 1 - ($ssRes / $ssTot)) : 0;
    }

    protected function detectSeasonality(array $values): int
    {
        $n = count($values);
        if ($n < 8) return 0;

        $bestPeriod = 0;
        $bestCorr = 0;

        for ($period = 2; $period <= min(12, floor($n / 2)); $period++) {
            $acc = 0;
            $count = 0;
            for ($i = $period; $i < $n; $i++) {
                $acc += pow($values[$i] - $values[$i - $period], 2);
                $count++;
            }
            if ($count > 0) {
                $mse = $acc / $count;
                $corr = 1 / (1 + $mse);
                if ($corr > $bestCorr) {
                    $bestCorr = $corr;
                    $bestPeriod = $period;
                }
            }
        }

        return $bestPeriod > 0 && $bestCorr > 0.3 ? $bestPeriod : 0;
    }

    protected function standardDeviation(array $values): float
    {
        $n = count($values);
        if ($n < 2) return 0;
        $mean = array_sum($values) / $n;
        $variance = array_sum(array_map(fn($v) => pow($v - $mean, 2), $values)) / ($n - 1);
        return sqrt($variance);
    }

    public function streamAnomalyCheck(string $module, array $data): array
    {
        $value = $data['value'] ?? 0;
        $cacheKey = "anomaly_stream_{$module}";
        $cached = cache()->get($cacheKey, ['ema' => null, 'ema_sq' => null, 'count' => 0, 'mean' => 0, 'variance' => 0]);

        $count = $cached['count'] + 1;
        $alpha = 2.0 / (min($count, 30) + 1);

        if ($cached['ema'] === null) {
            $cached['ema'] = $value;
            $cached['ema_sq'] = $value * $value;
        } else {
            $cached['ema'] = $alpha * $value + (1 - $alpha) * $cached['ema'];
            $cached['ema_sq'] = $alpha * ($value * $value) + (1 - $alpha) * $cached['ema_sq'];
        }

        $ema = $cached['ema'];
        $emaVariance = $cached['ema_sq'] - ($ema * $ema);
        $stdDev = sqrt(max(0.0001, $emaVariance));

        $zScore = ($value - $ema) / max(0.0001, $stdDev);
        $threshold = $count < 5 ? 3.5 : 3.0;

        $severity = 'normal';
        if (abs($zScore) > 4) $severity = 'critical';
        elseif (abs($zScore) > 3) $severity = 'high';
        elseif (abs($zScore) > 2) $severity = 'medium';
        elseif (abs($zScore) > 1.5) $severity = 'low';

        $cached['count'] = $count;
        cache()->put($cacheKey, $cached, now()->addDays(7));

        return [
            'is_anomaly' => abs($zScore) > $threshold,
            'z_score' => round($zScore, 4),
            'threshold' => $threshold,
            'severity' => abs($zScore) > $threshold ? $severity : 'normal',
            'current_value' => $value,
            'expected_value' => round($ema, 2),
            'ema' => round($ema, 2),
            'std_dev' => round($stdDev, 4),
            'module' => $module,
            'sample_count' => $count,
        ];
    }

    protected function getPeriodQuery(string $period): array
    {
        return match ($period) {
            'week' => ['start' => now()->startOfWeek(), 'end' => now()->endOfWeek()],
            'month' => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()],
            'quarter' => ['start' => now()->startOfQuarter(), 'end' => now()->endOfQuarter()],
            'year' => ['start' => now()->startOfYear(), 'end' => now()->endOfYear()],
            default => ['start' => now()->startOfMonth(), 'end' => now()->endOfMonth()],
        };
    }
}
