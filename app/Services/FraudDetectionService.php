<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FraudDetectionService
{
    protected ?AiProvider $provider = null;

    protected array $benfordExpected = [
        1 => 30.10, 2 => 17.61, 3 => 12.49, 4 => 9.69,
        5 => 7.92, 6 => 6.69, 7 => 5.80, 8 => 5.12, 9 => 4.58,
    ];

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

    public function scanInvoice(Invoice $invoice): array
    {
        $flags = [];
        $score = 0;

        $duplicateNumber = Invoice::where('invoice_number', $invoice->invoice_number)
            ->where('id', '!=', $invoice->id)
            ->where('company_id', $invoice->company_id)
            ->exists();

        if ($duplicateNumber) {
            $flags[] = [
                'rule' => 'duplicate_invoice_number',
                'severity' => 'high',
                'weight' => 30,
                'description' => 'Nomor invoice duplikat dengan invoice lain',
            ];
            $score += 30;
        }

        $duplicateAmount = Invoice::where('total', $invoice->total)
            ->where('company_id', $invoice->company_id)
            ->where('id', '!=', $invoice->id)
            ->where('invoice_date', '>=', Carbon::parse($invoice->invoice_date)->subDays(3))
            ->where('reference_entity', $invoice->reference_entity)
            ->exists();

        if ($duplicateAmount && $invoice->total > 0) {
            $flags[] = [
                'rule' => 'duplicate_amount_entity',
                'severity' => 'medium',
                'weight' => 20,
                'description' => 'Jumlah dan referensi sama dengan invoice lain dalam 3 hari',
            ];
            $score += 20;
        }

        $zScore = $this->calculateZScore($invoice);
        if ($zScore > 3) {
            $flags[] = [
                'rule' => 'unusual_amount',
                'severity' => 'high',
                'weight' => 25,
                'description' => "Jumlah invoice Rp " . number_format($invoice->total, 0, ',', '.') . " tidak biasa (z-score: " . round($zScore, 2) . ")",
            ];
            $score += 25;
        } elseif ($zScore > 2) {
            $flags[] = [
                'rule' => 'slightly_unusual_amount',
                'severity' => 'medium',
                'weight' => 15,
                'description' => "Jumlah invoice sedikit tidak biasa (z-score: " . round($zScore, 2) . ")",
            ];
            $score += 15;
        }

        if ($invoice->total > 1000000 && $invoice->total % 1000000 == 0) {
            $flags[] = [
                'rule' => 'round_number_anomaly',
                'severity' => 'low',
                'weight' => 8,
                'description' => 'Jumlah invoice bernilai bulat (kelipatan 1 juta)',
            ];
            $score += 8;
        } elseif ($invoice->total > 500000 && $invoice->total % 500000 == 0) {
            $flags[] = [
                'rule' => 'round_number_anomaly',
                'severity' => 'low',
                'weight' => 5,
                'description' => 'Jumlah invoice bernilai bulat (kelipatan 500 ribu)',
            ];
            $score += 5;
        }

        $invoiceDate = Carbon::parse($invoice->invoice_date);
        if ($invoiceDate->isWeekend()) {
            $flags[] = [
                'rule' => 'weekend_posting',
                'severity' => 'low',
                'weight' => 5,
                'description' => 'Invoice dibuat di akhir pekan (' . $invoiceDate->translatedFormat('l') . ')',
            ];
            $score += 5;
        }

        $entityRange = $this->getEntityNormalRange($invoice->reference_entity, $invoice->reference_id, $invoice->company_id);
        if ($entityRange && $invoice->total > $entityRange['max'] * 2) {
            $flags[] = [
                'rule' => 'outside_entity_range',
                'severity' => 'high',
                'weight' => 25,
                'description' => "Jumlah invoice melebihi 2x batas normal entitas ini (normal: Rp " . number_format($entityRange['max'], 0, ',', '.') . ")",
            ];
            $score += 25;
        } elseif ($entityRange && $invoice->total > $entityRange['max']) {
            $flags[] = [
                'rule' => 'above_entity_range',
                'severity' => 'medium',
                'weight' => 15,
                'description' => 'Jumlah invoice di atas batas normal entitas ini',
            ];
            $score += 15;
        }

        $score = min(100, $score);
        $riskLevel = $score >= 60 ? 'high' : ($score >= 30 ? 'medium' : 'low');

        return [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'total' => (float) $invoice->total,
            'risk_score' => $score,
            'risk_level' => $riskLevel,
            'flags' => $flags,
            'total_flags' => count($flags),
        ];
    }

    public function scanAll(int $companyId, string $period = 'this_month'): array
    {
        $now = Carbon::now();
        $dateRange = match ($period) {
            'this_month' => [$now->copy()->startOfMonth(), $now],
            'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_year' => [$now->copy()->startOfYear(), $now],
            'last_30_days' => [$now->copy()->subDays(30), $now],
            'last_90_days' => [$now->copy()->subDays(90), $now],
            default => [$now->copy()->startOfMonth(), $now],
        };

        $invoices = Invoice::where('company_id', $companyId)
            ->whereBetween('invoice_date', $dateRange)
            ->get();

        $results = [];
        $summary = [
            'total_invoices' => count($invoices),
            'flagged' => 0,
            'high_risk' => 0,
            'medium_risk' => 0,
            'low_risk' => 0,
            'total_flags' => 0,
        ];

        foreach ($invoices as $invoice) {
            $scan = $this->scanInvoice($invoice);
            if (!empty($scan['flags'])) {
                $results[] = $scan;
                $summary['flagged']++;
                if ($scan['risk_level'] === 'high') $summary['high_risk']++;
                elseif ($scan['risk_level'] === 'medium') $summary['medium_risk']++;
                else $summary['low_risk']++;
                $summary['total_flags'] += $scan['total_flags'];
            }
        }

        usort($results, fn($a, $b) => $b['risk_score'] <=> $a['risk_score']);

        return [
            'summary' => $summary,
            'flagged_invoices' => $results,
        ];
    }

    public function analyzeVendorPattern(Supplier $vendor): array
    {
        $invoices = Invoice::where('reference_entity', 'supplier')
            ->where('reference_id', $vendor->id)
            ->orderBy('invoice_date', 'desc')
            ->limit(50)
            ->get();

        if ($invoices->isEmpty()) {
            return [
                'vendor_name' => $vendor->name,
                'invoice_count' => 0,
                'typical_amount_range' => ['min' => 0, 'max' => 0],
                'typical_frequency' => 'Tidak ada data',
                'anomaly_count' => 0,
            ];
        }

        $amounts = $invoices->pluck('total')->filter(fn($v) => $v > 0)->toArray();
        sort($amounts);
        $n = count($amounts);

        $q1 = $amounts[(int) ($n * 0.25)] ?? $amounts[0] ?? 0;
        $q3 = $amounts[(int) ($n * 0.75)] ?? end($amounts) ?? 0;
        $iqr = $q3 - $q1;
        $lowerBound = max(0, $q1 - 1.5 * $iqr);
        $upperBound = $q3 + 1.5 * $iqr;

        $anomalies = 0;
        foreach ($amounts as $a) {
            if ($a < $lowerBound || $a > $upperBound) $anomalies++;
        }

        $dates = $invoices->pluck('invoice_date')->filter()->sort()->toArray();
        $frequency = 'Tidak diketahui';
        if (count($dates) >= 2) {
            $firstDate = Carbon::parse(reset($dates));
            $lastDate = Carbon::parse(end($dates));
            $avgInterval = $lastDate->diffInDays($firstDate) / max(1, count($dates) - 1);
            $frequency = round($avgInterval, 0) . ' hari rata-rata';
        }

        return [
            'vendor_name' => $vendor->name,
            'invoice_count' => count($invoices),
            'typical_amount_range' => ['min' => round($lowerBound, 2), 'max' => round($upperBound, 2)],
            'typical_frequency' => $frequency,
            'anomaly_count' => $anomalies,
        ];
    }

    public function benfordAnalysis(int $companyId, string $period = 'this_year'): array
    {
        $now = Carbon::now();
        $dateRange = match ($period) {
            'this_month' => [$now->copy()->startOfMonth(), $now],
            'this_year' => [$now->copy()->startOfYear(), $now],
            'last_90_days' => [$now->copy()->subDays(90), $now],
            default => [$now->copy()->startOfYear(), $now],
        };

        $invoices = Invoice::where('company_id', $companyId)
            ->where('invoice_type', 'purchase')
            ->whereBetween('invoice_date', $dateRange)
            ->where('total', '>', 0)
            ->pluck('total')
            ->toArray();

        $payments = Payment::where('company_id', $companyId)
            ->whereBetween('payment_date', $dateRange)
            ->where('amount', '>', 0)
            ->pluck('amount')
            ->toArray();

        $invoiceDist = $this->calculateFirstDigitDistribution($invoices);
        $paymentDist = $this->calculateFirstDigitDistribution($payments);

        $invoiceDeviations = [];
        $totalInvoiceDeviation = 0;
        foreach ($this->benfordExpected as $digit => $expected) {
            $actual = $invoiceDist[$digit] ?? 0;
            $deviation = abs($actual - $expected);
            $invoiceDeviations[$digit] = ['expected' => $expected, 'actual' => $actual, 'deviation' => round($deviation, 2)];
            $totalInvoiceDeviation += $deviation;
        }

        $paymentDeviations = [];
        $totalPaymentDeviation = 0;
        foreach ($this->benfordExpected as $digit => $expected) {
            $actual = $paymentDist[$digit] ?? 0;
            $deviation = abs($actual - $expected);
            $paymentDeviations[$digit] = ['expected' => $expected, 'actual' => $actual, 'deviation' => round($deviation, 2)];
            $totalPaymentDeviation += $deviation;
        }

        $invoiceSuspicious = $totalInvoiceDeviation > 30;
        $paymentSuspicious = $totalPaymentDeviation > 30;

        return [
            'period' => $period,
            'invoice_count' => count($invoices),
            'payment_count' => count($payments),
            'invoice_distribution' => $invoiceDeviations,
            'payment_distribution' => $paymentDeviations,
            'invoice_total_deviation' => round($totalInvoiceDeviation, 2),
            'payment_total_deviation' => round($totalPaymentDeviation, 2),
            'invoice_suspicious' => $invoiceSuspicious,
            'payment_suspicious' => $paymentSuspicious,
            'verdict' => $invoiceSuspicious || $paymentSuspicious
                ? 'Distribusi angka pertama menyimpang dari Hukum Benford. Kemungkinan manipulasi data.'
                : 'Distribusi angka pertama sesuai dengan Hukum Benford. Tidak ada indikasi manipulasi.',
        ];
    }

    public function generateFraudReport(int $companyId, string $period = 'this_month'): string
    {
        $scan = $this->scanAll($companyId, $period);
        $benford = $this->benfordAnalysis($companyId, $period);
        $provider = $this->getProvider();

        $summary = $scan['summary'];
        $flaggedCount = $summary['flagged'];
        $topInvoices = array_slice($scan['flagged_invoices'], 0, 5);

        $systemPrompt = "Anda adalah auditor forensik profesional. Buat laporan singkat deteksi fraud dalam Bahasa Indonesia. Format: (1) Ringkasan temuan, (2) Analisis pola, (3) Rekomendasi tindakan. Gaya profesional, faktual, langsung ke poin. Maksimal 4 paragraf pendek.";

        $userMessage = "Laporan Deteksi Fraud - Periode: {$period}\n\n";
        $userMessage .= "Statistik:\n";
        $userMessage .= "- Total invoice: {$summary['total_invoices']}\n";
        $userMessage .= "- Invoice terindikasi: {$flaggedCount}\n";
        $userMessage .= "- Risiko tinggi: {$summary['high_risk']}\n";
        $userMessage .= "- Risiko sedang: {$summary['medium_risk']}\n\n";

        $userMessage .= "Benford Analysis: {$benford['verdict']}\n";
        $userMessage .= "- Deviasi invoice: {$benford['invoice_total_deviation']}%\n";
        $userMessage .= "- Deviasi pembayaran: {$benford['payment_total_deviation']}%\n\n";

        if ($topInvoices) {
            $userMessage .= "Invoice teratas:\n";
            foreach ($topInvoices as $inv) {
                $userMessage .= "- #{$inv['invoice_number']} (Skor: {$inv['risk_score']})\n";
            }
        }

        return $this->callLlm($provider, $systemPrompt, $userMessage);
    }

    protected function calculateZScore(Invoice $invoice): float
    {
        $amounts = Invoice::where('company_id', $invoice->company_id)
            ->where('invoice_type', $invoice->invoice_type)
            ->where('total', '>', 0)
            ->pluck('total')
            ->toArray();

        if (count($amounts) < 3) return 0;

        $mean = array_sum($amounts) / count($amounts);
        $variance = 0;
        foreach ($amounts as $a) {
            $variance += pow($a - $mean, 2);
        }
        $stdDev = sqrt($variance / count($amounts));

        if ($stdDev == 0) return 0;

        return abs(($invoice->total - $mean) / $stdDev);
    }

    protected function getEntityNormalRange(?string $entityType, ?int $entityId, int $companyId): ?array
    {
        if (!$entityType || !$entityId) return null;

        $invoices = Invoice::where('company_id', $companyId)
            ->where('reference_entity', $entityType)
            ->where('reference_id', $entityId)
            ->where('total', '>', 0)
            ->orderBy('invoice_date', 'desc')
            ->limit(20)
            ->pluck('total')
            ->toArray();

        if (count($invoices) < 3) return null;

        sort($invoices);
        $n = count($invoices);
        $q1 = $invoices[(int)($n * 0.25)] ?? $invoices[0];
        $q3 = $invoices[(int)($n * 0.75)] ?? end($invoices);
        $iqr = $q3 - $q1;

        return [
            'min' => round(max(0, $q1 - 1.5 * $iqr), 2),
            'max' => round($q3 + 1.5 * $iqr, 2),
        ];
    }

    protected function calculateFirstDigitDistribution(array $values): array
    {
        $distribution = array_fill(1, 9, 0);
        $total = count($values);

        if ($total == 0) return $distribution;

        foreach ($values as $value) {
            if ($value <= 0) continue;
            $firstDigit = (int) substr((string) abs($value), 0, 1);
            if ($firstDigit >= 1 && $firstDigit <= 9) {
                $distribution[$firstDigit]++;
            }
        }

        $nonZero = array_sum($distribution);
        if ($nonZero > 0) {
            foreach ($distribution as $k => $v) {
                $distribution[$k] = round(($v / $nonZero) * 100, 2);
            }
        }

        return $distribution;
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
                    'max_tokens' => 1500,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? '';
            }

            Log::error('FraudDetection LLM error', ['status' => $response->status()]);
            return 'Maaf, tidak dapat menghasilkan laporan fraud.';
        } catch (ConnectionException $e) {
            Log::error('FraudDetection connection error: ' . $e->getMessage());
            return 'Maaf, tidak dapat terhubung ke AI provider.';
        }
    }
}
