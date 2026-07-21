<?php

namespace App\Services;

use App\Models\KpiDefinition;
use App\Models\KpiValue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KpiService
{
    public function createKpi(array $data): KpiDefinition
    {
        return KpiDefinition::create([
            'company_id' => $data['company_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? 'general',
            'calculation_formula' => $data['calculation_formula'],
            'target_value' => $data['target_value'] ?? null,
            'unit' => $data['unit'] ?? null,
            'data_source' => $data['data_source'] ?? null,
            'update_frequency' => $data['update_frequency'] ?? 'monthly',
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function calculateKpi(KpiDefinition $kpi, string $period): float
    {
        $formula = $kpi->calculation_formula;
        $companyId = $kpi->company_id;

        $periodDate = match ($kpi->update_frequency) {
            'daily' => Carbon::parse($period),
            'weekly' => Carbon::parse($period)->startOfWeek(),
            'monthly' => Carbon::parse($period . '-01'),
            'quarterly' => Carbon::parse($period . '-01'),
            'yearly' => Carbon::parse($period . '-01-01'),
            default => Carbon::parse($period . '-01'),
        };

        switch ($formula) {
            case 'total_revenue':
                $value = $this->calculateTotalRevenue($companyId, $periodDate, $kpi->update_frequency);
                break;

            case 'total_expenses':
                $value = $this->calculateTotalExpenses($companyId, $periodDate, $kpi->update_frequency);
                break;

            case 'net_profit':
                $value = $this->calculateTotalRevenue($companyId, $periodDate, $kpi->update_frequency)
                       - $this->calculateTotalExpenses($companyId, $periodDate, $kpi->update_frequency);
                break;

            case 'employee_count':
                $value = $this->calculateEmployeeCount($companyId);
                break;

            case 'attendance_rate':
                $value = $this->calculateAttendanceRate($companyId, $periodDate, $kpi->update_frequency);
                break;

            case 'invoice_collection_rate':
                $value = $this->calculateInvoiceCollectionRate($companyId, $periodDate, $kpi->update_frequency);
                break;

            case 'customer_count':
                $value = $this->calculateCustomerCount($companyId);
                break;

            case 'lead_conversion_rate':
                $value = $this->calculateLeadConversionRate($companyId, $periodDate, $kpi->update_frequency);
                break;

            case 'product_count':
                $value = $this->calculateProductCount($companyId);
                break;

            case 'project_completion_rate':
                $value = $this->calculateProjectCompletionRate($companyId, $periodDate, $kpi->update_frequency);
                break;

            case 'average_deal_value':
                $value = $this->calculateAverageDealValue($companyId, $periodDate, $kpi->update_frequency);
                break;

            case 'gross_margin_percent':
                $revenue = $this->calculateTotalRevenue($companyId, $periodDate, $kpi->update_frequency);
                $cogs = $this->calculateCogs($companyId, $periodDate, $kpi->update_frequency);
                $value = $revenue > 0 ? (($revenue - $cogs) / $revenue) * 100 : 0;
                break;

            case 'stock_turnover':
                $value = $this->calculateStockTurnover($companyId, $periodDate, $kpi->update_frequency);
                break;

            default:
                $value = $this->evaluateCustomFormula($formula, $companyId, $periodDate, $kpi->update_frequency);
                break;
        }

        $target = $kpi->target_value;
        $status = 'on_track';
        if ($target !== null && $target > 0) {
            $ratio = $value / $target;
            if ($ratio < 0.75) {
                $status = 'behind';
            } elseif ($ratio < 0.95) {
                $status = 'at_risk';
            }
        }

        KpiValue::updateOrCreate(
            ['kpi_definition_id' => $kpi->id, 'period' => $period],
            [
                'value' => round($value, 2),
                'target' => $target,
                'status' => $status,
                'calculated_at' => now(),
            ]
        );

        return $value;
    }

    public function getDashboardKpis(int $companyId): array
    {
        $kpiDefs = KpiDefinition::where('company_id', $companyId)
            ->active()
            ->get();

        $result = [];
        foreach ($kpiDefs as $kpi) {
            $period = $this->currentPeriod($kpi->update_frequency);
            $latestValue = $kpi->latestValue();

            if (!$latestValue || $latestValue->period !== $period) {
                $this->calculateKpi($kpi, $period);
                $latestValue = $kpi->values()->first();
            }

            $result[] = [
                'id' => $kpi->id,
                'name' => $kpi->name,
                'category' => $kpi->category,
                'value' => $latestValue?->value ?? 0,
                'target' => $latestValue?->target ?? $kpi->target_value,
                'unit' => $kpi->unit,
                'status' => $latestValue?->status ?? 'on_track',
                'formula' => $kpi->calculation_formula,
                'frequency' => $kpi->update_frequency,
                'period' => $latestValue?->period ?? $period,
            ];
        }

        return $result;
    }

    public function getTrend(KpiDefinition $kpi, int $periods = 12): array
    {
        $frequency = $kpi->update_frequency;

        $periodsList = [];
        for ($i = $periods - 1; $i >= 0; $i--) {
            $period = $this->periodOffset($frequency, $i);
            $periodsList[] = $period;
        }

        $values = [];
        foreach ($periodsList as $period) {
            $existing = KpiValue::where('kpi_definition_id', $kpi->id)
                ->where('period', $period)
                ->first();

            if (!$existing) {
                $this->calculateKpi($kpi, $period);
                $existing = KpiValue::where('kpi_definition_id', $kpi->id)
                    ->where('period', $period)
                    ->first();
            }

            $values[] = [
                'period' => $period,
                'value' => $existing?->value ?? 0,
                'target' => $existing?->target ?? $kpi->target_value,
                'status' => $existing?->status ?? 'on_track',
            ];
        }

        return [
            'kpi_name' => $kpi->name,
            'unit' => $kpi->unit,
            'frequency' => $frequency,
            'values' => $values,
        ];
    }

    public function calculateAllKpis(int $companyId): array
    {
        $kpiDefs = KpiDefinition::where('company_id', $companyId)->active()->get();
        $results = [];

        foreach ($kpiDefs as $kpi) {
            $period = $this->currentPeriod($kpi->update_frequency);
            try {
                $value = $this->calculateKpi($kpi, $period);
                $results[] = ['kpi' => $kpi->name, 'period' => $period, 'value' => $value, 'success' => true];
            } catch (\Exception $e) {
                Log::error('KPI calculation failed: ' . $e->getMessage(), ['kpi' => $kpi->name]);
                $results[] = ['kpi' => $kpi->name, 'period' => $period, 'error' => $e->getMessage(), 'success' => false];
            }
        }

        return $results;
    }

    // ───── Calculation methods ─────

    protected function calculateTotalRevenue(int $companyId, Carbon $date, string $frequency): float
    {
        [$start, $end] = $this->getDateRange($date, $frequency);
        return (float) DB::table('invoices')
            ->where('company_id', $companyId)
            ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->sum('grand_total');
    }

    protected function calculateTotalExpenses(int $companyId, Carbon $date, string $frequency): float
    {
        [$start, $end] = $this->getDateRange($date, $frequency);
        return (float) DB::table('journal_entries')
            ->where('company_id', $companyId)
            ->where('type', 'credit')
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->sum('amount');
    }

    protected function calculateEmployeeCount(int $companyId): float
    {
        return (float) DB::table('employees')
            ->where('company_id', $companyId)
            ->whereNull('deleted_at')
            ->count();
    }

    protected function calculateAttendanceRate(int $companyId, Carbon $date, string $frequency): float
    {
        [$start, $end] = $this->getDateRange($date, $frequency);
        $total = DB::table('employees')->where('company_id', $companyId)->whereNull('deleted_at')->count();
        if ($total === 0) return 0;

        $present = DB::table('attendances')
            ->where('company_id', $companyId)
            ->whereIn('status', ['hadir', 'terlambat'])
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->distinct('employee_id')
            ->count('employee_id');

        $workDays = $start->diffInWeekdays($end) + 1;
        $expected = $total * max($workDays, 1);

        return $expected > 0 ? round(($present / $expected) * 100, 2) : 0;
    }

    protected function calculateInvoiceCollectionRate(int $companyId, Carbon $date, string $frequency): float
    {
        [$start, $end] = $this->getDateRange($date, $frequency);
        $total = DB::table('invoices')
            ->where('company_id', $companyId)
            ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->count();

        if ($total === 0) return 0;

        $paid = DB::table('invoices')
            ->where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])
            ->count();

        return round(($paid / $total) * 100, 2);
    }

    protected function calculateCustomerCount(int $companyId): float
    {
        return (float) DB::table('clients')
            ->where('company_id', $companyId)
            ->whereNull('deleted_at')
            ->count();
    }

    protected function calculateLeadConversionRate(int $companyId, Carbon $date, string $frequency): float
    {
        [$start, $end] = $this->getDateRange($date, $frequency);
        $total = DB::table('leads')
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->count();

        if ($total === 0) return 0;

        $converted = DB::table('leads')
            ->where('company_id', $companyId)
            ->where('status', 'won')
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->count();

        return round(($converted / $total) * 100, 2);
    }

    protected function calculateProductCount(int $companyId): float
    {
        return (float) DB::table('products')
            ->where('company_id', $companyId)
            ->whereNull('deleted_at')
            ->count();
    }

    protected function calculateProjectCompletionRate(int $companyId, Carbon $date, string $frequency): float
    {
        $total = DB::table('projects')->where('company_id', $companyId)->count();
        if ($total === 0) return 0;

        $completed = DB::table('projects')
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->count();

        return round(($completed / $total) * 100, 2);
    }

    protected function calculateAverageDealValue(int $companyId, Carbon $date, string $frequency): float
    {
        [$start, $end] = $this->getDateRange($date, $frequency);
        return (float) DB::table('deals')
            ->where('company_id', $companyId)
            ->where('status', 'won')
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->avg('amount') ?? 0;
    }

    protected function calculateCogs(int $companyId, Carbon $date, string $frequency): float
    {
        [$start, $end] = $this->getDateRange($date, $frequency);
        return (float) DB::table('stock_movements')
            ->where('company_id', $companyId)
            ->where('type', 'out')
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->sum(DB::raw('quantity * unit_cost'));
    }

    protected function calculateStockTurnover(int $companyId, Carbon $date, string $frequency): float
    {
        [$start, $end] = $this->getDateRange($date, $frequency);
        $cogs = $this->calculateCogs($companyId, $date, $frequency);

        $avgInventory = DB::table('stock_balances')
            ->where('company_id', $companyId)
            ->whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->avg('quantity') ?? 0;

        return $avgInventory > 0 ? round($cogs / $avgInventory, 2) : 0;
    }

    protected function evaluateCustomFormula(string $formula, int $companyId, Carbon $date, string $frequency): float
    {
        $revenue = $this->calculateTotalRevenue($companyId, $date, $frequency);
        $expenses = $this->calculateTotalExpenses($companyId, $date, $frequency);

        return round($revenue - $expenses, 2);
    }

    // ───── Helpers ─────

    protected function currentPeriod(string $frequency): string
    {
        return match ($frequency) {
            'daily' => now()->toDateString(),
            'weekly' => now()->format('Y-\WW'),
            'monthly' => now()->format('Y-m'),
            'quarterly' => now()->format('Y-Q') . ceil(now()->month / 3),
            'yearly' => now()->format('Y'),
            default => now()->format('Y-m'),
        };
    }

    protected function periodOffset(string $frequency, int $offset): string
    {
        return match ($frequency) {
            'daily' => now()->subDays($offset)->toDateString(),
            'weekly' => now()->subWeeks($offset)->format('Y-\WW'),
            'monthly' => now()->subMonths($offset)->format('Y-m'),
            'quarterly' => now()->subQuarters($offset)->format('Y-Q') . ceil(now()->subQuarters($offset)->month / 3),
            'yearly' => now()->subYears($offset)->format('Y'),
            default => now()->subMonths($offset)->format('Y-m'),
        };
    }

    protected function getDateRange(Carbon $date, string $frequency): array
    {
        return match ($frequency) {
            'daily' => [$date->copy()->startOfDay(), $date->copy()->endOfDay()],
            'weekly' => [$date->copy()->startOfWeek(), $date->copy()->endOfWeek()],
            'monthly' => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            'quarterly' => [$date->copy()->firstOfQuarter(), $date->copy()->lastOfQuarter()],
            'yearly' => [$date->copy()->startOfYear(), $date->copy()->endOfYear()],
            default => [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
        };
    }
}
