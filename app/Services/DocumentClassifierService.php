<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\AppFile;
use App\Models\FileFolder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentClassifierService
{
    protected ?AiProvider $provider = null;

    protected array $documentTypes = [
        'ktp' => ['label' => 'KTP', 'folder' => 'KTP', 'icon' => 'heroicon-o-identification'],
        'npwp' => ['label' => 'NPWP', 'folder' => 'NPWP', 'icon' => 'heroicon-o-document-text'],
        'npwp_card' => ['label' => 'Kartu NPWP', 'folder' => 'NPWP', 'icon' => 'heroicon-o-credit-card'],
        'sim' => ['label' => 'SIM', 'folder' => 'SIM', 'icon' => 'heroicon-o-document-text'],
        'passport' => ['label' => 'Paspor', 'folder' => 'Paspor', 'icon' => 'heroicon-o-globe-alt'],
        'contract' => ['label' => 'Kontrak', 'folder' => 'Kontrak', 'icon' => 'heroicon-o-document-check'],
        'invoice' => ['label' => 'Invoice', 'folder' => 'Invoice', 'icon' => 'heroicon-o-receipt-percent'],
        'certificate' => ['label' => 'Sertifikat', 'folder' => 'Sertifikat', 'icon' => 'heroicon-o-academic-cap'],
        'bank_statement' => ['label' => 'Rekening Koran', 'folder' => 'Keuangan', 'icon' => 'heroicon-o-banknotes'],
        'tax_form' => ['label' => 'Formulir Pajak', 'folder' => 'Pajak', 'icon' => 'heroicon-o-document-text'],
        'bpjs_card' => ['label' => 'Kartu BPJS', 'folder' => 'BPJS', 'icon' => 'heroicon-o-heart'],
        'proof_of_payment' => ['label' => 'Bukti Pembayaran', 'folder' => 'Keuangan', 'icon' => 'heroicon-o-check-circle'],
        'other' => ['label' => 'Lainnya', 'folder' => 'Lainnya', 'icon' => 'heroicon-o-document'],
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

    public function classify(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File tidak ditemukan: {$filePath}");
        }

        $fileName = basename($filePath);
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $fileSize = filesize($filePath);

        $fileNameAnalysis = $this->analyzeFileName($fileName);

        $contentHint = $this->extractTextHint($filePath, $extension);

        $provider = $this->getProvider();
        return $this->classifyWithAi($provider, $fileName, $extension, $fileNameAnalysis, $contentHint);
    }

    public function batchClassify(int $companyId): array
    {
        $files = AppFile::whereNull('folder_id')
            ->orWhere('folder_id', function ($q) {
                $q->select('id')->from('file_folders')
                    ->where('name', 'Unclassified')
                    ->orWhere('name', 'Belum Terklasifikasi');
            })
            ->limit(50)
            ->get();

        if ($files->isEmpty()) {
            return ['classified' => 0, 'results' => [], 'message' => 'Tidak ada file yang perlu diklasifikasi.'];
        }

        $results = [];
        $classified = 0;

        foreach ($files as $file) {
            $filePath = storage_path('app/public/' . $file->file_path);
            if (!file_exists($filePath)) {
                $results[] = [
                    'file_id' => $file->id,
                    'file_name' => $file->original_name,
                    'status' => 'error',
                    'message' => 'File tidak ditemukan',
                ];
                continue;
            }

            try {
                $classification = $this->classify($filePath);
                $results[] = array_merge($classification, [
                    'file_id' => $file->id,
                    'file_name' => $file->original_name,
                    'status' => 'success',
                ]);
                $classified++;
            } catch (\Exception $e) {
                $results[] = [
                    'file_id' => $file->id,
                    'file_name' => $file->original_name,
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'classified' => $classified,
            'total' => count($files),
            'results' => $results,
        ];
    }

    public function autoFile(AppFile $file): void
    {
        $filePath = storage_path('app/public/' . $file->file_path);
        if (!file_exists($filePath)) {
            return;
        }

        $classification = $this->classify($filePath);
        $docType = $classification['document_type'] ?? 'other';
        $folderName = $this->documentTypes[$docType]['folder'] ?? 'Lainnya';

        $folder = FileFolder::firstOrCreate(
            ['name' => $folderName],
            ['parent_id' => null, 'is_public' => false]
        );

        $file->update(['folder_id' => $folder->id]);
    }

    public function extractData(string $filePath, string $documentType): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File tidak ditemukan: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $contentHint = $this->extractTextHint($filePath, $extension);
        $provider = $this->getProvider();

        $extractionPrompt = $this->getExtractionPrompt($documentType);
        $fileName = basename($filePath);

        $userMessage = "Nama file: {$fileName}\n\n";
        if ($contentHint) {
            $userMessage .= "Konten terdeteksi:\n{$contentHint}";
        } else {
            $userMessage .= "Tidak dapat membaca konten file secara otomatis. Gunakan nama file sebagai petunjuk.";
        }

        $response = $this->callLlm($provider, $extractionPrompt, $userMessage);
        $jsonResponse = $this->cleanJsonResponse($response);

        $data = json_decode($jsonResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => 'Gagal mengekstrak data',
                'raw_response' => $response,
            ];
        }

        return $data ?: [];
    }

    protected function analyzeFileName(string $fileName): array
    {
        $lower = strtolower($fileName);
        $hints = [];

        $keywordMap = [
            'ktp' => 'ktp',
            'npwp' => 'npwp',
            'sim' => 'sim',
            'paspor' => 'passport',
            'passport' => 'passport',
            'kontrak' => 'contract',
            'contract' => 'contract',
            'perjanjian' => 'contract',
            'invoice' => 'invoice',
            'tagihan' => 'invoice',
            'faktur' => 'invoice',
            'sertifikat' => 'certificate',
            'certificate' => 'certificate',
            'ijazah' => 'certificate',
            'rekening' => 'bank_statement',
            'bank' => 'bank_statement',
            'pajak' => 'tax_form',
            'tax' => 'tax_form',
            'spt' => 'tax_form',
            'bpjs' => 'bpjs_card',
            'kk' => 'family_card',
            'slip' => 'pay_slip',
            'gaji' => 'pay_slip',
            'pembayaran' => 'proof_of_payment',
            'bukti' => 'proof_of_payment',
            'transfer' => 'proof_of_payment',
        ];

        $found = [];
        foreach ($keywordMap as $keyword => $type) {
            if (str_contains($lower, $keyword)) {
                $found[$type] = ($found[$type] ?? 0) + 1;
            }
        }

        arsort($found);
        $hints['keyword_matches'] = array_keys(array_slice($found, 0, 3));
        $hints['file_name'] = $fileName;

        return $hints;
    }

    protected function extractTextHint(string $filePath, string $extension): string
    {
        $content = '';

        if ($extension === 'txt' || $extension === 'csv') {
            $content = file_get_contents($filePath);
            $content = Str::limit($content, 2000);
        } elseif ($extension === 'pdf') {
            try {
                if (class_exists(\Smalot\PdfParser\Parser::class)) {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($filePath);
                    $content = $pdf->getText();
                    $content = Str::limit($content, 2000);
                }
            } catch (\Exception $e) {
                $content = '';
            }
        } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'bmp', 'webp'])) {
            $content = '[File gambar - tidak dapat membaca teks secara langsung. Gunakan nama file ' . basename($filePath) . ' sebagai petunjuk.]';
        } elseif (in_array($extension, ['docx', 'doc'])) {
            $content = '[File dokumen Word - gunakan nama file ' . basename($filePath) . ' sebagai petunjuk.]';
        }

        return $content;
    }

    protected function classifyWithAi(AiProvider $provider, string $fileName, string $extension, array $fileNameAnalysis, string $contentHint): array
    {
        $systemPrompt = $this->getClassificationPrompt();

        $userMessage = "Nama file: {$fileName}\n";
        $userMessage .= "Ekstensi: {$extension}\n";
        $userMessage .= "Kata kunci dari nama file: " . implode(', ', $fileNameAnalysis['keyword_matches'] ?? ['tidak ada']) . "\n";

        if ($contentHint) {
            $userMessage .= "\nKonten teks terdeteksi:\n{$contentHint}\n";
        }

        $userMessage .= "\nKlasifikasikan dokumen ini. HANYA output JSON, tanpa markdown atau teks lain.";

        $response = $this->callLlm($provider, $systemPrompt, $userMessage);
        $jsonResponse = $this->cleanJsonResponse($response);

        $data = json_decode($jsonResponse, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $bestGuess = $fileNameAnalysis['keyword_matches'][0] ?? 'other';
            return [
                'document_type' => $bestGuess,
                'confidence' => 40,
                'suggested_folder' => $this->documentTypes[$bestGuess]['folder'] ?? 'Lainnya',
                'extracted_data' => [],
                'error' => 'AI classification failed, using keyword fallback',
            ];
        }

        $docType = $data['document_type'] ?? 'other';
        if (!isset($this->documentTypes[$docType])) {
            $docType = 'other';
        }

        return [
            'document_type' => $docType,
            'document_label' => $this->documentTypes[$docType]['label'],
            'confidence' => (int) ($data['confidence'] ?? 50),
            'suggested_folder' => $this->documentTypes[$docType]['folder'],
            'extracted_data' => $data['extracted_data'] ?? [],
            'reasoning' => $data['reasoning'] ?? '',
        ];
    }

    protected function getClassificationPrompt(): string
    {
        $types = implode("\n", array_map(fn($k, $v) => "- {$k}: {$v['label']}", array_keys($this->documentTypes), $this->documentTypes));

        return "Anda adalah asisten klasifikasi dokumen untuk aplikasi bisnis BizOS. Klasifikasikan dokumen ke salah satu tipe berikut:\n\n{$types}\n\nFormat output HARUS JSON valid:\n{\n  \"document_type\": \"salah_satu_dari_list_diatas\",\n  \"confidence\": 0-100,\n  \"reasoning\": \"alasan singkat\",\n  \"extracted_data\": {\n    \"nama\": \"jika terdeteksi\",\n    \"nomor_dokumen\": \"jika terdeteksi\",\n    \"tanggal\": \"jika terdeteksi\"\n  }\n}\n\nHANYA output JSON, tanpa markdown code block.";
    }

    protected function getExtractionPrompt(string $documentType): string
    {
        $typeLabel = $this->documentTypes[$documentType]['label'] ?? 'dokumen';

        $fields = match ($documentType) {
            'ktp' => '"nik", "nama", "tempat_lahir", "tanggal_lahir", "jenis_kelamin", "alamat", "agama", "status_perkawinan", "pekerjaan", "kewarganegaraan", "berlaku_hingga"',
            'npwp', 'npwp_card' => '"npwp", "nama", "alamat", "tanggal_terdaftar", "status"',
            'sim' => '"nomor_sim", "nama", "tempat_lahir", "tanggal_lahir", "alamat", "golongan", "berlaku_hingga"',
            'passport' => '"nomor_paspor", "nama", "tempat_lahir", "tanggal_lahir", "tanggal_terbit", "berlaku_hingga", "negara_penerbit"',
            'invoice' => '"nomor_invoice", "vendor", "tanggal", "jumlah", "mata_uang", "deskripsi"',
            'contract' => '"nomor_kontrak", "pihak_pertama", "pihak_kedua", "tanggal_mulai", "tanggal_berakhir", "nilai_kontrak"',
            'bank_statement' => '"nama_bank", "nomor_rekening", "periode", "saldo_awal", "saldo_akhir"',
            default => '"nama", "nomor_dokumen", "tanggal"',
        };

        return "Anda adalah asisten ekstraksi data dokumen. Ekstrak informasi berikut dari dokumen {$typeLabel}:\n\nField yang harus diekstrak: {$fields}\n\nFormat output HARUS JSON valid dengan field-field di atas. Gunakan null jika informasi tidak ditemukan. HANYA output JSON, tanpa markdown code block.";
    }

    protected function cleanJsonResponse(string $response): string
    {
        $response = trim($response);
        $response = preg_replace('/^```(?:json)?\s*/i', '', $response);
        $response = preg_replace('/\s*```$/i', '', $response);
        return $response;
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
                    'temperature' => 0.1,
                    'max_tokens' => 2000,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? '';
            }

            Log::error('DocumentClassifier LLM error', ['status' => $response->status()]);
            return '{}';
        } catch (ConnectionException $e) {
            Log::error('DocumentClassifier connection error: ' . $e->getMessage());
            return '{}';
        }
    }
}
