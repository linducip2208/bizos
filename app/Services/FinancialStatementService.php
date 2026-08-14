<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinancialStatementService
{
    protected const DEBIT_NORMAL = ['ASET', 'BEBAN'];
    protected const CREDIT_NORMAL = ['LIAB', 'EKUITAS', 'PENDAPATAN'];

    /**
     * Generate a balance sheet (Neraca) as of a given date.
     *
     * @param array{company_id?: int, branch_id?: int|null, as_of_date?: string} $filters
     */
    public function generateBalanceSheet(array $filters): array
    {
        $companyId = $filters['company_id'] ?? auth()->user()->company_id;
        $branchId = $filters['branch_id'] ?? null;
        $asOfDate = $filters['as_of_date'] ?? now()->format('Y-m-d');

        $balances = $this->balancesAsOf($companyId, $branchId, $asOfDate);

        $assets = ['current' => [], 'non_current' => []];
        $liabilities = ['current' => [], 'non_current' => []];
        $equity = [];

        foreach ($balances as $row) {
            $categoryCode = $row['category_code'];
            $balance = round((float) $row['balance'], 2);

            if ($categoryCode === 'ASET') {
                $key = $this->classify($row['code'], 'ASET') === 'non_current' ? 'non_current' : 'current';
                $assets[$key][] = $this->lineItem($row, $balance);
            } elseif ($categoryCode === 'LIAB') {
                $key = $this->classify($row['code'], 'LIAB') === 'non_current' ? 'non_current' : 'current';
                $liabilities[$key][] = $this->lineItem($row, $balance);
            } elseif ($categoryCode === 'EKUITAS') {
                // Exclude "Laba Tahun Berjalan" (computed below) to avoid double counting.
                if ($this->isCurrentYearProfitAccount($row['code'], $row['name'])) {
                    continue;
                }
                $equity[] = $this->lineItem($row, $balance);
            }
        }

        $totalAssetsCurrent = $this->sumBalances($assets['current']);
        $totalAssetsNonCurrent = $this->sumBalances($assets['non_current']);
        $totalAssets = $totalAssetsCurrent + $totalAssetsNonCurrent;

        $totalLiabilitiesCurrent = $this->sumBalances($liabilities['current']);
        $totalLiabilitiesNonCurrent = $this->sumBalances($liabilities['non_current']);
        $totalLiabilities = $totalLiabilitiesCurrent + $totalLiabilitiesNonCurrent;

        $netProfit = $this->netProfitAsOf($companyId, $branchId, $asOfDate);
        $equity[] = [
            'code' => '',
            'name' => $netProfit >= 0 ? 'Laba Tahun Berjalan' : 'Rugi Tahun Berjalan',
            'balance' => round($netProfit, 2),
            'is_computed' => true,
        ];

        $totalEquity = $this->sumBalances($equity);

        return [
            'as_of_date' => $asOfDate,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets_current' => round($totalAssetsCurrent, 2),
            'total_assets_non_current' => round($totalAssetsNonCurrent, 2),
            'total_assets' => round($totalAssets, 2),
            'total_liabilities_current' => round($totalLiabilitiesCurrent, 2),
            'total_liabilities_non_current' => round($totalLiabilitiesNonCurrent, 2),
            'total_liabilities' => round($totalLiabilities, 2),
            'total_equity' => round($totalEquity, 2),
            'total_liabilities_and_equity' => round($totalLiabilities + $totalEquity, 2),
            'net_profit' => round($netProfit, 2),
        ];
    }

    /**
     * Generate a trial balance (Neraca Saldo) for a date range.
     *
     * @param array{company_id?: int, branch_id?: int|null, date_from?: string, date_to?: string} $filters
     */
    public function generateTrialBalance(array $filters): array
    {
        $companyId = $filters['company_id'] ?? auth()->user()->company_id;
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? now()->startOfYear()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? now()->format('Y-m-d');

        $coaList = Coa::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('is_header', false)
            ->with('category')
            ->orderBy('code')
            ->get();

        $beforeMovements = $this->movementsByCoa(
            $companyId,
            $branchId,
            null,
            Carbon::parse($dateFrom)->subDay()->toDateString()
        );

        $periodMovements = $this->movementsByCoa($companyId, $branchId, $dateFrom, $dateTo);

        $accounts = [];
        $groups = [];
        $totals = [
            'opening_debit' => 0.0,
            'opening_credit' => 0.0,
            'movement_debit' => 0.0,
            'movement_credit' => 0.0,
            'closing_debit' => 0.0,
            'closing_credit' => 0.0,
        ];

        foreach ($coaList as $coa) {
            $categoryCode = strtoupper($coa->category?->code ?? '');
            $isDebitNormal = in_array($categoryCode, self::DEBIT_NORMAL, true);

            $before = $beforeMovements[$coa->id] ?? null;
            $beforeDebit = (float) ($before->total_debit ?? 0);
            $beforeCredit = (float) ($before->total_credit ?? 0);

            $period = $periodMovements[$coa->id] ?? null;
            $movementDebit = (float) ($period->total_debit ?? 0);
            $movementCredit = (float) ($period->total_credit ?? 0);

            // Signed opening balance: initial Coa.opening_balance + movements before the period.
            $openingSigned = (float) $coa->opening_balance
                + $this->netMovement($beforeDebit, $beforeCredit, $isDebitNormal);

            [$openingDebit, $openingCredit] = $this->splitSigned($openingSigned, $isDebitNormal);

            // Signed closing balance.
            $closingSigned = $openingSigned
                + $this->netMovement($movementDebit, $movementCredit, $isDebitNormal);

            [$closingDebit, $closingCredit] = $this->splitSigned($closingSigned, $isDebitNormal);

            $row = [
                'coa_id' => $coa->id,
                'code' => $coa->code,
                'name' => $coa->name,
                'category_code' => $categoryCode,
                'category_name' => $coa->category?->name ?? '',
                'normal_balance' => $isDebitNormal ? 'debit' : 'credit',
                'opening_debit' => round($openingDebit, 2),
                'opening_credit' => round($openingCredit, 2),
                'movement_debit' => round($movementDebit, 2),
                'movement_credit' => round($movementCredit, 2),
                'closing_debit' => round($closingDebit, 2),
                'closing_credit' => round($closingCredit, 2),
            ];

            $accounts[] = $row;

            $groupName = $categoryCode ?: 'LAINNYA';
            $groups[$groupName]['accounts'][] = $row;
            $groups[$groupName]['label'] = $coa->category?->name ?? $groupName;
            $groups[$groupName]['code'] = $groupName;

            $totals['opening_debit'] += $openingDebit;
            $totals['opening_credit'] += $openingCredit;
            $totals['movement_debit'] += $movementDebit;
            $totals['movement_credit'] += $movementCredit;
            $totals['closing_debit'] += $closingDebit;
            $totals['closing_credit'] += $closingCredit;
        }

        $orderedGroups = [];
        foreach (['ASET', 'LIAB', 'EKUITAS', 'PENDAPATAN', 'BEBAN'] as $code) {
            if (isset($groups[$code])) {
                $orderedGroups[$code] = $groups[$code];
            }
        }

        $totals = array_map(fn ($v) => round((float) $v, 2), $totals);

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'accounts' => $accounts,
            'groups' => $orderedGroups,
            'totals' => $totals,
            'balanced' => abs($totals['closing_debit'] - $totals['closing_credit']) < 0.01,
        ];
    }

    /**
     * Generate an income statement (Laba Rugi) for a date range.
     *
     * @param array{company_id?: int, branch_id?: int|null, date_from?: string, date_to?: string} $filters
     */
    public function generateIncomeStatement(array $filters): array
    {
        $companyId = $filters['company_id'] ?? auth()->user()->company_id;
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? now()->startOfYear()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? now()->format('Y-m-d');

        $coaList = Coa::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('is_header', false)
            ->whereHas('category', fn ($q) => $q->whereIn('code', ['PENDAPATAN', 'BEBAN']))
            ->with('category')
            ->orderBy('code')
            ->get();

        $movements = $this->movementsByCoa($companyId, $branchId, $dateFrom, $dateTo);

        $revenue = [];
        $expenses = [];
        $totalRevenue = 0.0;
        $totalExpenses = 0.0;

        foreach ($coaList as $coa) {
            $categoryCode = strtoupper($coa->category?->code ?? '');
            $period = $movements[$coa->id] ?? null;
            $debit = (float) ($period->total_debit ?? 0);
            $credit = (float) ($period->total_credit ?? 0);

            // Revenue is credit-normal: net = credit - debit.
            // Expense is debit-normal: net = debit - credit.
            $amount = $categoryCode === 'PENDAPATAN'
                ? $credit - $debit
                : $debit - $credit;

            if (abs($amount) < 0.001) {
                continue;
            }

            $line = [
                'code' => $coa->code,
                'name' => $coa->name,
                'amount' => round($amount, 2),
            ];

            if ($categoryCode === 'PENDAPATAN') {
                $revenue[] = $line;
                $totalRevenue += $amount;
            } else {
                $expenses[] = $line;
                $totalExpenses += $amount;
            }
        }

        $netProfit = $totalRevenue - $totalExpenses;

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'total_revenue' => round($totalRevenue, 2),
            'total_expenses' => round($totalExpenses, 2),
            'net_profit' => round($netProfit, 2),
        ];
    }

    /**
     * Generate a cash flow statement (Arus Kas) for a date range (indirect method).
     *
     * @param array{company_id?: int, branch_id?: int|null, date_from?: string, date_to?: string} $filters
     */
    public function generateCashFlowStatement(array $filters): array
    {
        $companyId = $filters['company_id'] ?? auth()->user()->company_id;
        $branchId = $filters['branch_id'] ?? null;
        $dateFrom = $filters['date_from'] ?? now()->startOfYear()->format('Y-m-d');
        $dateTo = $filters['date_to'] ?? now()->format('Y-m-d');

        $startDate = Carbon::parse($dateFrom)->subDay()->toDateString();
        $balancesStart = $this->balancesAsOf($companyId, $branchId, $startDate);
        $balancesEnd = $this->balancesAsOf($companyId, $branchId, $dateTo);

        $income = $this->generateIncomeStatement([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]);

        $netProfit = $income['net_profit'];

        // Non-cash items (depreciation) within the period.
        $depreciation = $this->sumExpenseMovements($companyId, $branchId, $dateFrom, $dateTo, ['penyusutan', 'amortisasi']);

        $changeCurrentAssets = $this->changeInGroup($balancesStart, $balancesEnd, 'ASET', 'current');
        $changeNonCurrentAssets = $this->changeInGroup($balancesStart, $balancesEnd, 'ASET', 'non_current');
        $changeCurrentLiabilities = $this->changeInGroup($balancesStart, $balancesEnd, 'LIAB', 'current');
        $changeNonCurrentLiabilities = $this->changeInGroup($balancesStart, $balancesEnd, 'LIAB', 'non_current');
        $changeEquity = $this->changeInGroup($balancesStart, $balancesEnd, 'EKUITAS', 'other');

        $operating = [
            ['label' => 'Laba (Rugi) Bersih', 'amount' => round($netProfit, 2)],
            ['label' => 'Beban Penyusutan & Amortisasi', 'amount' => round($depreciation, 2)],
            ['label' => 'Perubahan Aset Lancar', 'amount' => round(-$changeCurrentAssets, 2)],
            ['label' => 'Perubahan Kewajiban Lancar', 'amount' => round($changeCurrentLiabilities, 2)],
        ];

        $investing = [
            ['label' => 'Perubahan Aset Tetap', 'amount' => round(-$changeNonCurrentAssets, 2)],
        ];

        $financing = [
            ['label' => 'Perubahan Kewajiban Jangka Panjang', 'amount' => round($changeNonCurrentLiabilities, 2)],
            ['label' => 'Perubahan Ekuitas', 'amount' => round($changeEquity, 2)],
        ];

        $operatingTotal = round($netProfit + $depreciation - $changeCurrentAssets + $changeCurrentLiabilities, 2);
        $investingTotal = round(-$changeNonCurrentAssets, 2);
        $financingTotal = round($changeNonCurrentLiabilities + $changeEquity, 2);
        $netCashChange = round($operatingTotal + $investingTotal + $financingTotal, 2);

        $cashBeginning = $this->cashBalance($balancesStart);
        $cashEnding = $this->cashBalance($balancesEnd);

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'operating' => $operating,
            'investing' => $investing,
            'financing' => $financing,
            'operating_total' => $operatingTotal,
            'investing_total' => $investingTotal,
            'financing_total' => $financingTotal,
            'net_cash_change' => $netCashChange,
            'cash_beginning' => round($cashBeginning, 2),
            'cash_ending' => round($cashEnding, 2),
        ];
    }

    /**
     * Compute signed balances per COA as of a date.
     * Positive = normal-balance direction (asset/expense debit, liability/equity/revenue credit).
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function balancesAsOf(int $companyId, ?int $branchId, string $asOfDate): Collection
    {
        $coaList = Coa::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('is_header', false)
            ->with('category')
            ->orderBy('code')
            ->get();

        $movements = $this->movementsByCoa($companyId, $branchId, null, $asOfDate);

        return $coaList->map(function (Coa $coa) use ($movements) {
            $categoryCode = strtoupper($coa->category?->code ?? '');
            $isDebitNormal = in_array($categoryCode, self::DEBIT_NORMAL, true);

            $mv = $movements[$coa->id] ?? null;
            $debit = (float) ($mv->total_debit ?? 0);
            $credit = (float) ($mv->total_credit ?? 0);

            $balance = (float) $coa->opening_balance
                + $this->netMovement($debit, $credit, $isDebitNormal);

            return [
                'coa_id' => $coa->id,
                'code' => $coa->code,
                'name' => $coa->name,
                'category_code' => $categoryCode,
                'category_name' => $coa->category?->name ?? '',
                'balance' => $balance,
            ];
        })->values();
    }

    protected function netProfitAsOf(int $companyId, ?int $branchId, string $asOfDate): float
    {
        $income = $this->generateIncomeStatement([
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'date_from' => '1970-01-01',
            'date_to' => $asOfDate,
        ]);

        return $income['net_profit'];
    }

    protected function movementsByCoa(int $companyId, ?int $branchId, ?string $from, ?string $to): Collection
    {
        return JournalEntry::query()
            ->selectRaw('coa_id, SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->whereHas('journal', function ($q) use ($companyId, $branchId, $from, $to) {
                $q->where('company_id', $companyId)
                    ->where('status', 'posted');

                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }

                if ($from) {
                    $q->where('journal_date', '>=', $from);
                }

                if ($to) {
                    $q->where('journal_date', '<=', $to);
                }
            })
            ->groupBy('coa_id')
            ->get()
            ->keyBy('coa_id');
    }

    protected function netMovement(float $debit, float $credit, bool $isDebitNormal): float
    {
        return $isDebitNormal ? $debit - $credit : $credit - $debit;
    }

    /**
     * @return array{0: float, 1: float}
     */
    protected function splitSigned(float $signed, bool $isDebitNormal): array
    {
        if ($isDebitNormal) {
            return $signed >= 0 ? [$signed, 0.0] : [0.0, abs($signed)];
        }

        return $signed >= 0 ? [0.0, $signed] : [abs($signed), 0.0];
    }

    protected function classify(string $code, string $categoryCode): string
    {
        $parts = explode('-', $code);
        $sub = (int) ($parts[1] ?? 0);

        if ($categoryCode === 'ASET') {
            return $sub >= 5000 ? 'non_current' : 'current';
        }

        if ($categoryCode === 'LIAB') {
            return $sub >= 4000 ? 'non_current' : 'current';
        }

        return 'other';
    }

    protected function isCurrentYearProfitAccount(string $code, string $name): bool
    {
        $haystack = mb_strtolower($code . ' ' . $name);

        return str_contains($haystack, 'tahun berjalan')
            || str_contains($haystack, 'laba berjalan')
            || str_contains($haystack, 'rugi berjalan');
    }

    protected function lineItem(array $row, float $balance): array
    {
        return [
            'code' => $row['code'],
            'name' => $row['name'],
            'balance' => round($balance, 2),
        ];
    }

    protected function sumBalances(array $items): float
    {
        return array_sum(array_map(fn ($i) => (float) ($i['balance'] ?? 0), $items));
    }

    protected function changeInGroup(Collection $start, Collection $end, string $categoryCode, string $key): float
    {
        $startGroup = $start->filter(function ($row) use ($categoryCode, $key) {
            return $row['category_code'] === $categoryCode
                && $this->classify($row['code'], $categoryCode) === $key;
        });

        $endGroup = $end->filter(function ($row) use ($categoryCode, $key) {
            return $row['category_code'] === $categoryCode
                && $this->classify($row['code'], $categoryCode) === $key;
        });

        $startTotal = $startGroup->sum(fn ($r) => (float) $r['balance']);
        $endTotal = $endGroup->sum(fn ($r) => (float) $r['balance']);

        return $endTotal - $startTotal;
    }

    protected function cashBalance(Collection $balances): float
    {
        return $balances->filter(function ($row) {
            if ($row['category_code'] !== 'ASET') {
                return false;
            }

            $name = mb_strtolower($row['name']);
            $code = $row['code'];
            $sub = (int) (explode('-', $code)[1] ?? 0);

            return $sub >= 1000 && $sub < 2000
                || str_contains($name, 'kas')
                || str_contains($name, 'bank');
        })->sum(fn ($r) => (float) $r['balance']);
    }

    protected function sumExpenseMovements(int $companyId, ?int $branchId, string $from, string $to, array $keywords): float
    {
        $expenseCoaIds = Coa::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where('is_header', false)
            ->whereHas('category', fn ($q) => $q->where('code', 'BEBAN'))
            ->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%");
                }
            })
            ->pluck('id');

        if ($expenseCoaIds->isEmpty()) {
            return 0.0;
        }

        return (float) JournalEntry::query()
            ->whereIn('coa_id', $expenseCoaIds)
            ->whereHas('journal', function ($q) use ($companyId, $branchId, $from, $to) {
                $q->where('company_id', $companyId)
                    ->where('status', 'posted')
                    ->whereBetween('journal_date', [$from, $to]);

                if ($branchId) {
                    $q->where('branch_id', $branchId);
                }
            })
            ->sum('debit');
    }
}
