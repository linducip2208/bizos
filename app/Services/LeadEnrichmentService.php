<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LeadEnrichmentService
{
    protected const CLEARBIT_API = 'https://autocomplete.clearbit.com/v1';

    public function enrich(Lead $lead): Lead
    {
        $data = [];
        $companyName = $lead->company_name ?? '';
        $domain = $this->extractDomain($lead->company_name, $lead->email);

        if ($domain) {
            $data = $this->lookupClearbit($domain);
        }

        if (empty($data) && !empty($companyName)) {
            $data = $this->scrapeData($companyName);
        }

        if (!empty($data)) {
            $lead->update(array_filter([
                'industry' => $lead->industry ?: ($data['industry'] ?? null),
                'company_name' => $companyName ?: ($data['company_name'] ?? null),
                'address' => $lead->address ?: ($data['location'] ?? null),
            ]));

            $lead->setAttribute('_enrichment_meta', [
                'source' => $data['_source'] ?? 'unknown',
                'enriched_at' => now()->toIso8601String(),
                'data' => array_filter([
                    'size' => $data['size'] ?? null,
                    'revenue' => $data['revenue'] ?? null,
                    'founded' => $data['founded'] ?? null,
                    'employees' => $data['employees'] ?? null,
                ]),
            ]);
        }

        return $lead;
    }

    public function getEnrichmentSuggestions(Lead $lead): array
    {
        $suggestions = [];

        if (empty($lead->industry)) {
            $suggestions['industry'] = $this->guessIndustry($lead->company_name);
        }

        if (empty($lead->phone) && !empty($lead->company_name)) {
            $suggestions['source'] = 'Google Business Profile';
            $suggestions['action'] = 'Cari di Google Business untuk ' . $lead->company_name;
        }

        return $suggestions;
    }

    protected function extractDomain(?string $companyName, ?string $email): ?string
    {
        if (!empty($email)) {
            $parts = explode('@', $email);
            if (isset($parts[1]) && !in_array($parts[1], ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'icloud.com'])) {
                return $parts[1];
            }
        }

        if (!empty($companyName)) {
            $slug = str_replace([' ', 'PT.', 'CV.', 'UD.', 'Inc.', 'Ltd.'], '', strtolower($companyName));
            $slug = preg_replace('/[^a-z0-9]/', '', $slug);
            return $slug . '.co.id';
        }

        return null;
    }

    protected function lookupClearbit(string $domain): array
    {
        try {
            $response = Http::timeout(10)->get(self::CLEARBIT_API . '/companies/suggest', [
                'query' => $domain,
            ]);

            if ($response->successful()) {
                $results = $response->json();
                if (!empty($results)) {
                    $first = $results[0];
                    return [
                        'company_name' => $first['name'] ?? null,
                        'domain' => $first['domain'] ?? null,
                        'industry' => null,
                        'location' => null,
                        '_source' => 'clearbit',
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::warning('LeadEnrichment: Clearbit lookup failed', ['error' => $e->getMessage()]);
        }

        return [];
    }

    protected function scrapeData(string $companyName): array
    {
        return [
            'company_name' => $companyName,
            'industry' => $this->guessIndustry($companyName),
            '_source' => 'heuristic',
        ];
    }

    protected function guessIndustry(?string $companyName): ?string
    {
        if (empty($companyName)) return null;

        $lower = strtolower($companyName);
        $map = [
            'tekno' => 'Teknologi',
            'digital' => 'Teknologi',
            'software' => 'Teknologi',
            'it ' => 'Teknologi',
            'solu' => 'Teknologi',
            'manufaktur' => 'Manufaktur',
            'pabrik' => 'Manufaktur',
            'industri' => 'Manufaktur',
            'farmasi' => 'Farmasi',
            'obat' => 'Farmasi',
            'apotek' => 'Farmasi',
            'makanan' => 'F&B',
            'minuman' => 'F&B',
            'resto' => 'F&B',
            'kafe' => 'F&B',
            'cafe' => 'F&B',
            'konstruksi' => 'Konstruksi',
            'bangun' => 'Konstruksi',
            'property' => 'Properti',
            'real estate' => 'Properti',
            'properti' => 'Properti',
            'logistik' => 'Logistik',
            'transport' => 'Logistik',
            'ekspedisi' => 'Logistik',
            'keuangan' => 'Keuangan',
            'finance' => 'Keuangan',
            'bank' => 'Keuangan',
            'asuransi' => 'Asuransi',
            'insurance' => 'Asuransi',
            'kesehatan' => 'Kesehatan',
            'rumah sakit' => 'Kesehatan',
            'klinik' => 'Kesehatan',
            'pendidikan' => 'Pendidikan',
            'sekolah' => 'Pendidikan',
            'universitas' => 'Pendidikan',
            'retail' => 'Retail',
            'toko' => 'Retail',
        ];

        foreach ($map as $key => $industry) {
            if (str_contains($lower, $key)) {
                return $industry;
            }
        }

        return null;
    }
}
