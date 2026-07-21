<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CashFlowForecastService
{
    protected ?AiProvider $provider = null;

    public function getProvider(): AiProvider
    {
        if ($this->provider) {
            return $this->provider;
        }

        $this->provider = AiProvider::where('is_active', true)
            ->where('api_format', 'openai_compatible')
            ->first();

        if (!$this->provider) {
            throw new \RuntimeException('Tidak ada AI Provider aktif dengan format openai_compatible.');
        }

        return $this->provider;
    }

    public function setProvider(AiProvider $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    public function forecastCashPosition(int $companyId, int $horizonDays = 30): array
    {
        $now = Carbon::now();
        $startDate = $now->format('Y-m-d');

        $currentBalance = $this->getCurrentCashBalance($companyId);

        $projectedInflows = $this->projectReceivables($companyId, $horizonDays);
        $projectedOutflows = $this->projectPayables($companyId, $horizonDays);
        $recurringExpenses = $this->projectRecurringExpenses($companyId, $horizonDays);

        $result = [];
        $runningBalance = $currentBalance;

        $inflowMap = [];
        foreach ($projectedInflows as $inf) {
            $inflowMap[$inf['date']] = ($inflowMap[$inf['date']] ?? 0) + $inf['amount'];
        }

        $outflowMap = [];
        foreach ($projectedOutflows as $outf) {
            $outflowMap[$outf['date']] = ($outflowMap[$outf['date']] ?? 0) + $outf['amount'];
        }

        foreach ($recurringExpenses as $rec) {
            $outflowMap[$rec['date']] = ($outflowMap[$rec['date']] ?? 0) + $rec['amount'];
        }

        $seasonalAdjustment = $this->getSeasonalCashAdjustment($companyId, $horizonDays);

        for ($i = 0; $i < $horizonDays; $i++) {
            $date = $now->copy()->addDays($i)->format('Y-m-d');
            $inflow = $inflowMap[$date] ?? 0;
            $outflow = $outflowMap[$date] ?? 0;
            $adj = $seasonalAdjustment[$i] ?? 0;

            $netFlow = $inflow - $outflow + $adj;
            $runningBalance += $netFlow;

            $alertLevel = 'normal';
            if ($runningBalance < 0) {
                $alertLevel = 'critical';
            } elseif ($runningBalance < $currentBalance * 0.2) {
                $alertLevel = 'warning';
            } elseif ($runningBalance < $currentBalance * 0.4) {
                $alertLevel = 'caution';
            }

            $result[] = [
                'date' => $date,
                'projected_inflow' => round($inflow, 2),
                'projected_outflow' => round($outflow, 2),
                'net_flow' => round($netFlow, 2),
                'closing_balance' => round($runningBalance, 2),
                'alert_level' => $alertLevel,
            ];
        }

        return $result;
    }

    public function getCashShortageAlerts(int $companyId): array
    {
        $forecast = $this->forecastCashPosition($companyId, 30);
        $alerts = [];

        foreach ($forecast as $f) {
            if ($f['alert_level'] === 'critical' || $f['alert_level'] === 'warning') {
                $alerts[] = $f;
            }
        }

        return $alerts;
    }

    public function simulateScenario(int $companyId, array $changes): array
    {
        $baseForecast = $this->forecastCashPosition($companyId, 30);

        $delayDays = $changes['delay_receivables_days'] ?? 0;
        $additionalExpense = $changes['additional_expense'] ?? 0;
        $additionalIncome = $changes['additional_income'] ?? 0;
        $expenseDate = $changes['expense_date'] ?? Carbon::now()->addDays(7)->format('Y-m-d');
        $incomeDate = $changes['income_date'] ?? Carbon::now()->addDays(14)->format('Y-m-d');

        $scenario = [];
        $runningBalance = $baseForecast[0]['closing_balance'] - $baseForecast[0]['net_flow'];

        foreach ($baseForecast as $i => $f) {
            $inflow = $f['projected_inflow'];
            $outflow = $f['projected_outflow'];

            if ($delayDays > 0 && $i < $delayDays) {
                $inflow *= 0.7;
            }
            if ($delayDays > 0 && $i >= $delayDays && $i < $delayDays + 5) {
                $inflow *= 1.15;
            }

            if ($f['date'] === $expenseDate) {
                $outflow += $additionalExpense;
            }
            if ($f['date'] === $incomeDate) {
                $inflow += $additionalIncome;
            }

            $netFlow = $inflow - $outflow;
            $runningBalance += $netFlow;

            $alertLevel = 'normal';
            if ($runningBalance < 0) {
                $alertLevel = 'critical';
            } elseif ($runningBalance < $baseForecast[0]['closing_balance'] * 0.15) {
                $alertLevel = 'warning';
            }

            $scenario[] = [
                'date' => $f['date'],
                'projected_inflow' => round($inflow, 2),
                'projected_outflow' => round($outflow, 2),
                'net_flow' => round($netFlow, 2),
                'closing_balance' => round($runningBalance, 2),
                'alert_level' => $alertLevel,
            ];
        }

        return $scenario;
    }

    protected function getCurrentCashBalance(int $companyId): float
    {
        $coaIds = DB::table('coas')
            ->where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('type', 'asset_cash')
                    ->orWhere('coa_code', 'like', '1-1%');
            })
            ->pluck('id')
            ->toArray();

        if (empty($coaIds)) {
            return 0;
        }

        $debits = JournalEntry::whereIn('coa_id', $coaIds)
            ->whereHas('journal', function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->where('status', 'posted');
            })
            ->sum('debit');

        $credits = JournalEntry::whereIn('coa_id', $coaIds)
            ->whereHas('journal', function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->where('status', 'posted');
            })
            ->sum('credit');

        return round($debits - $credits, 2);
    }

    protected function projectReceivables(int $companyId, int $horizonDays): array
    {
        $now = Carbon::now();

        $unpaidInvoices = Invoice::where('company_id', $companyId)
            ->where('invoice_type', 'sale')
            ->whereIn('status', ['unpaid', 'partially_paid', 'sent'])
            ->where('remaining_amount', '>', 0)
            ->get();

        $projected = [];

        foreach ($unpaidInvoices as $invoice) {
            $dueDate = $invoice->due_date ?? $invoice->invoice_date?->copy()->addDays(30);
            if (!$dueDate) continue;

            $daysUntilDue = max(0, $now->diffInDays(Carbon::parse($dueDate), false));
            $collectionDelay = $this->getAverageCollectionDelay($invoice->customer_id ?? 0, $companyId);
            $expectedDate = Carbon::parse($dueDate)->addDays($collectionDelay);

            $daysFromNow = max(0, $now->diffInDays($expectedDate, false));
            if ($daysFromNow < $horizonDays) {
                $projected[] = [
                    'date' => $expectedDate->format('Y-m-d'),
                    'amount' => (float) $invoice->remaining_amount,
                    'source' => "Invoice #{$invoice->invoice_number}",
                    'probability' => $this->getCollectionProbability($daysUntilDue),
                ];
            }
        }

        return $this->adjustByProbability($projected);
    }

    protected function projectPayables(int $companyId, int $horizonDays): array
    {
        $now = Carbon::now();

        $unpaidBills = Invoice::where('company_id', $companyId)
            ->where('invoice_type', 'purchase')
            ->whereIn('status', ['unpaid', 'partially_paid', 'received'])
            ->where('remaining_amount', '>', 0)
            ->get();

        $projected = [];

        foreach ($unpaidBills as $bill) {
            $dueDate = $bill->due_date ?? $bill->invoice_date?->copy()->addDays(30);
            if (!$dueDate) continue;
            if (!Carbon::parse($dueDate)->gte($now)) continue;

            $daysFromNow = max(0, $now->diffInDays(Carbon::parse($dueDate), false));
            if ($daysFromNow < $horizonDays) {
                $projected[] = [
                    'date' => Carbon::parse($dueDate)->format('Y-m-d'),
                    'amount' => (float) $bill->remaining_amount,
                    'source' => "Tagihan #{$bill->invoice_number}",
                ];
            }
        }

        return $projected;
    }

    protected function projectRecurringExpenses(int $companyId, int $horizonDays): array
    {
        $now = Carbon::now();
        $projected = [];

        $journals = Journal::where('company_id', $companyId)
            ->where('status', 'posted')
            ->where('journal_date', '>=', $now->copy()->subMonths(3))
            ->where('description', 'like', '%gaji%')
            ->orWhere('description', 'like', '%bulanan%')
            ->orWhere('description', 'like', '%sewa%')
            ->orWhere('description', 'like', '%listrik%')
            ->orWhere('description', 'like', '%internet%')
            ->orWhere('description', 'like', '%langganan%')
            ->get();

        $monthlyPatterns = [];
        foreach ($journals as $j) {
            $day = Carbon::parse($j->journal_date)->day;
            $desc = strtolower($j->description ?? '');
            $key = substr($desc, 0, 20);
            if (!isset($monthlyPatterns[$key])) {
                $monthlyPatterns[$key] = ['amounts' => [], 'days' => []];
            }
            $monthlyPatterns[$key]['amounts'][] = (float) $j->total_debit;
            $monthlyPatterns[$key]['days'][] = $day;
        }

        foreach ($monthlyPatterns as $label => $data) {
            if (count($data['amounts']) < 2) continue;

            $avgAmount = array_sum($data['amounts']) / count($data['amounts']);
            $avgDay = (int) round(array_sum($data['days']) / count($data['days']));

            for ($m = 0; $m < ceil($horizonDays / 30); $m++) {
                $nextDate = $now->copy()->addMonths($m)->day(min($avgDay, $now->copy()->addMonths($m)->daysInMonth));
                if ($nextDate->lte($now)) {
                    $nextDate = $now->copy()->addMonths($m + 1)->day(min($avgDay, $now->copy()->addMonths($m + 1)->daysInMonth));
                }
                $daysFromNow = max(0, $now->diffInDays($nextDate, false));
                if ($daysFromNow < $horizonDays) {
                    $projected[] = [
                        'date' => $nextDate->format('Y-m-d'),
                        'amount' => round($avgAmount, 2),
                        'source' => "Biaya {$label} (recurring)",
                    ];
                }
            }
        }

        return $projected;
    }

    protected function getAverageCollectionDelay(int $customerId, int $companyId): int
    {
        $invoices = Invoice::where('company_id', $companyId)
            ->where('invoice_type', 'sale')
            ->where('status', 'paid')
            ->whereNotNull('due_date')
            ->orderBy('invoice_date', 'desc')
            ->limit(20)
            ->get();

        if (count($invoices) < 3) return 5;

        $delays = [];
        foreach ($invoices as $inv) {
            $payments = InvoicePayment::where('invoice_id', $inv->id)
                ->oldest('created_at')
                ->first();
            if ($payments) {
                $paymentDate = Carbon::parse($payments->created_at);
                $dueDate = Carbon::parse($inv->due_date);
                $delay = $dueDate->diffInDays($paymentDate, false);
                if ($delay > 0) {
                    $delays[] = $delay;
                }
            }
        }

        return count($delays) > 0 ? (int) round(array_sum($delays) / count($delays)) : 5;
    }

    protected function getCollectionProbability(int $daysOverdue): float
    {
        if ($daysOverdue <= 0) return 0.95;
        if ($daysOverdue <= 7) return 0.90;
        if ($daysOverdue <= 14) return 0.80;
        if ($daysOverdue <= 30) return 0.65;
        if ($daysOverdue <= 60) return 0.40;
        if ($daysOverdue <= 90) return 0.20;
        return 0.05;
    }

    protected function adjustByProbability(array $flows): array
    {
        return array_map(function ($flow) {
            $probability = $flow['probability'] ?? 1.0;
            return array_merge($flow, [
                'amount' => round($flow['amount'] * $probability, 2),
            ]);
        }, $flows);
    }

    protected function getSeasonalCashAdjustment(int $companyId, int $horizonDays): array
    {
        $adjustments = [];
        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek;
        $dayOfMonth = $now->day;

        for ($i = 0; $i < $horizonDays; $i++) {
            $date = $now->copy()->addDays($i);
            $adj = 0;

            if ($date->dayOfWeek == Carbon::MONDAY) {
                $adj += 100000;
            }
            if ($date->dayOfWeek == Carbon::FRIDAY) {
                $adj -= 50000;
            }

            if ($date->day == 25 || $date->day == 1) {
                $adj -= 200000;
            }

            $adjustments[] = $adj;
        }

        return $adjustments;
    }
}
