<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\BankAccount;
use App\Models\BudgetItem;
use App\Models\Contract;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\PayrollPeriod;
use App\Models\PosTransaction;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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

    protected function companyId(): int
    {
        return auth()->user()?->company_id ?? 0;
    }

    /**
     * Proyeksi arus kas harian untuk N hari ke depan.
     *
     * @return array{current_cash:float, dates:array, inflows:array, outflows:array,
     *               net:array, cumulative:array, projected_ending_balance:float,
     *               net_change:float, threshold:float, warning_threshold:float, sources:array}
     */
    public function generateForecast(int $days = 90): array
    {
        $now = Carbon::now();

        $currentCash = $this->getCurrentCashPosition();

        $inflowItems = $this->getExpectedInflows($days);
        $outflowItems = $this->getExpectedOutflows($days);

        $inflowMap = [];
        foreach ($inflowItems as $item) {
            $date = $item['date'] ?? null;
            if (!$date) continue;
            $inflowMap[$date] = ($inflowMap[$date] ?? 0) + (float) $item['amount'];
        }

        $outflowMap = [];
        foreach ($outflowItems as $item) {
            $date = $item['date'] ?? null;
            if (!$date) continue;
            $outflowMap[$date] = ($outflowMap[$date] ?? 0) + (float) $item['amount'];
        }

        $dates = [];
        $inflows = [];
        $outflows = [];
        $net = [];
        $cumulative = [];
        $running = $currentCash;

        for ($i = 0; $i < $days; $i++) {
            $date = $now->copy()->addDays($i)->format('Y-m-d');
            $in = $inflowMap[$date] ?? 0;
            $out = $outflowMap[$date] ?? 0;
            $n = $in - $out;
            $running += $n;

            $dates[] = $date;
            $inflows[] = round($in, 2);
            $outflows[] = round($out, 2);
            $net[] = round($n, 2);
            $cumulative[] = round($running, 2);
        }

        return [
            'current_cash' => round($currentCash, 2),
            'dates' => $dates,
            'inflows' => $inflows,
            'outflows' => $outflows,
            'net' => $net,
            'cumulative' => $cumulative,
            'projected_ending_balance' => round($running, 2),
            'net_change' => round($running - $currentCash, 2),
            'threshold' => 0.0,
            'warning_threshold' => round(max(0, $currentCash * 0.2), 2),
            'sources' => $this->summarizeSources($inflowItems, $outflowItems),
        ];
    }

    /**
     * Posisi kas saat ini: jumlah saldo seluruh rekening aktif (dikonversi ke mata uang basis)
     * ditambah setara kas.
     */
    public function getCurrentCashPosition(): float
    {
        $companyId = $this->companyId();
        $total = 0.0;

        $accounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->with('currency')
            ->get();

        foreach ($accounts as $account) {
            $rate = $account->currency?->exchange_rate ?? 1;
            $total += (float) $account->current_balance * (float) $rate;
        }

        return round($total, 2);
    }

    /**
     * Arus kas masuk yang diharapkan: piutang jatuh tempo, proyeksi penjualan POS,
     * dan invoice berulang.
     */
    public function getExpectedInflows(int $days = 90): Collection
    {
        $companyId = $this->companyId();
        $now = Carbon::now();
        $end = $now->copy()->addDays($days);

        $items = collect();

        // 1. Piutang (invoice penjualan) yang belum lunas dan jatuh tempo dalam window
        $invoices = Invoice::where('company_id', $companyId)
            ->where('invoice_type', 'sales')
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->where('remaining_amount', '>', 0)
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $end->format('Y-m-d'))
            ->get();

        foreach ($invoices as $inv) {
            $due = Carbon::parse($inv->due_date);
            if ($due->lt($now->copy()->startOfDay())) {
                $due = $now;
            }
            $items->push([
                'date' => $due->format('Y-m-d'),
                'amount' => (float) $inv->remaining_amount,
                'source' => 'Piutang ' . $inv->invoice_number,
                'category' => 'invoice',
            ]);
        }

        // 2. Proyeksi penjualan POS (rata-rata harian x tren)
        foreach ($this->projectPosSales($companyId, $days) as $pos) {
            $items->push($pos);
        }

        // 3. Invoice berulang (tabel recurring_invoices, bila tersedia)
        foreach ($this->projectRecurringInvoices($companyId, $days) as $rec) {
            $items->push($rec);
        }

        return $items;
    }

    /**
     * Arus kas keluar yang diharapkan: utang jatuh tempo, payroll, biaya berulang,
     * dan kewajiban kontrak.
     */
    public function getExpectedOutflows(int $days = 90): Collection
    {
        $companyId = $this->companyId();
        $now = Carbon::now();
        $end = $now->copy()->addDays($days);

        $items = collect();

        // 1. Utang (invoice pembelian) yang belum lunas dan jatuh tempo dalam window
        $payables = Invoice::where('company_id', $companyId)
            ->where('invoice_type', 'purchase')
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->where('remaining_amount', '>', 0)
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $end->format('Y-m-d'))
            ->get();

        foreach ($payables as $bill) {
            $due = Carbon::parse($bill->due_date);
            if ($due->lt($now->copy()->startOfDay())) {
                $due = $now;
            }
            $items->push([
                'date' => $due->format('Y-m-d'),
                'amount' => (float) $bill->remaining_amount,
                'source' => 'Utang ' . $bill->invoice_number,
                'category' => 'payable',
            ]);
        }

        // 2. Payroll (periode penggajian berikutnya)
        foreach ($this->projectPayroll($companyId, $days) as $pr) {
            $items->push($pr);
        }

        // 3. Biaya berulang (item anggaran & langganan)
        foreach ($this->projectBudgetAndSubscriptions($companyId, $days) as $re) {
            $items->push($re);
        }

        // 4. Kewajiban kontrak
        foreach ($this->projectContractObligations($companyId, $days) as $co) {
            $items->push($co);
        }

        return $items;
    }

    /**
     * Hari-hari ketika saldo kumulatif berada di bawah nol (kritis) atau di bawah
     * ambang peringatan.
     */
    public function getLowBalanceDays(array $forecast): array
    {
        $dates = $forecast['dates'] ?? [];
        $cumulative = $forecast['cumulative'] ?? [];
        $threshold = (float) ($forecast['threshold'] ?? 0);
        $warningThreshold = (float) ($forecast['warning_threshold'] ?? 0);

        $result = [];

        foreach ($cumulative as $i => $balance) {
            if ($balance < $threshold) {
                $result[] = [
                    'date' => $dates[$i] ?? '',
                    'balance' => round((float) $balance, 2),
                    'level' => 'critical',
                    'label' => 'Kritis',
                ];
            } elseif ($balance < $warningThreshold) {
                $result[] = [
                    'date' => $dates[$i] ?? '',
                    'balance' => round((float) $balance, 2),
                    'level' => 'warning',
                    'label' => 'Waspada',
                ];
            }
        }

        return $result;
    }

    /**
     * Rekomendasi berbasis aturan berdasarkan proyeksi arus kas.
     */
    public function generateRecommendations(array $forecast): array
    {
        $currentCash = (float) ($forecast['current_cash'] ?? 0);
        $ending = (float) ($forecast['projected_ending_balance'] ?? 0);
        $lowDays = $this->getLowBalanceDays($forecast);
        $criticalCount = count(array_filter($lowDays, fn($d) => $d['level'] === 'critical'));
        $sources = $forecast['sources'] ?? [];
        $totalInflow = array_sum($sources['inflows'] ?? []);
        $totalOutflow = array_sum($sources['outflows'] ?? []);

        $recommendations = [];

        if ($criticalCount > 0) {
            $recommendations[] = [
                'title' => 'Risiko kas negatif terdeteksi',
                'detail' => "Terdapat {$criticalCount} hari dengan proyeksi saldo kas negatif dalam periode ini. Prioritaskan perbaikan arus kas segera agar kewajiban tetap terpenuhi.",
                'severity' => 'critical',
            ];
        }

        if ($ending < $currentCash && $totalOutflow > $totalInflow) {
            $recommendations[] = [
                'title' => 'Percepat penagihan piutang',
                'detail' => 'Total arus keluar proyeksi melebihi arus masuk. Tawarkan diskon pembayaran lebih awal, kirim pengingat otomatis, dan tindak lanjuti piutang yang lewat jatuh tempo.',
                'severity' => 'warning',
            ];
        }

        if ($totalOutflow > $totalInflow * 1.1) {
            $recommendations[] = [
                'title' => 'Tunda pembayaran kewajiban (payables)',
                'detail' => 'Negosiasikan ulang termin pembayaran dengan pemasok untuk memperpanjang jatuh tempo, tanpa merusak relasi dan peringkat kredit.',
                'severity' => 'warning',
            ];
        }

        if (($forecast['cumulative'] ?? []) && min($forecast['cumulative']) < 0) {
            $recommendations[] = [
                'title' => 'Amankan fasilitas kredit atau overdraft',
                'detail' => 'Saldo kas terendah proyeksi berada di bawah nol. Siapkan fasilitas kredit/overdraft sebagai buffer untuk menutup defisit sementara.',
                'severity' => 'warning',
            ];
        }

        if ($criticalCount === 0 && $ending >= $currentCash) {
            $recommendations[] = [
                'title' => 'Manfaatkan kelebihan kas',
                'detail' => 'Proyeksi kas sehat dan surplus. Pertimbangkan menempatkan kelebihan dana pada deposito berjangka atau instrumen likuid untuk mengoptimalkan imbal hasil.',
                'severity' => 'info',
            ];
        }

        if (empty($recommendations)) {
            $recommendations[] = [
                'title' => 'Arus kas dalam kondisi stabil',
                'detail' => 'Tidak ditemukan risiko arus kas signifikan pada periode ini. Tetap pantau proyeksi secara berkala.',
                'severity' => 'info',
            ];
        }

        return $recommendations;
    }

    protected function projectPosSales(int $companyId, int $days): array
    {
        $now = Carbon::now();

        $last30 = (float) PosTransaction::where('company_id', $companyId)
            ->where('transaction_date', '>=', $now->copy()->subDays(30)->startOfDay())
            ->sum('grand_total');

        $avgDaily = $last30 / 30;

        $last7 = (float) PosTransaction::where('company_id', $companyId)
            ->where('transaction_date', '>=', $now->copy()->subDays(7)->startOfDay())
            ->sum('grand_total');

        $prev7 = (float) PosTransaction::where('company_id', $companyId)
            ->where('transaction_date', '>=', $now->copy()->subDays(14)->startOfDay())
            ->where('transaction_date', '<', $now->copy()->subDays(7)->startOfDay())
            ->sum('grand_total');

        $trend = 1.0;
        if ($prev7 > 0) {
            $trend = max(0.8, min(1.2, $last7 / $prev7));
        }

        $daily = $avgDaily * $trend;

        $result = [];
        for ($i = 1; $i <= $days; $i++) {
            $date = $now->copy()->addDays($i);
            $dayOfWeek = $date->dayOfWeek;
            $weekendFactor = in_array($dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]) ? 0.7 : 1.0;
            $amount = round($daily * $weekendFactor, 2);
            if ($amount <= 0) continue;

            $result[] = [
                'date' => $date->format('Y-m-d'),
                'amount' => $amount,
                'source' => 'Proyeksi Penjualan POS',
                'category' => 'pos',
            ];
        }

        return $result;
    }

    protected function projectRecurringInvoices(int $companyId, int $days): array
    {
        if (!Schema::hasTable('recurring_invoices')) {
            return [];
        }

        $now = Carbon::now();
        $end = $now->copy()->addDays($days);
        $result = [];

        $rows = DB::table('recurring_invoices')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        foreach ($rows as $row) {
            $amount = (float) ($row->amount ?? 0);
            $frequency = $row->frequency ?? 'monthly';
            if ($amount <= 0) continue;

            $cursor = $now->copy();
            $guard = 0;
            while ($cursor->lte($end) && $guard < 400) {
                $cursor->addMonth();
                if ($cursor->lte($end)) {
                    $result[] = [
                        'date' => $cursor->format('Y-m-d'),
                        'amount' => $amount,
                        'source' => 'Invoice Berulang: ' . ($row->name ?? '—'),
                        'category' => 'recurring',
                    ];
                }
                $guard++;
            }
        }

        return $result;
    }

    protected function projectPayroll(int $companyId, int $days): array
    {
        $now = Carbon::now();
        $end = $now->copy()->addDays($days);
        $result = [];

        $periods = PayrollPeriod::where('company_id', $companyId)
            ->whereNotNull('payment_date')
            ->where('payment_date', '>=', $now->format('Y-m-d'))
            ->where('payment_date', '<=', $end->format('Y-m-d'))
            ->get();

        foreach ($periods as $period) {
            $amount = (float) $period->total_net;
            if ($amount <= 0) {
                $amount = (float) $period->payrolls()->sum('net_salary');
            }
            if ($amount <= 0) continue;

            $result[] = [
                'date' => Carbon::parse($period->payment_date)->format('Y-m-d'),
                'amount' => round($amount, 2),
                'source' => 'Payroll ' . ($period->period_code ?? ''),
                'category' => 'payroll',
            ];
        }

        // Estimasi periode payroll berikutnya bila belum ada data periode mendatang
        if ($periods->isEmpty()) {
            $last = PayrollPeriod::where('company_id', $companyId)
                ->whereNotNull('payment_date')
                ->orderByDesc('payment_date')
                ->first();

            if ($last && (float) $last->total_net > 0) {
                $day = Carbon::parse($last->payment_date)->day;
                for ($m = 1; $m <= ceil($days / 30); $m++) {
                    $next = $now->copy()->addMonthsNoOverflow($m)->day(min($day, $now->copy()->addMonthsNoOverflow($m)->daysInMonth));
                    if ($next->gt($end)) break;

                    $result[] = [
                        'date' => $next->format('Y-m-d'),
                        'amount' => round((float) $last->total_net, 2),
                        'source' => 'Payroll (estimasi) ' . $next->format('M Y'),
                        'category' => 'payroll',
                    ];
                }
            }
        }

        return $result;
    }

    protected function projectBudgetAndSubscriptions(int $companyId, int $days): array
    {
        $now = Carbon::now();
        $end = $now->copy()->addDays($days);
        $result = [];

        // Item anggaran yang disetujui, didistribusikan bulanan
        $budgetItems = BudgetItem::whereHas('budget', function ($q) use ($companyId) {
            $q->where('company_id', $companyId)->whereIn('status', ['approved', 'active']);
        })->where('planned_amount', '>', 0)->get();

        foreach ($budgetItems as $item) {
            $start = $item->period_start ? Carbon::parse($item->period_start) : $now->copy()->startOfMonth();
            $endPeriod = $item->period_end ? Carbon::parse($item->period_end) : $now->copy()->endOfYear();
            $months = max(1, $start->diffInMonths($endPeriod) ?: 1);
            $monthly = (float) $item->planned_amount / $months;

            $cursor = $now->copy()->startOfMonth();
            for ($m = 0; $m <= ceil($days / 30); $m++) {
                $date = $cursor->copy()->addMonthsNoOverflow($m);
                if ($date->gt($end)) break;
                if ($date->lt($now->copy()->startOfDay())) continue;
                if ($start->gt($date) || $endPeriod->lt($date)) continue;

                $result[] = [
                    'date' => $date->format('Y-m-d'),
                    'amount' => round($monthly, 2),
                    'source' => 'Anggaran: ' . ($item->description ?? 'Item'),
                    'category' => 'recurring',
                ];
            }
        }

        // Langganan aktif perusahaan (biaya bulanan)
        $subscriptions = Subscription::where('company_id', $companyId)
            ->whereIn('status', ['trial', 'active', 'grace'])
            ->with('plan')
            ->get();

        foreach ($subscriptions as $sub) {
            $price = (float) ($sub->plan?->monthly_price ?? 0);
            if ($price <= 0) continue;

            $cursor = $now->copy()->startOfMonth();
            for ($m = 1; $m <= ceil($days / 30); $m++) {
                $date = $cursor->copy()->addMonthsNoOverflow($m);
                if ($date->gt($end)) break;

                $result[] = [
                    'date' => $date->format('Y-m-d'),
                    'amount' => round($price, 2),
                    'source' => 'Langganan: ' . ($sub->plan?->name ?? 'Plan'),
                    'category' => 'recurring',
                ];
            }
        }

        return $result;
    }

    protected function projectContractObligations(int $companyId, int $days): array
    {
        $now = Carbon::now();
        $end = $now->copy()->addDays($days);
        $result = [];

        $contracts = Contract::where('company_id', $companyId)
            ->where('status', 'active')
            ->where('value', '>', 0)
            ->get();

        foreach ($contracts as $contract) {
            if (!$this->isOutflowContract($contract)) continue;

            $value = (float) $contract->value;
            $start = $contract->start_date ? Carbon::parse($contract->start_date) : $now->copy()->startOfMonth();
            $endContract = $contract->end_date ? Carbon::parse($contract->end_date) : $now->copy()->addYear();
            $months = max(1, $start->diffInMonths($endContract) ?: 1);
            $monthly = $value / $months;

            $cursor = $now->copy()->startOfMonth();
            for ($m = 0; $m <= ceil($days / 30); $m++) {
                $date = $cursor->copy()->addMonthsNoOverflow($m);
                if ($date->gt($end)) break;
                if ($date->lt($now->copy()->startOfDay())) continue;
                if ($start->gt($date) || $endContract->lt($date)) continue;

                $result[] = [
                    'date' => $date->format('Y-m-d'),
                    'amount' => round($monthly, 2),
                    'source' => 'Kontrak: ' . $contract->title,
                    'category' => 'contract',
                ];
            }
        }

        return $result;
    }

    protected function isOutflowContract(Contract $contract): bool
    {
        $party = strtolower(trim((string) $contract->party_type));

        if (in_array($party, ['supplier', 'vendor', 'subcontractor', 'employee'], true)) {
            return true;
        }

        if ($party === '' || $party === 'null') {
            return in_array($contract->contract_type, ['procurement', 'subcontractor', 'tenancy', 'employment'], true);
        }

        return false;
    }

    protected function summarizeSources(Collection $inflows, Collection $outflows): array
    {
        $in = ['invoice' => 0, 'pos' => 0, 'recurring' => 0, 'other' => 0];
        foreach ($inflows as $item) {
            $cat = $item['category'] ?? 'other';
            if (!isset($in[$cat])) $cat = 'other';
            $in[$cat] = ($in[$cat] ?? 0) + (float) $item['amount'];
        }

        $out = ['payable' => 0, 'payroll' => 0, 'recurring' => 0, 'contract' => 0, 'other' => 0];
        foreach ($outflows as $item) {
            $cat = $item['category'] ?? 'other';
            if (!isset($out[$cat])) $cat = 'other';
            $out[$cat] = ($out[$cat] ?? 0) + (float) $item['amount'];
        }

        return [
            'inflows' => array_map(fn($v) => round((float) $v, 2), $in),
            'outflows' => array_map(fn($v) => round((float) $v, 2), $out),
        ];
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
