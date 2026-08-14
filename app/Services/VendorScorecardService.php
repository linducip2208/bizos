<?php

namespace App\Services;

use App\Models\Bid;
use App\Models\GoodsReceiptInspection;
use App\Models\Supplier;
use Illuminate\Support\Collection;

class VendorScorecardService
{
    public const WEIGHTS = [
        'delivery' => 0.40,
        'quality' => 0.35,
        'price' => 0.15,
        'response' => 0.10,
    ];

    public function calculateScorecard(Supplier $supplier): array
    {
        $delivery = $this->deliveryScoreOrNull($supplier);
        $quality = $this->qualityScoreOrNull($supplier);
        $price = $this->priceScoreOrNull($supplier);
        $response = $this->responseScoreOrNull($supplier);

        $overall = $this->overallScore([
            'delivery' => $delivery,
            'quality' => $quality,
            'price' => $price,
            'response' => $response,
        ]);

        $orders = $supplier->purchaseOrders()
            ->whereIn('status', ['sent', 'approved', 'partially_received', 'received'])
            ->with(['goodsReceipts' => function ($q) {
                $q->where('status', 'posted')->orderBy('receipt_date');
            }])
            ->get();

        $deliveryDays = [];
        foreach ($orders as $po) {
            $firstReceipt = $po->goodsReceipts->first();
            if ($firstReceipt && $firstReceipt->receipt_date && $po->order_date) {
                $deliveryDays[] = $po->order_date->diffInDays($firstReceipt->receipt_date);
            }
        }

        $hasData = $delivery !== null || $quality !== null || $price !== null || $response !== null;

        return [
            'supplier_id' => $supplier->id,
            'supplier_name' => $supplier->name,
            'supplier_code' => $supplier->code,
            'supplier' => $supplier,
            'on_time_delivery' => round($delivery ?? 0.0, 1),
            'quality_acceptance' => round($quality ?? 0.0, 1),
            'price_competitiveness' => round($price ?? 0.0, 1),
            'response_time' => round($response ?? 0.0, 1),
            'overall_score' => round($overall, 1),
            'grade' => self::gradeFromScore($overall),
            'total_orders' => $orders->count(),
            'total_value' => (float) $orders->sum('total'),
            'avg_delivery_days' => $deliveryDays ? round(array_sum($deliveryDays) / count($deliveryDays), 1) : 0.0,
            'has_data' => $hasData,
        ];
    }

    public function getAllScorecards(): Collection
    {
        return Supplier::active()
            ->orderBy('name')
            ->get()
            ->map(fn (Supplier $supplier) => $this->calculateScorecard($supplier))
            ->sortByDesc('overall_score')
            ->values();
    }

    public function getTopSuppliers(int $limit = 10): Collection
    {
        return $this->getAllScorecards()
            ->take($limit)
            ->values();
    }

    public function getWorstSuppliers(int $limit = 10): Collection
    {
        return $this->getAllScorecards()
            ->filter(fn (array $scorecard) => $scorecard['has_data'])
            ->sortBy('overall_score')
            ->take($limit)
            ->values();
    }

    public function calculateDeliveryScore(Supplier $supplier): float
    {
        return round($this->deliveryScoreOrNull($supplier) ?? 0.0, 1);
    }

    public function calculateQualityScore(Supplier $supplier): float
    {
        return round($this->qualityScoreOrNull($supplier) ?? 0.0, 1);
    }

    public function calculatePriceScore(Supplier $supplier): float
    {
        return round($this->priceScoreOrNull($supplier) ?? 0.0, 1);
    }

    public function calculateResponseScore(Supplier $supplier): float
    {
        return round($this->responseScoreOrNull($supplier) ?? 0.0, 1);
    }

    protected function deliveryScoreOrNull(Supplier $supplier): ?float
    {
        $deliveredPos = $supplier->purchaseOrders()
            ->whereNotNull('expected_date')
            ->whereIn('status', ['approved', 'partially_received', 'received'])
            ->whereHas('goodsReceipts', function ($q) {
                $q->where('status', 'posted');
            })
            ->with(['goodsReceipts' => function ($q) {
                $q->where('status', 'posted')->orderBy('receipt_date');
            }])
            ->get();

        if ($deliveredPos->isEmpty()) {
            return null;
        }

        $onTime = 0;
        foreach ($deliveredPos as $po) {
            $firstReceipt = $po->goodsReceipts->first();
            if ($firstReceipt && $firstReceipt->receipt_date && $po->expected_date) {
                if ($firstReceipt->receipt_date->lte($po->expected_date)) {
                    $onTime++;
                }
            }
        }

        return ($onTime / $deliveredPos->count()) * 100;
    }

    protected function qualityScoreOrNull(Supplier $supplier): ?float
    {
        $inspections = GoodsReceiptInspection::query()
            ->whereIn('result', ['pass', 'fail'])
            ->whereHas('goodsReceipt.purchaseOrder', function ($q) use ($supplier) {
                $q->where('supplier_id', $supplier->id);
            })
            ->get();

        $total = $inspections->count();
        if ($total === 0) {
            return null;
        }

        $pass = $inspections->where('result', 'pass')->count();

        return ($pass / $total) * 100;
    }

    protected function priceScoreOrNull(Supplier $supplier): ?float
    {
        $bids = $supplier->bids()
            ->whereIn('status', ['submitted', 'shortlisted', 'accepted'])
            ->whereNotNull('total_amount')
            ->get();

        $ratios = [];
        foreach ($bids as $bid) {
            $lowest = Bid::where('rfq_id', $bid->rfq_id)
                ->whereIn('status', ['submitted', 'shortlisted', 'accepted'])
                ->whereNotNull('total_amount')
                ->min('total_amount');

            if ($lowest && $lowest > 0 && (float) $bid->total_amount > 0) {
                $ratios[] = min(100.0, ($lowest / (float) $bid->total_amount) * 100);
            }
        }

        if ($ratios) {
            return array_sum($ratios) / count($ratios);
        }

        $evaluationAverage = $supplier->bids()
            ->whereNotNull('evaluation_score')
            ->avg('evaluation_score');

        if ($evaluationAverage !== null) {
            return (float) min(100.0, max(0.0, (float) $evaluationAverage));
        }

        return null;
    }

    protected function responseScoreOrNull(Supplier $supplier): ?float
    {
        $entries = $supplier->rfqSuppliers()->get();

        if ($entries->isEmpty()) {
            return null;
        }

        $responded = $entries->filter(fn ($entry) => $entry->responded_at && $entry->invited_at);
        $responseRate = $responded->count() / $entries->count();

        if ($responded->isEmpty()) {
            return 0.0;
        }

        $speeds = [];
        foreach ($responded as $entry) {
            $days = $entry->invited_at->diffInHours($entry->responded_at) / 24;
            $speeds[] = max(0.0, min(100.0, 100 - (($days - 1) * 10)));
        }

        return (array_sum($speeds) / count($speeds)) * $responseRate;
    }

    protected function overallScore(array $scores): float
    {
        $totalWeight = 0.0;
        $weighted = 0.0;

        foreach (self::WEIGHTS as $key => $weight) {
            if (($scores[$key] ?? null) !== null) {
                $weighted += $scores[$key] * $weight;
                $totalWeight += $weight;
            }
        }

        if ($totalWeight === 0.0) {
            return 0.0;
        }

        return $weighted / $totalWeight;
    }

    public static function gradeFromScore(float $score): string
    {
        return match (true) {
            $score >= 85 => 'A',
            $score >= 70 => 'B',
            $score >= 55 => 'C',
            default => 'D',
        };
    }
}
