<?php

namespace App\Services;

use App\Models\AiProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;

class ReportNlgService
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

    public function generateExecutiveSummary(array $reportData, string $module): string
    {
        $moduleNames = [
            'bisnis' => 'laporan bisnis',
            'keuangan' => 'laporan keuangan',
            'operasional' => 'laporan operasional',
        ];
        $moduleName = $moduleNames[$module] ?? $module;

        $dataJson = json_encode($reportData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $systemPrompt = "Anda adalah analis bisnis profesional untuk BizOS. Buat ringkasan eksekutif dalam Bahasa Indonesia untuk {$moduleName} berdasarkan data berikut. Format:\n\n1. Ringkasan Utama (2-3 kalimat)\n2. 3-5 Insight Kunci (masing-masing 1 kalimat, bold)\n3. Rekomendasi (2-3 action item)\n\nGunakan angka yang mudah dibaca (Rp 2.3M, bukan Rp 2300000000). Nada profesional namun mudah dipahami.";

        return $this->callLlm($systemPrompt, "Berikut data {$moduleName}:\n\n{$dataJson}");
    }

    public function chartInsight(string $chartType, array $data, array $comparison): string
    {
        $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);
        $comparisonJson = json_encode($comparison, JSON_UNESCAPED_UNICODE);

        $systemPrompt = "Anda adalah analis data untuk BizOS. Berikan insight naratif dari data grafik berikut dalam Bahasa Indonesia. Format: 2-4 kalimat pendek yang menjelaskan tren, pola, dan anomali. Gunakan angka dengan format Rp dan singkatan (M, M, Jt). Sertakan rekomendasi singkat jika relevan.";

        $userMessage = "Jenis grafik: {$chartType}\nData saat ini: {$dataJson}\nData perbandingan: {$comparisonJson}";

        return $this->callLlm($systemPrompt, $userMessage);
    }

    public function generateDailyDigest(int $companyId): string
    {
        $now = now();
        $company = \App\Models\Company::find($companyId);
        $companyName = $company?->name ?? 'Perusahaan';

        $pendingLeaves = \App\Models\Leave::where('company_id', $companyId)
            ->where('status', 'pending')
            ->count();

        $overdueInvoices = \App\Models\Invoice::where('company_id', $companyId)
            ->where('status', 'sent')
            ->where('due_date', '<', $now)
            ->count();

        $unassignedTickets = \App\Models\Ticket::where('company_id', $companyId)
            ->whereNull('assigned_to')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        $pendingApprovals = \App\Models\ApprovalRequest::where('company_id', $companyId)
            ->where('status', 'pending')
            ->count();

        $todayRevenue = \App\Models\Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereDate('invoice_date', $now)
            ->sum('total');

        $posRevenue = \App\Models\PosTransaction::where('company_id', $companyId)
            ->where('payment_status', 'paid')
            ->whereDate('transaction_date', $now)
            ->sum('grand_total');

        $todayTotal = $todayRevenue + $posRevenue;

        $data = [
            'perusahaan' => $companyName,
            'tanggal' => $now->format('d M Y'),
            'izin_pending' => $pendingLeaves,
            'invoice_overdue' => $overdueInvoices,
            'tiket_belum_assigned' => $unassignedTickets,
            'approval_pending' => $pendingApprovals,
            'pendapatan_hari_ini' => Number::format($todayTotal, 0, ',', '.'),
        ];

        $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);

        $systemPrompt = "Anda adalah asisten ringkasan harian untuk BizOS. Buat digest harian singkat dalam Bahasa Indonesia menggunakan data berikut. Format WhatsApp-friendly (gunakan *bold* untuk angka penting, gunakan emoji secukupnya). Maksimal 15 baris. Nada: informatif dan actionable.";

        return $this->callLlm($systemPrompt, "Data harian:\n{$dataJson}");
    }

    public function explainVariance(string $metric, float $current, float $previous): string
    {
        $diff = $current - $previous;
        $percentChange = $previous > 0 ? round(($diff / $previous) * 100, 1) : 0;
        $direction = $diff >= 0 ? 'naik' : 'turun';
        $absPercent = abs($percentChange);

        $data = [
            'metrik' => $metric,
            'nilai_saat_ini' => Number::format($current, 0, ',', '.'),
            'nilai_sebelumnya' => Number::format($previous, 0, ',', '.'),
            'perubahan' => Number::format($diff, 0, ',', '.'),
            'persentase' => "{$absPercent}%",
            'arah' => $direction,
        ];

        $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);

        $systemPrompt = "Anda adalah analis keuangan untuk BizOS. Jelaskan varians metrik bisnis dalam Bahasa Indonesia. 2-3 kalimat analitis yang menjelaskan penyebab potensial perubahan. Gunakan format: '{$metric} {$direction} {$absPercent}% menjadi Rp ...' lalu jelaskan kemungkinan penyebabnya.";

        return $this->callLlm($systemPrompt, $dataJson);
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
                    'temperature' => 0.5,
                    'max_tokens' => 1000,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? 'Tidak dapat menghasilkan ringkasan.';
            }

            Log::error('Report NLG LLM error', ['status' => $response->status()]);
            return $this->generateFallbackSummary($systemPrompt, $userMessage);
        } catch (ConnectionException $e) {
            Log::error('Report NLG connection error: ' . $e->getMessage());
            return $this->generateFallbackSummary($systemPrompt, $userMessage);
        }
    }

    protected function generateFallbackSummary(string $systemPrompt, string $userMessage): string
    {
        $data = json_decode($userMessage, true);
        if (!$data) {
            return 'Ringkasan tidak tersedia saat ini. Silakan refresh halaman.';
        }

        $lines = [];
        foreach ($data as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));
            $lines[] = "• {$label}: {$value}";
        }

        return "Ringkasan Data:\n" . implode("\n", $lines) . "\n\n(Catatan: Ringkasan AI tidak tersedia, menampilkan data mentah.)";
    }
}
