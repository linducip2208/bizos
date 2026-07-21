<?php

namespace App\Services;

use App\Models\AbcClassification;
use App\Models\Product;
use App\Models\PosTransactionItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AbcAnalysisService
{
    public function classify(int $companyId): void
    {
        $products = Product::where('company_id', $companyId)
            ->where('is_active', true)
            ->get();

        $consumptionData = [];
        foreach ($products as $product) {
            $annualValue = $this->calculateAnnualConsumptionValue($product, $companyId);
            if ($annualValue > 0) {
                $consumptionData[$product->id] = [
                    'product' => $product,
                    'annual_value' => $annualValue,
                ];
            }
        }

        uasort($consumptionData, fn($a, $b) => $b['annual_value'] <=> $a['annual_value']);

        $totalValue = array_sum(array_column($consumptionData, 'annual_value'));

        $cumulativePercent = 0;
        foreach ($consumptionData as $data) {
            $percent = ($totalValue > 0) ? ($data['annual_value'] / $totalValue) * 100 : 0;
            $cumulativePercent += $percent;

            $classification = 'C';
            if ($cumulativePercent - $percent <= 80) {
                $classification = 'A';
            } elseif ($cumulativePercent - $percent <= 95) {
                $classification = 'B';
            }

            AbcClassification::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'product_id' => $data['product']->id,
                ],
                [
                    'classification' => $classification,
                    'annual_consumption_value' => round($data['annual_value'], 2),
                    'cumulative_percent' => round($cumulativePercent, 2),
                    'calculated_at' => now(),
                ]
            );
        }
    }

    public function getClassification(Product $product): string
    {
        $classification = AbcClassification::where('product_id', $product->id)
            ->where('company_id', $product->company_id)
            ->first();

        return $classification?->classification ?? 'C';
    }

    public function getCycleCountSchedule(int $companyId): array
    {
        $classifications = AbcClassification::where('company_id', $companyId)
            ->with('product')
            ->orderBy('classification')
            ->orderByDesc('annual_consumption_value')
            ->get();

        $schedule = [];
        $today = Carbon::now();

        foreach ($classifications as $classification) {
            $frequency = match ($classification->classification) {
                'A' => 'monthly',
                'B' => 'quarterly',
                default => 'annually',
            };

            $nextCountDate = match ($classification->classification) {
                'A' => $today->copy()->addMonth(),
                'B' => $today->copy()->addMonths(3),
                default => $today->copy()->addYear(),
            };

            $schedule[] = [
                'product_id' => $classification->product_id,
                'product_name' => $classification->product?->name,
                'product_code' => $classification->product?->code,
                'classification' => $classification->classification,
                'annual_value' => (float) $classification->annual_consumption_value,
                'frequency' => $frequency,
                'last_counted' => $classification->calculated_at?->format('Y-m-d'),
                'next_count_date' => $nextCountDate->format('Y-m-d'),
            ];
        }

        return $schedule;
    }

    public function getSummary(int $companyId): array
    {
        $classifications = AbcClassification::where('company_id', $companyId)->get();
        $totalValue = $classifications->sum('annual_consumption_value');

        $aItems = $classifications->where('classification', 'A');
        $bItems = $classifications->where('classification', 'B');
        $cItems = $classifications->where('classification', 'C');

        return [
            'total_items' => $classifications->count(),
            'total_annual_value' => (float) $totalValue,
            'a_items' => [
                'count' => $aItems->count(),
                'value' => (float) $aItems->sum('annual_consumption_value'),
                'percent_value' => $totalValue > 0 ? round(($aItems->sum('annual_consumption_value') / $totalValue) * 100, 1) : 0,
                'percent_count' => $classifications->count() > 0 ? round(($aItems->count() / $classifications->count()) * 100, 1) : 0,
                'cycle_count' => 'bulanan',
            ],
            'b_items' => [
                'count' => $bItems->count(),
                'value' => (float) $bItems->sum('annual_consumption_value'),
                'percent_value' => $totalValue > 0 ? round(($bItems->sum('annual_consumption_value') / $totalValue) * 100, 1) : 0,
                'percent_count' => $classifications->count() > 0 ? round(($bItems->count() / $classifications->count()) * 100, 1) : 0,
                'cycle_count' => 'triwulan',
            ],
            'c_items' => [
                'count' => $cItems->count(),
                'value' => (float) $cItems->sum('annual_consumption_value'),
                'percent_value' => $totalValue > 0 ? round(($cItems->sum('annual_consumption_value') / $totalValue) * 100, 1) : 0,
                'percent_count' => $classifications->count() > 0 ? round(($cItems->count() / $classifications->count()) * 100, 1) : 0,
                'cycle_count' => 'tahunan',
            ],
        ];
    }

    protected function calculateAnnualConsumptionValue(Product $product, int $companyId): float
    {
        $oneYearAgo = Carbon::now()->subYear();

        $soldValue = PosTransactionItem::where('product_id', $product->id)
            ->whereHas('transaction', function ($q) use ($oneYearAgo) {
                $q->where('transaction_date', '>=', $oneYearAgo);
            })
            ->sum(DB::raw('quantity * unit_price'));

        $consumedValue = StockMovement::where('product_id', $product->id)
            ->where('company_id', $companyId)
            ->where('movement_type', 'out')
            ->where('movement_date', '>=', $oneYearAgo)
            ->sum(DB::raw('quantity_out * unit_cost'));

        return (float) max($soldValue, $consumedValue);
    }
}
