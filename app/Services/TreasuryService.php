<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankFacility;
use App\Models\BankFacilityCovenant;
use App\Models\BankFacilityDrawdown;
use App\Models\BankTransfer;
use App\Models\Company;
use App\Models\Currency;
use App\Models\ExchangeRateLog;
use App\Models\ForexRateSnapshot;
use App\Models\Investment;
use App\Models\InvestmentTransaction;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Coa;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TreasuryService
{
    public function getCashPosition(int $companyId): array
    {
        $accounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->with('currency')
            ->get();

        $totalIdr = 0;
        $accountDetails = [];
        $currencyTotals = [];

        foreach ($accounts as $account) {
            $rate = $account->currency?->exchange_rate ?? 1;
            $balanceIdr = $account->current_balance * $rate;

            $accountDetails[] = [
                'id' => $account->id,
                'bank_name' => $account->bank_name,
                'account_number' => $account->account_number,
                'account_name' => $account->account_name,
                'currency' => $account->currency?->code ?? 'IDR',
                'balance' => (float) $account->current_balance,
                'balance_formatted' => 'Rp ' . number_format($account->current_balance, 2, ',', '.'),
                'balance_idr' => round($balanceIdr, 2),
                'balance_idr_formatted' => 'Rp ' . number_format($balanceIdr, 2, ',', '.'),
            ];

            $currencyCode = $account->currency?->code ?? 'IDR';
            $currencyTotals[$currencyCode] = ($currencyTotals[$currencyCode] ?? 0) + (float) $account->current_balance;
            $totalIdr += $balanceIdr;
        }

        $balanceByCurrency = [];
        foreach ($currencyTotals as $code => $total) {
            $rate = Currency::where('code', $code)->first()?->exchange_rate ?? 1;
            $balanceByCurrency[] = [
                'currency' => $code,
                'total' => round($total, 2),
                'rate' => (float) $rate,
                'total_idr' => round($total * $rate, 2),
                'total_formatted' => number_format($total, 2, ',', '.'),
            ];
        }

        return [
            'accounts' => $accountDetails,
            'account_count' => $accounts->count(),
            'total_idr' => round($totalIdr, 2),
            'total_formatted' => 'Rp ' . number_format($totalIdr, 2, ',', '.'),
            'by_currency' => $balanceByCurrency,
            'as_of' => now()->format('Y-m-d H:i'),
        ];
    }

    public function suggestCashPooling(int $companyId): array
    {
        $accounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->with('currency')
            ->get();

        if ($accounts->count() < 2) {
            return ['pooling_possible' => false, 'message' => 'Minimal butuh 2 rekening untuk cash pooling'];
        }

        $idrAccounts = $accounts->filter(fn($a) => ($a->currency->code ?? 'IDR') === 'IDR');
        if ($idrAccounts->count() < 2) {
            return ['pooling_possible' => false, 'message' => 'Butuh minimal 2 rekening IDR'];
        }

        $totalBalance = $idrAccounts->sum('current_balance');
        $targetPerAccount = $totalBalance / $idrAccounts->count();

        $minimumBuffer = 5000000; // Rp 5 juta

        $excesses = [];
        $deficits = [];

        foreach ($idrAccounts as $account) {
            $targetWithBuffer = max($targetPerAccount, $minimumBuffer);

            if ($account->current_balance > $targetWithBuffer * 1.5) {
                $excess = $account->current_balance - $targetWithBuffer;
                $excesses[] = [
                    'account_id' => $account->id,
                    'account_name' => $account->account_name,
                    'account_number' => $account->account_number,
                    'current_balance' => (float) $account->current_balance,
                    'target_balance' => round($targetWithBuffer, 2),
                    'excess' => round($excess, 2),
                    'excess_formatted' => 'Rp ' . number_format($excess, 2, ',', '.'),
                ];
            } elseif ($account->current_balance < $targetWithBuffer * 0.5) {
                $deficit = $targetWithBuffer - $account->current_balance;
                $deficits[] = [
                    'account_id' => $account->id,
                    'account_name' => $account->account_name,
                    'account_number' => $account->account_number,
                    'current_balance' => (float) $account->current_balance,
                    'target_balance' => round($targetWithBuffer, 2),
                    'deficit' => round($deficit, 2),
                    'deficit_formatted' => 'Rp ' . number_format($deficit, 2, ',', '.'),
                ];
            }
        }

        $totalExcess = array_sum(array_column($excesses, 'excess'));
        $totalDeficit = array_sum(array_column($deficits, 'deficit'));

        $instructions = [];
        if ($totalExcess > 0 && count($deficits) > 0) {
            $availableExcess = $totalExcess;

            foreach ($deficits as &$deficit) {
                $amount = min($deficit['deficit'], $availableExcess);
                if ($amount <= 0) break;

                $transferAmount = floor($amount / 100000) * 100000;

                if ($transferAmount >= 100000) {
                    foreach ($excesses as &$excess) {
                        if ($excess['excess'] >= $transferAmount) {
                            $instructions[] = [
                                'from_account_id' => $excess['account_id'],
                                'from_account_name' => $excess['account_name'],
                                'to_account_id' => $deficit['account_id'],
                                'to_account_name' => $deficit['account_name'],
                                'amount' => $transferAmount,
                                'amount_formatted' => 'Rp ' . number_format($transferAmount, 2, ',', '.'),
                                'reason' => 'Cash pooling: transfer dari surplus ke defisit',
                            ];
                            $excess['excess'] -= $transferAmount;
                            $availableExcess -= $transferAmount;
                            $deficit['deficit'] -= $transferAmount;
                            break;
                        }
                    }
                }
            }
        }

        return [
            'pooling_possible' => true,
            'total_balance' => (float) $totalBalance,
            'target_per_account' => round($targetPerAccount, 2),
            'total_excess' => round($totalExcess, 2),
            'total_deficit' => round($totalDeficit, 2),
            'excess_accounts' => $excesses,
            'deficit_accounts' => $deficits,
            'transfer_instructions' => $instructions,
            'instruction_count' => count($instructions),
            'message' => $instructions
                ? count($instructions) . ' transfer disarankan untuk cash pooling'
                : 'Tidak ada pooling yang diperlukan',
        ];
    }

    public function executeCashPooling(int $companyId, array $instructions): array
    {
        $results = [];

        foreach ($instructions as $instruction) {
            $transfer = BankTransfer::create([
                'company_id' => $companyId,
                'from_account_id' => $instruction['from_account_id'],
                'to_account_id' => $instruction['to_account_id'],
                'transfer_date' => now()->format('Y-m-d'),
                'amount' => $instruction['amount'],
                'notes' => $instruction['reason'] ?? 'Cash pooling transfer',
                'status' => 'completed',
                'created_by' => auth()->id(),
            ]);

            // Update balances
            BankAccount::where('id', $instruction['from_account_id'])->decrement('current_balance', $instruction['amount']);
            BankAccount::where('id', $instruction['to_account_id'])->increment('current_balance', $instruction['amount']);

            $results[] = [
                'transfer_id' => $transfer->id,
                'status' => 'completed',
                'amount' => $instruction['amount'],
            ];
        }

        return [
            'executed' => count($results),
            'results' => $results,
        ];
    }

    public function getForexExposure(int $companyId): array
    {
        $currencies = Currency::where('is_base', false)->active()->get();
        $baseCurrency = Currency::base()->first();

        if (!$baseCurrency) {
            return ['exposures' => [], 'message' => 'Mata uang basis tidak ditemukan'];
        }

        $exposures = [];
        $totalNetExposureIdr = 0;

        foreach ($currencies as $currency) {
            $rate = $currency->exchange_rate;

            // AR in foreign currency
            $arAmount = Invoice::where('company_id', $companyId)
                ->where('currency_id', $currency->id)
                ->whereIn('status', ['sent', 'partial', 'overdue'])
                ->sum('balance_due');

            // AP in foreign currency
            $apAmount = Invoice::where('company_id', $companyId)
                ->where('currency_id', $currency->id)
                ->where('type', 'payable')
                ->whereIn('status', ['sent', 'partial', 'overdue'])
                ->sum('balance_due');

            $netExposure = $arAmount - $apAmount;
            $netExposureIdr = $netExposure * $rate;

            // Check for unrealized gain/loss
            $lastSnapshot = ForexRateSnapshot::latestForCurrency($currency->id)->first();
            $originalRate = $lastSnapshot ? $lastSnapshot->mid_rate : $rate;
            $unrealizedGainLoss = $netExposure > 0
                ? ($rate - $originalRate) * $netExposure
                : ($originalRate - $rate) * abs($netExposure);

            $exposures[] = [
                'currency' => $currency->code,
                'currency_name' => $currency->name,
                'current_rate' => (float) $rate,
                'ar_amount' => round($arAmount, 2),
                'ap_amount' => round($apAmount, 2),
                'net_exposure' => round($netExposure, 2),
                'net_exposure_idr' => round($netExposureIdr, 2),
                'net_exposure_formatted' => 'Rp ' . number_format($netExposureIdr, 2, ',', '.'),
                'unrealized_gain_loss' => round($unrealizedGainLoss, 2),
                'exposure_direction' => $netExposure > 0 ? 'long' : ($netExposure < 0 ? 'short' : 'balanced'),
            ];

            $totalNetExposureIdr += $netExposureIdr;
        }

        return [
            'base_currency' => $baseCurrency->code,
            'exposures' => $exposures,
            'total_net_exposure_idr' => round($totalNetExposureIdr, 2),
            'total_net_exposure_formatted' => 'Rp ' . number_format($totalNetExposureIdr, 2, ',', '.'),
            'as_of' => now()->format('Y-m-d'),
        ];
    }

    public function suggestHedging(int $companyId): array
    {
        $exposure = $this->getForexExposure($companyId);
        $suggestions = [];

        foreach ($exposure['exposures'] as $exp) {
            $absExposureIdr = abs($exp['net_exposure_idr']);

            if ($absExposureIdr > 100000000) { // Above 100 juta IDR
                $suggestions[] = [
                    'currency' => $exp['currency'],
                    'net_exposure' => $exp['net_exposure'],
                    'net_exposure_idr' => $exp['net_exposure_idr'],
                    'direction' => $exp['exposure_direction'],
                    'suggested_action' => $exp['exposure_direction'] === 'long'
                        ? 'Pertimbangkan forward contract jual ' . $exp['currency']
                        : 'Pertimbangkan forward contract beli ' . $exp['currency'],
                    'hedge_amount_suggested' => round($absExposureIdr * 0.8, 2),
                    'hedge_percent_suggested' => 80,
                    'reason' => 'Eksposur di atas threshold Rp 100 juta',
                ];
            }
        }

        return [
            'suggestions' => $suggestions,
            'suggestion_count' => count($suggestions),
            'threshold_idr' => 100000000,
            'message' => $suggestions
                ? count($suggestions) . ' mata uang disarankan untuk hedging'
                : 'Eksposur forex dalam batas aman',
        ];
    }

    public function createInvestment(array $data): Investment
    {
        $principal = $data['principal_amount'] ?? 0;
        $rate = $data['interest_rate_percent'] ?? 0;
        $startDate = Carbon::parse($data['start_date']);

        $data['current_value'] = $data['current_value'] ?? $principal;
        $data['next_interest_date'] = $data['next_interest_date'] ?? $startDate->copy()->addMonths(1)->format('Y-m-d');
        $data['created_by'] = auth()->id();

        return Investment::create($data);
    }

    public function accrueInterest(Investment $investment): ?JournalEntry
    {
        if ($investment->status !== 'active') return null;
        if ($investment->interest_rate_percent <= 0) return null;

        $lastTransaction = $investment->transactions()
            ->where('type', 'interest_income')
            ->orderByDesc('transaction_date')
            ->first();

        $lastAccrualDate = $lastTransaction
            ? Carbon::parse($lastTransaction->transaction_date)
            : Carbon::parse($investment->start_date);

        $now = now();
        if ($lastAccrualDate->isSameDay($now)) return null;

        if ($investment->interest_type === 'fixed') {
            $daysElapsed = $lastAccrualDate->diffInDays($now);
            if ($daysElapsed <= 0) return null;

            $dailyRate = $investment->interest_rate_percent / 100 / 365;
            $accruedAmount = $investment->principal_amount * $dailyRate * $daysElapsed;

            if ($accruedAmount < 0.01) return null;

            $accruedAmount = round($accruedAmount, 2);

            $transaction = InvestmentTransaction::create([
                'company_id' => $investment->company_id,
                'investment_id' => $investment->id,
                'type' => 'interest_income',
                'transaction_date' => $now->format('Y-m-d'),
                'amount' => $accruedAmount,
                'currency_id' => $investment->currency_id,
                'notes' => "Akrual bunga {$daysElapsed} hari @ {$investment->interest_rate_percent}% p.a.",
                'created_by' => auth()->id(),
            ]);

            $investment->update([
                'total_accrued_interest' => $investment->total_accrued_interest + $accruedAmount,
                'total_interest_earned' => $investment->total_interest_earned + $accruedAmount,
                'current_value' => $investment->principal_amount + $investment->total_interest_earned,
                'next_interest_date' => $investment->next_interest_date
                    ? Carbon::parse($investment->next_interest_date)->addMonth()->format('Y-m-d')
                    : $now->copy()->addMonth()->format('Y-m-d'),
            ]);

            $interestReceivableCoa = Coa::where('code', '1-1300')->first();
            $interestIncomeCoa = Coa::where('code', '4-2000')->first();

            if ($interestReceivableCoa && $interestIncomeCoa) {
                $journal = Journal::create([
                    'company_id' => $investment->company_id,
                    'journal_date' => $now->format('Y-m-d'),
                    'reference' => "BUNGA-{$investment->id}-{$now->format('Ymd')}",
                    'description' => "Akrual bunga investasi: {$investment->name}",
                    'journal_type' => 'general',
                    'status' => 'posted',
                    'created_by' => auth()->id(),
                ]);

                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $interestReceivableCoa->id,
                    'debit' => $accruedAmount,
                    'credit' => 0,
                    'description' => 'Piutang bunga investasi',
                ]);

                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $interestIncomeCoa->id,
                    'debit' => 0,
                    'credit' => $accruedAmount,
                    'description' => 'Pendapatan bunga investasi',
                ]);

                $transaction->update(['journal_entry_id' => $journal->entries()->first()->id]);

                return $journal->entries()->first();
            }
        }

        return null;
    }

    public function getMaturitySchedule(int $companyId): array
    {
        $now = now();

        $next30 = Investment::where('company_id', $companyId)
            ->active()
            ->where('maturity_date', '<=', $now->copy()->addDays(30))
            ->where('maturity_date', '>=', $now)
            ->orderBy('maturity_date')
            ->get();

        $next60 = Investment::where('company_id', $companyId)
            ->active()
            ->where('maturity_date', '>', $now->copy()->addDays(30))
            ->where('maturity_date', '<=', $now->copy()->addDays(60))
            ->get();

        $next90 = Investment::where('company_id', $companyId)
            ->active()
            ->where('maturity_date', '>', $now->copy()->addDays(60))
            ->where('maturity_date', '<=', $now->copy()->addDays(90))
            ->get();

        $pastDue = Investment::where('company_id', $companyId)
            ->active()
            ->where('maturity_date', '<', $now)
            ->orderBy('maturity_date')
            ->get();

        $formatItems = function ($items) {
            return $items->map(fn($inv) => [
                'id' => $inv->id,
                'name' => $inv->name,
                'type' => $inv->type,
                'principal' => (float) $inv->principal_amount,
                'principal_formatted' => 'Rp ' . number_format($inv->principal_amount, 2, ',', '.'),
                'current_value' => (float) $inv->current_value,
                'interest_rate' => (float) $inv->interest_rate_percent,
                'maturity_date' => $inv->maturity_date->format('Y-m-d'),
                'days_to_maturity' => $inv->getDaysToMaturity(),
                'status' => $inv->status,
            ])->toArray();
        };

        return [
            'next_30_days' => $formatItems($next30),
            'next_60_days' => $formatItems($next60),
            'next_90_days' => $formatItems($next90),
            'past_due' => $formatItems($pastDue),
            'total_maturing_30' => $next30->sum('principal_amount'),
            'total_maturing_90' => $next30->sum('principal_amount') + $next60->sum('principal_amount') + $next90->sum('principal_amount'),
        ];
    }

    public function getInvestmentPortfolio(int $companyId): array
    {
        $investments = Investment::where('company_id', $companyId)
            ->with('currency')
            ->get();

        $activeInvestments = $investments->where('status', 'active');
        $totalInvested = $activeInvestments->sum('principal_amount');
        $totalAccruedInterest = $activeInvestments->sum('total_accrued_interest');
        $totalCurrentValue = $activeInvestments->sum('current_value');

        $allocationByType = [];
        foreach ($activeInvestments->groupBy('type') as $type => $group) {
            $typeTotal = $group->sum('current_value');
            $allocationByType[] = [
                'type' => $type,
                'count' => $group->count(),
                'total_value' => (float) $typeTotal,
                'total_formatted' => 'Rp ' . number_format($typeTotal, 2, ',', '.'),
                'percent' => $totalCurrentValue > 0 ? round(($typeTotal / $totalCurrentValue) * 100, 1) : 0,
            ];
        }

        $ytdStart = now()->startOfYear();
        $ytdReturn = InvestmentTransaction::where('company_id', $companyId)
            ->whereIn('type', ['interest_income', 'dividend'])
            ->where('transaction_date', '>=', $ytdStart->format('Y-m-d'))
            ->sum('amount');

        $averageRate = $activeInvestments->isNotEmpty()
            ? $activeInvestments->avg('interest_rate_percent')
            : 0;

        $weightedReturn = 0;
        if ($totalInvested > 0) {
            foreach ($activeInvestments as $inv) {
                $weight = $inv->principal_amount / $totalInvested;
                $weightedReturn += $weight * $inv->interest_rate_percent;
            }
        }

        return [
            'total_invested' => (float) $totalInvested,
            'total_invested_formatted' => 'Rp ' . number_format($totalInvested, 2, ',', '.'),
            'total_accrued_interest' => (float) $totalAccruedInterest,
            'total_accrued_interest_formatted' => 'Rp ' . number_format($totalAccruedInterest, 2, ',', '.'),
            'total_current_value' => (float) $totalCurrentValue,
            'total_current_value_formatted' => 'Rp ' . number_format($totalCurrentValue, 2, ',', '.'),
            'ytd_return' => (float) $ytdReturn,
            'ytd_return_formatted' => 'Rp ' . number_format($ytdReturn, 2, ',', '.'),
            'average_rate' => round($averageRate, 2),
            'weighted_return' => round($weightedReturn, 2),
            'allocation_by_type' => $allocationByType,
            'active_count' => $activeInvestments->count(),
            'total_count' => $investments->count(),
        ];
    }

    public function createBankFacility(array $data): BankFacility
    {
        $data['available_amount'] = $data['available_amount'] ?? $data['limit_amount'];
        $data['utilized_amount'] = $data['utilized_amount'] ?? 0;
        $data['created_by'] = auth()->id();

        return BankFacility::create($data);
    }

    public function getFacilityUtilization(BankFacility $facility): array
    {
        $drawdowns = $facility->drawdowns()->outstanding()->get();
        $totalOutstanding = $drawdowns->sum('outstanding_amount');

        $facility->updateQuietly([
            'utilized_amount' => $totalOutstanding,
            'available_amount' => $facility->limit_amount - $totalOutstanding,
        ]);

        return [
            'facility' => [
                'id' => $facility->id,
                'name' => $facility->name,
                'type' => $facility->facility_type,
                'bank' => $facility->bank_name,
                'limit' => (float) $facility->limit_amount,
                'limit_formatted' => 'Rp ' . number_format($facility->limit_amount, 2, ',', '.'),
            ],
            'utilized' => round($totalOutstanding, 2),
            'utilized_formatted' => 'Rp ' . number_format($totalOutstanding, 2, ',', '.'),
            'available' => round($facility->limit_amount - $totalOutstanding, 2),
            'available_formatted' => 'Rp ' . number_format($facility->limit_amount - $totalOutstanding, 2, ',', '.'),
            'utilization_percent' => $facility->getUtilizationPercent(),
            'drawdown_count' => $drawdowns->count(),
            'expiry_date' => $facility->expiry_date->format('Y-m-d'),
            'days_to_expiry' => $facility->getDaysToExpiry(),
        ];
    }

    public function checkCovenantCompliance(BankFacility $facility): array
    {
        $covenants = $facility->covenants;
        $results = [];
        $allCompliant = true;

        foreach ($covenants as $covenant) {
            // Try to compute actual values based on metric
            $actual = $covenant->actual_value;

            if (empty($actual)) {
                $actual = $this->computeCovenantMetric($facility->company_id, $covenant->metric);
                $covenant->update(['actual_value' => $actual, 'last_tested_at' => now()->format('Y-m-d')]);
            }

            $compliant = $this->evaluateCovenantCompliance($covenant->metric, $actual, $covenant->requirement);

            if (!$compliant) {
                $allCompliant = false;
            }

            $covenant->update([
                'is_compliant' => $compliant,
                'status' => $compliant ? 'compliant' : 'breach',
                'last_tested_at' => now()->format('Y-m-d'),
            ]);

            $results[] = [
                'name' => $covenant->name,
                'metric' => $covenant->metric,
                'requirement' => $covenant->requirement,
                'actual' => $actual,
                'compliant' => $compliant,
            ];
        }

        return [
            'covenants' => $results,
            'overall_compliant' => $allCompliant,
            'breach_count' => count(array_filter($results, fn($r) => !$r['compliant'])),
            'tested_at' => now()->format('Y-m-d'),
        ];
    }

    private function computeCovenantMetric(int $companyId, string $metric): string
    {
        switch ($metric) {
            case 'current_ratio':
                $currentAssets = Coa::where('company_id', $companyId)
                    ->where('type', 'asset')
                    ->where('category', 'current_asset')
                    ->sum('balance');
                $currentLiabilities = Coa::where('company_id', $companyId)
                    ->where('type', 'liability')
                    ->where('category', 'current_liability')
                    ->sum('balance');
                return $currentLiabilities > 0
                    ? (string) round($currentAssets / $currentLiabilities, 2)
                    : '0';

            case 'debt_to_equity':
                $totalDebt = Coa::where('company_id', $companyId)
                    ->where('type', 'liability')
                    ->sum('balance');
                $totalEquity = Coa::where('company_id', $companyId)
                    ->where('type', 'equity')
                    ->sum('balance');
                return $totalEquity > 0
                    ? (string) round($totalDebt / $totalEquity, 2)
                    : '0';

            case 'debt_service_coverage':
                $ebitda = JournalEntry::whereHas('journal', fn($q) => $q->where('company_id', $companyId))
                    ->whereHas('coa', fn($q) => $q->whereIn('type', ['revenue', 'expense']))
                    ->whereMonth('created_at', now()->month)
                    ->sum(DB::raw('COALESCE(credit, 0) - COALESCE(debit, 0)'));
                $debtPayments = BankFacilityDrawdown::where('company_id', $companyId)
                    ->where('status', 'outstanding')
                    ->sum('outstanding_amount');
                return $debtPayments > 0
                    ? (string) round(abs($ebitda) / ($debtPayments / 12), 2)
                    : '0';

            default:
                return 'N/A';
        }
    }

    private function evaluateCovenantCompliance(string $metric, string $actual, string $requirement): bool
    {
        $actualNum = (float) $actual;
        $reqNum = (float) filter_var($requirement, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if (empty($requirement) || $actual === 'N/A') return true;

        if (str_starts_with($requirement, '>=')) {
            return $actualNum >= $reqNum;
        }
        if (str_starts_with($requirement, '<=')) {
            return $actualNum <= $reqNum;
        }
        if (str_starts_with($requirement, '>')) {
            return $actualNum > $reqNum;
        }
        if (str_starts_with($requirement, '<')) {
            return $actualNum < $reqNum;
        }

        return $actualNum >= $reqNum;
    }

    public function getDailyCashPosition(int $companyId, int $days = 30): array
    {
        $cashPosition = $this->getCashPosition($companyId);
        $openingBalance = $cashPosition['total_idr'] ?? 0;

        $now = now();
        $result = [];
        $runningBalance = $openingBalance;

        // Collect expected inflows and outflows
        $dailyInflows = [];
        $dailyOutflows = [];

        $receivables = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->where('balance_due', '>', 0)
            ->get();

        foreach ($receivables as $inv) {
            $dueDate = $inv->due_date?->format('Y-m-d') ?? $now->copy()->addDays(7)->format('Y-m-d');
            $dailyInflows[$dueDate] = ($dailyInflows[$dueDate] ?? 0) + (float) $inv->balance_due;
        }

        $payables = Invoice::where('company_id', $companyId)
            ->where('type', 'payable')
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->where('balance_due', '>', 0)
            ->get();

        foreach ($payables as $inv) {
            $dueDate = $inv->due_date?->format('Y-m-d') ?? $now->copy()->addDays(14)->format('Y-m-d');
            $dailyOutflows[$dueDate] = ($dailyOutflows[$dueDate] ?? 0) + (float) $inv->balance_due;
        }

        $minimumBalance = 5000000; // Rp 5 juta minimum cash

        for ($i = 0; $i < $days; $i++) {
            $date = $now->copy()->addDays($i)->format('Y-m-d');
            $inflow = $dailyInflows[$date] ?? 0;
            $outflow = $dailyOutflows[$date] ?? 0;

            $opening = $runningBalance;
            $closing = $opening + $inflow - $outflow;

            $alert = null;
            if ($closing < 0) {
                $alert = 'critical';
            } elseif ($closing < $minimumBalance) {
                $alert = 'warning';
            }

            $result[] = [
                'date' => $date,
                'opening_balance' => round($opening, 2),
                'inflows' => round($inflow, 2),
                'outflows' => round($outflow, 2),
                'net_flow' => round($inflow - $outflow, 2),
                'closing_balance' => round($closing, 2),
                'minimum_balance' => $minimumBalance,
                'alert' => $alert,
            ];

            $runningBalance = $closing;
        }

        return [
            'opening_balance' => round($openingBalance, 2),
            'opening_balance_formatted' => 'Rp ' . number_format($openingBalance, 2, ',', '.'),
            'minimum_balance' => $minimumBalance,
            'horizon_days' => $days,
            'daily_positions' => $result,
            'lowest_balance' => round(min(array_column($result, 'closing_balance')), 2),
            'lowest_date' => $this->findMinDate($result),
            'alert_days' => count(array_filter($result, fn($r) => $r['alert'] !== null)),
        ];
    }

    private function findMinDate(array $positions): ?string
    {
        $min = PHP_FLOAT_MAX;
        $minDate = null;
        foreach ($positions as $p) {
            if ($p['closing_balance'] < $min) {
                $min = $p['closing_balance'];
                $minDate = $p['date'];
            }
        }
        return $minDate;
    }

    public function getLiquidityRatios(int $companyId): array
    {
        $currentAssets = $this->getCoaBalance($companyId, 'asset', 'current_asset');
        $totalAssets = $this->getCoaBalance($companyId, 'asset', 'all');
        $currentLiabilities = $this->getCoaBalance($companyId, 'liability', 'current_liability');
        $totalLiabilities = $this->getCoaBalance($companyId, 'liability', 'all');
        $totalEquity = $this->getCoaBalance($companyId, 'equity', 'all');

        $cashAndEquivalents = $this->getCoaBalance($companyId, 'asset', 'cash');
        $accountsReceivable = $this->getCoaBalance($companyId, 'asset', 'receivable');
        $inventory = $this->getCoaBalance($companyId, 'asset', 'inventory');

        $currentRatio = $currentLiabilities > 0 ? round($currentAssets / $currentLiabilities, 2) : null;
        $quickRatio = $currentLiabilities > 0
            ? round(($cashAndEquivalents + $accountsReceivable) / $currentLiabilities, 2)
            : null;
        $cashRatio = $currentLiabilities > 0 ? round($cashAndEquivalents / $currentLiabilities, 2) : null;
        $workingCapital = $currentAssets - $currentLiabilities;
        $debtToEquity = $totalEquity > 0 ? round($totalLiabilities / $totalEquity, 2) : null;
        $debtToAssets = $totalAssets > 0 ? round($totalLiabilities / $totalAssets, 2) : null;

        return [
            'current_ratio' => $currentRatio,
            'current_ratio_formatted' => $currentRatio !== null ? number_format($currentRatio, 2, ',', '.') : 'N/A',
            'current_ratio_interpretation' => $this->interpretRatio('current_ratio', $currentRatio),
            'quick_ratio' => $quickRatio,
            'quick_ratio_formatted' => $quickRatio !== null ? number_format($quickRatio, 2, ',', '.') : 'N/A',
            'quick_ratio_interpretation' => $this->interpretRatio('quick_ratio', $quickRatio),
            'cash_ratio' => $cashRatio,
            'cash_ratio_formatted' => $cashRatio !== null ? number_format($cashRatio, 2, ',', '.') : 'N/A',
            'working_capital' => (float) $workingCapital,
            'working_capital_formatted' => 'Rp ' . number_format($workingCapital, 2, ',', '.'),
            'debt_to_equity' => $debtToEquity,
            'debt_to_equity_formatted' => $debtToEquity !== null ? number_format($debtToEquity, 2, ',', '.') : 'N/A',
            'debt_to_assets' => $debtToAssets,
            'debt_to_assets_formatted' => $debtToAssets !== null ? number_format($debtToAssets, 2, ',', '.') : 'N/A',
            'as_of' => now()->format('Y-m-d'),
        ];
    }

    private function getCoaBalance(int $companyId, string $type, string $category): float
    {
        switch ($category) {
            case 'all':
                $total = DB::table('journal_entries as je')
                    ->join('journals as j', 'je.journal_id', '=', 'j.id')
                    ->join('coas as c', 'je.coa_id', '=', 'c.id')
                    ->where('j.company_id', $companyId)
                    ->where('c.type', $type)
                    ->sum(DB::raw('COALESCE(je.debit, 0) - COALESCE(je.credit, 0)'));
                return $type === 'liability' || $type === 'equity'
                    ? -abs((float) $total)
                    : abs((float) $total);

            case 'cash':
                return (float) DB::table('journal_entries as je')
                    ->join('journals as j', 'je.journal_id', '=', 'j.id')
                    ->join('coas as c', 'je.coa_id', '=', 'c.id')
                    ->where('j.company_id', $companyId)
                    ->where('c.type', 'asset')
                    ->whereIn('c.code', ['1-1000', '1-1100', '1-1110'])
                    ->sum(DB::raw('COALESCE(je.debit, 0) - COALESCE(je.credit, 0)'));

            case 'receivable':
                return (float) DB::table('journal_entries as je')
                    ->join('journals as j', 'je.journal_id', '=', 'j.id')
                    ->join('coas as c', 'je.coa_id', '=', 'c.id')
                    ->where('j.company_id', $companyId)
                    ->where('c.type', 'asset')
                    ->whereIn('c.code', ['1-1200', '1-1300', '1-1400'])
                    ->sum(DB::raw('COALESCE(je.debit, 0) - COALESCE(je.credit, 0)'));

            case 'inventory':
                return (float) DB::table('journal_entries as je')
                    ->join('journals as j', 'je.journal_id', '=', 'j.id')
                    ->join('coas as c', 'je.coa_id', '=', 'c.id')
                    ->where('j.company_id', $companyId)
                    ->where('c.type', 'asset')
                    ->whereIn('c.code', ['1-1500', '1-1600'])
                    ->sum(DB::raw('COALESCE(je.debit, 0) - COALESCE(je.credit, 0)'));

            case 'current_asset':
                return (float) DB::table('journal_entries as je')
                    ->join('journals as j', 'je.journal_id', '=', 'j.id')
                    ->join('coas as c', 'je.coa_id', '=', 'c.id')
                    ->where('j.company_id', $companyId)
                    ->where('c.type', 'asset')
                    ->sum(DB::raw('COALESCE(je.debit, 0) - COALESCE(je.credit, 0)'));

            case 'current_liability':
                $total = (float) DB::table('journal_entries as je')
                    ->join('journals as j', 'je.journal_id', '=', 'j.id')
                    ->join('coas as c', 'je.coa_id', '=', 'c.id')
                    ->where('j.company_id', $companyId)
                    ->where('c.type', 'liability')
                    ->sum(DB::raw('COALESCE(je.credit, 0) - COALESCE(je.debit, 0)'));
                return $total;

            default:
                return 0;
        }
    }

    private function interpretRatio(string $type, ?float $value): string
    {
        if ($value === null) return 'Data tidak tersedia';

        switch ($type) {
            case 'current_ratio':
                if ($value >= 2) return 'Sangat Sehat';
                if ($value >= 1.5) return 'Sehat';
                if ($value >= 1) return 'Cukup';
                return 'Berisiko';

            case 'quick_ratio':
                if ($value >= 1.5) return 'Sangat Likuid';
                if ($value >= 1) return 'Likuid';
                if ($value >= 0.5) return 'Cukup';
                return 'Kurang Likuid';

            case 'cash_ratio':
                if ($value >= 1) return 'Sangat Kuat';
                if ($value >= 0.5) return 'Kuat';
                if ($value >= 0.2) return 'Cukup';
                return 'Lemah';

            default:
                return '';
        }
    }
}
