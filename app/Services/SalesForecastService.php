<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesForecastService
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

    public function forecastPerProduct(int $productId, int $horizonDays = 30): array
    {
        $historical = $this->getProductDailySales($productId, 365);

        if (count($historical) < 30) {
            return $this->generateSimpleForecast($historical, $horizonDays);
        }

        $values = array_column($historical, 'quantity');
        $seasonalPeriod = 7;

        $trend = $this->linearRegression($values);
        $seasonal = $this->extractSeasonality($values, $seasonalPeriod);
        $deseasonalized = [];
        foreach ($values as $i => $v) {
            $sIdx = $i % $seasonalPeriod;
            $deseasonalized[] = $seasonal[$sIdx] > 0 ? $v / $seasonal[$sIdx] : $v;
        }

        $alpha = 0.3;
        $beta = 0.1;
        $gamma = 0.1;

        $forecast = $this->holtWinters($deseasonalized, $alpha, $beta, $gamma, $seasonalPeriod, $horizonDays, $seasonal);

        $lastDate = Carbon::parse(end($historical)['date']);
        $rmse = $this->calculateRmse($values);
        $result = [];

        foreach ($forecast as $i => $fv) {
            $date = $lastDate->copy()->addDays($i + 1);
            $confidence = max(0.15, min(0.5, $rmse / max(1, abs($fv))));
            $result[] = [
                'date' => $date->format('Y-m-d'),
                'predicted_quantity' => round(max(0, $fv), 2),
                'confidence_low' => round(max(0, $fv * (1 - $confidence * 1.96)), 2),
                'confidence_high' => round($fv * (1 + $confidence * 1.96), 2),
                'trend_direction' => $i > 0 && $forecast[$i] >= $forecast[$i - 1] ? 'up' : 'down',
            ];
        }

        return $result;
    }

    public function forecastRevenue(int $companyId, int $horizonDays = 30): array
    {
        $historical = $this->getCompanyDailyRevenue($companyId, 365);
        return $this->forecastPerProductFromValues($historical, $horizonDays);
    }

    public function getForecastAccuracy(int $companyId, string $period = 'last_30_days'): array
    {
        $actualValues = [];
        $forecastValues = [];
        $now = now();

        $days = match ($period) {
            'last_7_days' => 7,
            'last_30_days' => 30,
            'last_90_days' => 90,
            default => 30,
        };

        $historical = $this->getCompanyDailyRevenue($companyId, $days * 2);

        if (count($historical) < $days + 7) {
            return ['mape' => 0, 'rmse' => 0, 'bias' => 0, 'data_points' => 0];
        }

        $train = array_slice($historical, 0, count($historical) - $days);
        $test = array_slice($historical, count($historical) - $days);
        $trainValues = array_column($train, 'quantity');
        $actualValues = array_column($test, 'quantity');

        $alpha = 0.3;
        $beta = 0.1;
        $seasonalPeriod = 7;
        $seasonal = $this->extractSeasonality($trainValues, $seasonalPeriod);
        $deseasonalized = [];
        foreach ($trainValues as $i => $v) {
            $sIdx = $i % $seasonalPeriod;
            $deseasonalized[] = $seasonal[$sIdx] > 0 ? $v / $seasonal[$sIdx] : $v;
        }

        $forecastValues = $this->holtWinters($deseasonalized, $alpha, $beta, $gamma = 0.1, $seasonalPeriod, count($actualValues), $seasonal);

        $n = min(count($actualValues), count($forecastValues));
        $sumAbsPercentError = 0;
        $sumSquaredError = 0;
        $sumError = 0;
        $validCount = 0;

        for ($i = 0; $i < $n; $i++) {
            $actual = $actualValues[$i];
            $predicted = $forecastValues[$i];
            if ($actual > 0) {
                $sumAbsPercentError += abs(($actual - $predicted) / $actual);
                $sumSquaredError += pow($actual - $predicted, 2);
                $sumError += ($predicted - $actual);
                $validCount++;
            }
        }

        return [
            'mape' => $validCount > 0 ? round(($sumAbsPercentError / $validCount) * 100, 2) : 0,
            'rmse' => $validCount > 0 ? round(sqrt($sumSquaredError / $validCount), 2) : 0,
            'bias' => $validCount > 0 ? round($sumError / $validCount, 2) : 0,
            'data_points' => $validCount,
        ];
    }

    public function generateNarrative(array $forecast): string
    {
        $provider = $this->getProvider();

        $firstValue = $forecast[0]['predicted_quantity'] ?? 0;
        $lastValue = $forecast[count($forecast) - 1]['predicted_quantity'] ?? 0;
        $totalPredicted = array_sum(array_column($forecast, 'predicted_quantity'));
        $upDays = count(array_filter($forecast, fn($f) => $f['trend_direction'] === 'up'));
        $downDays = count(array_filter($forecast, fn($f) => $f['trend_direction'] === 'down'));
        $horizonDays = count($forecast);
        $trendPct = $firstValue > 0 ? round((($lastValue - $firstValue) / $firstValue) * 100, 1) : 0;

        $trendWord = $trendPct > 5 ? 'naik signifikan' : ($trendPct > 1 ? 'sedikit naik' : ($trendPct < -5 ? 'turun signifikan' : ($trendPct < -1 ? 'sedikit turun' : 'stabil')));

        $systemPrompt = "Anda adalah analis penjualan profesional. Berikan narasi ringkas dalam 3-4 paragraf pendek dalam Bahasa Indonesia tentang proyeksi penjualan berikut. Gunakan data numerik spesifik. Fokus pada: (1) gambaran umum tren, (2) faktor yang mungkin mempengaruhi, (3) rekomendasi tindakan. Gaya profesional dan langsung ke poin.";

        $userMessage = "Proyeksi penjualan untuk {$horizonDays} hari ke depan:\n";
        $userMessage .= "- Hari pertama: {$firstValue} unit\n";
        $userMessage .= "- Hari terakhir: {$lastValue} unit\n";
        $userMessage .= "- Total proyeksi: {$totalPredicted} unit\n";
        $userMessage .= "- Tren: {$trendWord} ({$trendPct}%)\n";
        $userMessage .= "- Hari naik: {$upDays}, Hari turun: {$downDays}\n";
        $userMessage .= "\nBuat narasi analitis berdasarkan data ini.";

        return $this->callLlm($provider, $systemPrompt, $userMessage);
    }

    protected function getProductDailySales(int $productId, int $days): array
    {
        $start = Carbon::now()->subDays($days)->startOfDay();

        return PosTransactionItem::where('product_id', $productId)
            ->whereHas('transaction', function ($q) use ($start) {
                $q->where('transaction_date', '>=', $start);
            })
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(quantity) as quantity'),
                DB::raw('SUM(subtotal) as revenue')
            )
            ->join('pos_transactions', 'pos_transactions.id', '=', 'pos_transaction_items.transaction_id')
            ->groupBy(DB::raw('DATE(transaction_date)'))
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['date' => $r->date, 'quantity' => (float) $r->quantity, 'revenue' => (float) $r->revenue])
            ->toArray();
    }

    protected function getCompanyDailyRevenue(int $companyId, int $days): array
    {
        $start = Carbon::now()->subDays($days)->startOfDay();

        $transactions = PosTransaction::where('company_id', $companyId)
            ->where('transaction_date', '>=', $start)
            ->select(
                DB::raw('DATE(transaction_date) as date'),
                DB::raw('SUM(grand_total) as quantity')
            )
            ->groupBy(DB::raw('DATE(transaction_date)'))
            ->orderBy('date')
            ->get()
            ->map(fn($r) => ['date' => $r->date, 'quantity' => (float) ($r->quantity ?? 0), 'revenue' => (float) ($r->quantity ?? 0)])
            ->toArray();

        $dateMap = [];
        foreach ($transactions as $t) {
            $dateMap[$t['date']] = $t;
        }

        $result = [];
        $current = Carbon::parse($start);
        while ($current->lte(Carbon::now())) {
            $d = $current->format('Y-m-d');
            $result[] = $dateMap[$d] ?? ['date' => $d, 'quantity' => 0, 'revenue' => 0];
            $current->addDay();
        }

        return $result;
    }

    protected function forecastPerProductFromValues(array $historical, int $horizonDays): array
    {
        $values = array_column($historical, 'quantity');

        if (count($values) < 7) {
            $avg = count($values) > 0 ? array_sum($values) / count($values) : 0;
            $result = [];
            $lastDate = count($historical) > 0 ? Carbon::parse(end($historical)['date']) : Carbon::now();
            for ($i = 0; $i < $horizonDays; $i++) {
                $date = $lastDate->copy()->addDays($i + 1);
                $result[] = [
                    'date' => $date->format('Y-m-d'),
                    'predicted_quantity' => round(max(0, $avg), 2),
                    'confidence_low' => round(max(0, $avg * 0.7), 2),
                    'confidence_high' => round($avg * 1.3, 2),
                    'trend_direction' => 'stable',
                ];
            }
            return $result;
        }

        $seasonalPeriod = 7;
        $seasonal = $this->extractSeasonality($values, $seasonalPeriod);
        $deseasonalized = [];
        foreach ($values as $i => $v) {
            $sIdx = $i % $seasonalPeriod;
            $deseasonalized[] = $seasonal[$sIdx] > 0 ? $v / $seasonal[$sIdx] : $v;
        }

        $forecast = $this->holtWinters($deseasonalized, 0.3, 0.1, 0.1, $seasonalPeriod, $horizonDays, $seasonal);

        $lastDate = Carbon::parse(end($historical)['date']);
        $result = [];
        foreach ($forecast as $i => $fv) {
            $date = $lastDate->copy()->addDays($i + 1);
            $variability = $this->calculateRmse($values);
            $confidence = max(0.1, min(0.5, $variability / max(1, abs($fv))));
            $result[] = [
                'date' => $date->format('Y-m-d'),
                'predicted_quantity' => round(max(0, $fv), 2),
                'confidence_low' => round(max(0, $fv * (1 - $confidence * 1.96)), 2),
                'confidence_high' => round($fv * (1 + $confidence * 1.96), 2),
                'trend_direction' => $i > 0 && $forecast[$i] >= $forecast[$i - 1] ? 'up' : 'down',
            ];
        }

        return $result;
    }

    protected function generateSimpleForecast(array $historical, int $horizonDays): array
    {
        $values = array_column($historical, 'quantity');
        $avg = count($values) > 0 ? array_sum($values) / count($values) : 0;
        $lastDate = count($historical) > 0 ? Carbon::parse(end($historical)['date']) : Carbon::now();
        $result = [];

        for ($i = 0; $i < $horizonDays; $i++) {
            $dayOfWeek = $lastDate->copy()->addDays($i + 1)->dayOfWeek;
            $weekendFactor = in_array($dayOfWeek, [0, 6]) ? 0.7 : 1.0;
            $fv = $avg * $weekendFactor;
            $result[] = [
                'date' => $lastDate->copy()->addDays($i + 1)->format('Y-m-d'),
                'predicted_quantity' => round(max(0, $fv), 2),
                'confidence_low' => round(max(0, $fv * 0.5), 2),
                'confidence_high' => round($fv * 1.5, 2),
                'trend_direction' => 'stable',
            ];
        }

        return $result;
    }

    protected function linearRegression(array $values): array
    {
        $n = count($values);
        if ($n < 2) return ['slope' => 0, 'intercept' => $values[0] ?? 0];

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $x = $i;
            $y = $values[$i];
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / max(1, ($n * $sumX2 - $sumX * $sumX));
        $intercept = ($sumY - $slope * $sumX) / $n;

        return ['slope' => $slope, 'intercept' => $intercept];
    }

    protected function extractSeasonality(array $values, int $period): array
    {
        $n = count($values);
        if ($n < $period) {
            return array_fill(0, $period, 1.0);
        }

        $seasonal = array_fill(0, $period, 0.0);
        $count = array_fill(0, $period, 0);

        $trend = $this->linearRegression($values);
        for ($i = 0; $i < $n; $i++) {
            $trendValue = $trend['intercept'] + $trend['slope'] * $i;
            $sIdx = $i % $period;
            if ($trendValue > 0) {
                $seasonal[$sIdx] += $values[$i] / $trendValue;
                $count[$sIdx]++;
            }
        }

        $avgSeasonal = 0;
        $validCount = 0;
        for ($i = 0; $i < $period; $i++) {
            if ($count[$i] > 0) {
                $seasonal[$i] /= $count[$i];
                $avgSeasonal += $seasonal[$i];
                $validCount++;
            }
        }

        if ($validCount > 0 && $avgSeasonal > 0) {
            $normFactor = $period / $avgSeasonal;
            for ($i = 0; $i < $period; $i++) {
                $seasonal[$i] *= $normFactor;
            }
        }

        return $seasonal;
    }

    protected function holtWinters(array $values, float $alpha, float $beta, float $gamma, int $seasonalPeriod, int $horizon, array $seasonal): array
    {
        $n = count($values);
        if ($n < 2) return array_fill(0, $horizon, $values[0] ?? 0);

        $level = $values[0];
        $trend = $n > 1 ? $values[1] - $values[0] : 0;
        $seasonalLocal = $seasonal;

        for ($i = 0; $i < $n; $i++) {
            $prevLevel = $level;
            $sIdx = $i % $seasonalPeriod;
            $level = $alpha * ($values[$i] / max(0.001, $seasonalLocal[$sIdx])) + (1 - $alpha) * ($level + $trend);
            $trend = $beta * ($level - $prevLevel) + (1 - $beta) * $trend;
            $seasonalLocal[$sIdx] = $gamma * ($values[$i] / max(0.001, $level)) + (1 - $gamma) * $seasonalLocal[$sIdx];
        }

        $forecast = [];
        for ($h = 0; $h < $horizon; $h++) {
            $sIdx = ($n + $h) % $seasonalPeriod;
            $fv = ($level + $trend * ($h + 1)) * $seasonalLocal[$sIdx];
            $forecast[] = $fv;
        }

        return $forecast;
    }

    protected function calculateRmse(array $values): float
    {
        $n = count($values);
        if ($n < 2) return 0;

        $mean = array_sum($values) / $n;
        $sumSq = 0;
        foreach ($values as $v) {
            $sumSq += pow($v - $mean, 2);
        }
        return sqrt($sumSq / $n);
    }

    protected function callLlm(AiProvider $provider, string $systemPrompt, string $userMessage): string
    {
        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = decrypt($provider->api_key_encrypted);
        $model = $provider->default_model ?: 'gpt-4o-mini';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(60)
                ->post("{$baseUrl}/v1/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 1000,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? '';
            }

            Log::error('SalesForecast LLM error', ['status' => $response->status()]);
            return 'Maaf, tidak dapat menghasilkan narasi saat ini.';
        } catch (ConnectionException $e) {
            Log::error('SalesForecast connection error: ' . $e->getMessage());
            return 'Maaf, tidak dapat terhubung ke AI provider.';
        }
    }
}
