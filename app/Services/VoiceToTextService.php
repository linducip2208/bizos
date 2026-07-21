<?php

namespace App\Services;

use App\Models\AiProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class VoiceToTextService
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

    public function transcribe(string $audioPath): array
    {
        if (!file_exists($audioPath)) {
            throw new \RuntimeException("File audio tidak ditemukan: {$audioPath}");
        }

        $provider = $this->getProvider();

        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = decrypt($provider->api_key_encrypted);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])
                ->timeout(120)
                ->attach('file', file_get_contents($audioPath), basename($audioPath))
                ->post("{$baseUrl}/v1/audio/transcriptions", [
                    'model' => 'whisper-1',
                    'language' => 'id',
                    'prompt' => 'Ini adalah transkrip percakapan bisnis dalam Bahasa Indonesia.',
                    'response_format' => 'verbose_json',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'text' => $data['text'] ?? '',
                    'confidence' => $data['segments'][0]['avg_logprob'] ?? null,
                    'language' => $data['language'] ?? 'id',
                    'duration_seconds' => $data['duration'] ?? 0,
                    'segments' => $data['segments'] ?? [],
                    'success' => true,
                ];
            }

            Log::error('Whisper transcription error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [
                'text' => '',
                'success' => false,
                'error' => 'Gagal melakukan transkripsi audio. Status: ' . $response->status(),
            ];
        } catch (ConnectionException $e) {
            Log::error('Whisper connection error: ' . $e->getMessage());
            return [
                'text' => '',
                'success' => false,
                'error' => 'Tidak dapat terhubung ke AI provider. Periksa koneksi.',
            ];
        }
    }

    public function processCommand(string $transcript): array
    {
        $normalized = strtolower(trim($transcript));

        if (preg_match('/buat\s+(task|tugas)\s+(.+?)\s+deadline\s+(.+)/i', $transcript, $m)) {
            return [
                'command_type' => 'create_task',
                'action' => 'create',
                'entity_type' => 'task',
                'params' => [
                    'title' => trim($m[2]),
                    'deadline' => trim($m[3]),
                    'transcript' => $transcript,
                ],
                'raw' => $transcript,
            ];
        }

        if (preg_match('/(?:approve|setujui)\s+(?:cuti|leave)\s+(.+)/i', $transcript, $m)) {
            return [
                'command_type' => 'approve_leave',
                'action' => 'approve',
                'entity_type' => 'leave',
                'params' => [
                    'employee_name' => trim($m[1]),
                    'transcript' => $transcript,
                ],
                'raw' => $transcript,
            ];
        }

        if (preg_match('/(?:tolak|reject)\s+(?:cuti|leave)\s+(.+)/i', $transcript, $m)) {
            return [
                'command_type' => 'reject_leave',
                'action' => 'reject',
                'entity_type' => 'leave',
                'params' => [
                    'employee_name' => trim($m[1]),
                    'transcript' => $transcript,
                ],
                'raw' => $transcript,
            ];
        }

        if (preg_match('/(?:cek|lihat|tampilkan)\s+(?:stok|stock|inventory)\s+(.+)/i', $transcript, $m)) {
            return [
                'command_type' => 'check_stock',
                'action' => 'view',
                'entity_type' => 'product',
                'params' => [
                    'product_name' => trim($m[1]),
                    'transcript' => $transcript,
                ],
                'raw' => $transcript,
            ];
        }

        if (preg_match('/(?:buat|tambah)\s+(?:nota|catatan)\s+(.+)/i', $transcript, $m)) {
            return [
                'command_type' => 'create_note',
                'action' => 'create',
                'entity_type' => 'note',
                'params' => [
                    'content' => trim($m[1]),
                    'transcript' => $transcript,
                ],
                'raw' => $transcript,
            ];
        }

        if (preg_match('/(?:jadwalkan|schedule|atur)\s+(?:meeting|rapat)\s+(.+)/i', $transcript, $m)) {
            return [
                'command_type' => 'schedule_meeting',
                'action' => 'schedule',
                'entity_type' => 'meeting',
                'params' => [
                    'description' => trim($m[1]),
                    'transcript' => $transcript,
                ],
                'raw' => $transcript,
            ];
        }

        if (preg_match('/(?:dashboard|ringkasan|summary)\s*(.+)?/i', $transcript, $m)) {
            return [
                'command_type' => 'view_dashboard',
                'action' => 'navigate',
                'entity_type' => 'dashboard',
                'params' => [
                    'target' => trim($m[1] ?? ''),
                    'transcript' => $transcript,
                ],
                'raw' => $transcript,
            ];
        }

        if (preg_match('/(?:filter|cari)\s+(.+)/i', $transcript, $m)) {
            return [
                'command_type' => 'search',
                'action' => 'filter',
                'entity_type' => 'search',
                'params' => [
                    'query' => trim($m[1]),
                    'transcript' => $transcript,
                ],
                'raw' => $transcript,
            ];
        }

        return [
            'command_type' => 'unknown',
            'action' => 'none',
            'entity_type' => 'unknown',
            'params' => ['transcript' => $transcript],
            'raw' => $transcript,
        ];
    }

    public function executeCommand(array $parsedCommand): array
    {
        $commandType = $parsedCommand['command_type'] ?? 'unknown';
        $params = $parsedCommand['params'] ?? [];

        $result = [
            'command_type' => $commandType,
            'success' => true,
            'message' => '',
            'action' => null,
            'link' => null,
        ];

        switch ($commandType) {
            case 'create_task':
                try {
                    $task = \App\Models\Task::create([
                        'company_id' => auth()->user()->company_id,
                        'title' => $params['title'] ?? 'Task baru',
                        'description' => $params['transcript'] ?? '',
                        'status' => 'pending',
                        'created_by' => auth()->id(),
                    ]);

                    if (!empty($params['deadline'])) {
                        try {
                            $deadline = \Carbon\Carbon::parse($params['deadline']);
                            $task->due_date = $deadline;
                            $task->save();
                        } catch (\Exception $e) {
                        }
                    }

                    $result['message'] = "Task '{$task->title}' berhasil dibuat.";
                    $result['link'] = "/admin/tasks/{$task->id}";
                } catch (\Exception $e) {
                    $result['success'] = false;
                    $result['message'] = "Gagal membuat task: {$e->getMessage()}";
                }
                break;

            case 'approve_leave':
            case 'reject_leave':
                $employeeName = $params['employee_name'] ?? '';
                $leaves = \App\Models\Leave::whereHas('employee', function ($q) use ($employeeName) {
                    $q->where(DB::raw("CONCAT(first_name, ' ', COALESCE(last_name, ''))"), 'like', "%{$employeeName}%");
                })->where('status', 'pending')->limit(10)->get();

                if ($leaves->isEmpty()) {
                    $result['success'] = false;
                    $result['message'] = "Tidak ditemukan pengajuan cuti pending untuk '{$employeeName}'.";
                } elseif ($leaves->count() > 1) {
                    $result['success'] = false;
                    $result['message'] = "Ditemukan {$leaves->count()} pengajuan cuti. Silakan pilih spesifik di halaman cuti.";
                    $result['link'] = '/admin/leaves';
                } else {
                    $leave = $leaves->first();
                    $leave->update(['status' => $commandType === 'approve_leave' ? 'approved' : 'rejected']);
                    $actionWord = $commandType === 'approve_leave' ? 'disetujui' : 'ditolak';
                    $result['message'] = "Cuti {$leave->employee->first_name} berhasil {$actionWord}.";
                    $result['link'] = "/admin/leaves/{$leave->id}";
                }
                break;

            case 'check_stock':
                $productName = $params['product_name'] ?? '';
                $products = \App\Models\Product::where('name', 'like', "%{$productName}%")
                    ->where('is_active', true)
                    ->limit(5)
                    ->get();

                if ($products->isEmpty()) {
                    $result['message'] = "Produk '{$productName}' tidak ditemukan.";
                } elseif ($products->count() === 1) {
                    $p = $products->first();
                    $result['message'] = "Stok {$p->name}: {$p->stock} {$p->unit} (Min: {$p->min_stock}, Maks: {$p->max_stock})";
                    $result['link'] = "/admin/products/{$p->id}";
                } else {
                    $list = $products->map(fn($p) => "{$p->name}: {$p->stock} {$p->unit}")->implode('; ');
                    $result['message'] = "Ditemukan {$products->count()} produk: {$list}";
                    $result['link'] = '/admin/products';
                }
                break;

            case 'create_note':
                try {
                    $note = \App\Models\Task::create([
                        'company_id' => auth()->user()->company_id,
                        'title' => 'Catatan: ' . \Illuminate\Support\Str::limit($params['content'] ?? $params['transcript'] ?? '', 100),
                        'description' => $params['content'] ?? $params['transcript'] ?? '',
                        'status' => 'pending',
                        'created_by' => auth()->id(),
                    ]);
                    $result['message'] = "Catatan berhasil dibuat sebagai task.";
                    $result['link'] = "/admin/tasks/{$note->id}";
                } catch (\Exception $e) {
                    $result['message'] = "Gagal membuat catatan. Perintah suara yang diterima: {$params['transcript']}";
                }
                break;

            case 'view_dashboard':
                $result['message'] = "Membuka dashboard.";
                $result['link'] = '/admin';
                break;

            case 'search':
                $result['message'] = "Pencarian: {$params['query']}";
                $result['link'] = '/admin';
                $result['action'] = ['type' => 'global_search', 'query' => $params['query']];
                break;

            case 'schedule_meeting':
                $result['message'] = "Perintah penjadwalan rapat diterima: {$params['description']}. Fitur penjadwalan suara akan segera tersedia.";
                break;

            default:
                $result['success'] = false;
                $result['message'] = "Maaf, perintah suara tidak dikenali. Teks yang diterima: '{$params['transcript']}'. Silakan coba dengan format yang lebih jelas.";
                break;
        }

        return $result;
    }
}
