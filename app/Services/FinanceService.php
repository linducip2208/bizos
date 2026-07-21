<?php

namespace App\Services;

use App\Models\ClosingPeriod;
use App\Models\Coa;
use App\Models\Company;
use App\Models\FinancialConsolidation;
use App\Models\Invoice;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\OpeningBalance;
use App\Models\TrialBalance;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinanceService
{
    public function closePeriod(string $year, string $month, int $companyId): void
    {
        DB::transaction(function () use ($year, $month, $companyId) {
            $period = ClosingPeriod::where('company_id', $companyId)
                ->where('year', $year)
                ->where('month', $month)
                ->first();

            if (!$period) {
                $period = ClosingPeriod::create([
                    'company_id' => $companyId,
                    'year' => $year,
                    'month' => $month,
                    'status' => 'in_progress',
                ]);
            }

            if ($period->isClosed()) {
                throw new \Exception('Periode ' . $year . '-' . $month . ' sudah ditutup.');
            }

            $period->update(['status' => 'in_progress']);

            $this->generateTrialBalance($year, $month, $companyId);

            $nextMonth = Carbon::createFromDate($year, $month, 1)->addMonth();
            $nextYear = $nextMonth->format('Y');
            $nextMonthNum = $nextMonth->format('m');

            $trialBalances = TrialBalance::where('company_id', $companyId)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->get();

            foreach ($trialBalances as $tb) {
                OpeningBalance::updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'coa_id' => $tb->coa_id,
                        'period_year' => $nextYear,
                        'period_month' => $nextMonthNum,
                    ],
                    [
                        'debit_amount' => $tb->closing_debit,
                        'credit_amount' => $tb->closing_credit,
                    ]
                );
            }

            $period->update([
                'status' => 'closed',
                'closed_by' => auth()->id(),
                'closed_at' => now(),
            ]);
        });
    }

    public function generateTrialBalance(string $year, string $month, int $companyId): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $coaList = Coa::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('is_header', false)
            ->get();

        $result = [];

        foreach ($coaList as $coa) {
            $opening = OpeningBalance::where('company_id', $companyId)
                ->where('coa_id', $coa->id)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->first();

            $openingDebit = $opening?->debit_amount ?? 0;
            $openingCredit = $opening?->credit_amount ?? 0;

            $movements = JournalEntry::whereHas('journal', function ($q) use ($companyId, $startDate, $endDate) {
                $q->where('company_id', $companyId)
                    ->whereBetween('journal_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->where('status', 'posted');
            })->where('coa_id', $coa->id)->get();

            $movementDebit = $movements->sum('debit');
            $movementCredit = $movements->sum('credit');

            $closingDebit = $openingDebit + $movementDebit;
            $closingCredit = $openingCredit + $movementCredit;

            TrialBalance::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'coa_id' => $coa->id,
                    'period_year' => $year,
                    'period_month' => $month,
                ],
                [
                    'opening_debit' => $openingDebit,
                    'opening_credit' => $openingCredit,
                    'movement_debit' => $movementDebit,
                    'movement_credit' => $movementCredit,
                    'closing_debit' => $closingDebit,
                    'closing_credit' => $closingCredit,
                ]
            );

            $result[] = [
                'coa_id' => $coa->id,
                'coa_code' => $coa->code,
                'coa_name' => $coa->name,
                'opening_debit' => (float) $openingDebit,
                'opening_credit' => (float) $openingCredit,
                'movement_debit' => (float) $movementDebit,
                'movement_credit' => (float) $movementCredit,
                'closing_debit' => (float) $closingDebit,
                'closing_credit' => (float) $closingCredit,
            ];
        }

        return $result;
    }

    public function consolidate(int $parentCompanyId, string $year, string $month): void
    {
        DB::transaction(function () use ($parentCompanyId, $year, $month) {
            $childCompanies = Company::where('id', '!=', $parentCompanyId)
                ->where('is_active', true)
                ->get();

            foreach ($childCompanies as $child) {
                FinancialConsolidation::updateOrCreate(
                    [
                        'parent_company_id' => $parentCompanyId,
                        'child_company_id' => $child->id,
                        'period_year' => $year,
                        'period_month' => $month,
                        'consolidation_type' => 'balance_sheet',
                    ],
                    [
                        'status' => 'processed',
                        'mapping_config' => $this->buildMappingConfig($parentCompanyId, $child->id),
                    ]
                );

                FinancialConsolidation::updateOrCreate(
                    [
                        'parent_company_id' => $parentCompanyId,
                        'child_company_id' => $child->id,
                        'period_year' => $year,
                        'period_month' => $month,
                        'consolidation_type' => 'income_statement',
                    ],
                    [
                        'status' => 'processed',
                        'mapping_config' => $this->buildMappingConfig($parentCompanyId, $child->id),
                    ]
                );
            }
        });
    }

    private function buildMappingConfig(int $parentCompanyId, int $childCompanyId): array
    {
        $parentCoa = Coa::where('company_id', $parentCompanyId)
            ->where('is_active', true)
            ->pluck('name', 'code');

        $childCoa = Coa::where('company_id', $childCompanyId)
            ->where('is_active', true)
            ->pluck('name', 'code');

        $mapping = [];
        foreach ($childCoa as $code => $name) {
            if ($parentCoa->has($code)) {
                $mapping[$code] = [
                    'from' => $name,
                    'to' => $parentCoa[$code],
                    'matched' => true,
                ];
            } else {
                $mapping[$code] = [
                    'from' => $name,
                    'to' => null,
                    'matched' => false,
                ];
            }
        }

        return $mapping;
    }

    public function setOpeningBalance(int $coaId, float $debit, float $credit): void
    {
        $coa = Coa::findOrFail($coaId);
        $now = now();

        OpeningBalance::updateOrCreate(
            [
                'company_id' => $coa->company_id,
                'coa_id' => $coaId,
                'period_year' => $now->format('Y'),
                'period_month' => $now->format('m'),
            ],
            [
                'debit_amount' => $debit,
                'credit_amount' => $credit,
            ]
        );
    }

    public function getFinancialRatios(int $companyId, string $year, string $month): array
    {
        $trialBalances = TrialBalance::where('company_id', $companyId)
            ->where('period_year', $year)
            ->where('period_month', $month)
            ->with('coa')
            ->get();

        $totalAsetLancar = $this->sumByCoaType($trialBalances, 'aset-lancar');
        $totalAsetTetap = $this->sumByCoaType($trialBalances, 'aset-tetap');
        $totalAset = $totalAsetLancar + $totalAsetTetap;

        $totalLiabilitasJkPendek = $this->sumByCoaType($trialBalances, 'liabilitas-jangka-pendek');
        $totalLiabilitasJkPanjang = $this->sumByCoaType($trialBalances, 'liabilitas-jangka-panjang');
        $totalLiabilitas = $totalLiabilitasJkPendek + $totalLiabilitasJkPanjang;

        $totalEkuitas = $totalAset - $totalLiabilitas;

        $totalPendapatan = $this->sumByCoaType($trialBalances, 'pendapatan', 'movement_credit');
        $totalBeban = $this->sumByCoaType($trialBalances, 'beban', 'movement_debit');

        $labaBersih = $totalPendapatan - $totalBeban;

        $persediaan = $this->sumByCoaType($trialBalances, 'persediaan');
        $kasSetaraKas = $this->sumByCoaType($trialBalances, 'kas');

        $currentRatio = $totalLiabilitasJkPendek > 0 ? round($totalAsetLancar / $totalLiabilitasJkPendek, 2) : 0;
        $debtToEquityRatio = $totalEkuitas > 0 ? round($totalLiabilitas / $totalEkuitas, 2) : 0;
        $netProfitMargin = $totalPendapatan > 0 ? round(($labaBersih / $totalPendapatan) * 100, 2) : 0;
        $roa = $totalAset > 0 ? round(($labaBersih / $totalAset) * 100, 2) : 0;
        $roe = $totalEkuitas > 0 ? round(($labaBersih / $totalEkuitas) * 100, 2) : 0;
        $quickRatio = $totalLiabilitasJkPendek > 0 ? round((($totalAsetLancar - $persediaan) / $totalLiabilitasJkPendek), 2) : 0;
        $debtToAssetRatio = $totalAset > 0 ? round(($totalLiabilitas / $totalAset) * 100, 2) : 0;

        return [
            'periode' => $year . '-' . $month,
            'current_ratio' => $currentRatio,
            'quick_ratio' => $quickRatio,
            'debt_to_equity_ratio' => $debtToEquityRatio,
            'debt_to_asset_ratio' => $debtToAssetRatio,
            'net_profit_margin' => $netProfitMargin,
            'roa' => $roa,
            'roe' => $roe,
            'ringkasan' => [
                'total_aset_lancar' => (float) $totalAsetLancar,
                'total_aset_tetap' => (float) $totalAsetTetap,
                'total_aset' => (float) $totalAset,
                'total_liabilitas_jk_pendek' => (float) $totalLiabilitasJkPendek,
                'total_liabilitas_jk_panjang' => (float) $totalLiabilitasJkPanjang,
                'total_liabilitas' => (float) $totalLiabilitas,
                'total_ekuitas' => (float) $totalEkuitas,
                'total_pendapatan' => (float) $totalPendapatan,
                'total_beban' => (float) $totalBeban,
                'laba_bersih' => (float) $labaBersih,
                'kas_setara_kas' => (float) $kasSetaraKas,
                'persediaan' => (float) $persediaan,
            ],
        ];
    }

    private function sumByCoaType(Collection $trialBalances, string $typeKeyword, string $field = 'closing_debit'): float
    {
        return $trialBalances->filter(function ($tb) use ($typeKeyword) {
            if (!$tb->coa) return false;
            $categoryName = strtolower($tb->coa->category?->name ?? '');
            return str_contains($categoryName, $typeKeyword);
        })->sum(function ($tb) use ($field) {
            return $tb->$field;
        });
    }

    public function getPeriodStatus(int $companyId, string $year, string $month): ?string
    {
        $period = ClosingPeriod::where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        return $period?->status;
    }

    public function reopenPeriod(string $year, string $month, int $companyId): void
    {
        $period = ClosingPeriod::where('company_id', $companyId)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if (!$period || !$period->isClosed()) {
            throw new \Exception('Periode ' . $year . '-' . $month . ' tidak dalam status tertutup.');
        }

        $period->update([
            'status' => 'open',
            'closed_by' => null,
            'closed_at' => null,
        ]);
    }
}
