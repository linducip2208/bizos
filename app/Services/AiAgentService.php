<?php

namespace App\Services;

use App\Models\AiAgent;
use App\Models\AiProvider;
use App\Models\AiWorkflow;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAgentService
{
    public function createAgent(array $data): AiAgent
    {
        return AiAgent::create([
            'company_id' => $data['company_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'system_prompt' => $data['system_prompt'],
            'model' => $data['model'] ?? 'gpt-4o-mini',
            'provider_id' => $data['provider_id'],
            'tools' => $data['tools'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function updateAgent(AiAgent $agent, array $data): AiAgent
    {
        $agent->update($data);
        return $agent->fresh();
    }

    public function runAgent(AiAgent $agent, string $input, ?array $context = null): array
    {
        $provider = $agent->provider;
        if (!$provider || !$provider->is_active) {
            throw new \RuntimeException('AI Provider tidak aktif atau tidak ditemukan.');
        }

        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = decrypt($provider->api_key_encrypted);
        $model = $agent->model ?: $provider->default_model ?: 'gpt-4o-mini';

        $messages = [
            ['role' => 'system', 'content' => $agent->system_prompt],
        ];

        if ($context) {
            $messages[] = ['role' => 'system', 'content' => 'KONTEKS: ' . json_encode($context, JSON_UNESCAPED_UNICODE)];
        }

        $messages[] = ['role' => 'user', 'content' => $input];

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 4000,
        ];

        if (!empty($agent->tools)) {
            $payload['tools'] = $this->formatTools($agent->tools);
            $payload['tool_choice'] = 'auto';
        }

        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(120)
                ->post("{$baseUrl}/v1/chat/completions", $payload);

            $elapsed = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $body = $response->json();
                $choice = $body['choices'][0] ?? [];
                $message = $choice['message'] ?? [];

                $result = [
                    'success' => true,
                    'content' => $message['content'] ?? null,
                    'tool_calls' => $message['tool_calls'] ?? null,
                    'model' => $body['model'] ?? $model,
                    'usage' => $body['usage'] ?? null,
                    'elapsed_ms' => $elapsed,
                ];

                Log::info('AiAgent run success', [
                    'agent' => $agent->name,
                    'model' => $model,
                    'elapsed_ms' => $elapsed,
                ]);

                return $result;
            }

            Log::error('AiAgent LLM error', [
                'agent' => $agent->name,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'content' => 'Maaf, terjadi kesalahan saat memanggil AI agent.',
                'error' => $response->body(),
                'elapsed_ms' => $elapsed,
            ];
        } catch (ConnectionException $e) {
            Log::error('AiAgent connection error: ' . $e->getMessage());
            return [
                'success' => false,
                'content' => 'Tidak dapat terhubung ke AI provider. Periksa koneksi dan coba lagi.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function runAgentStreaming(AiAgent $agent, string $input, ?array $context = null): callable
    {
        $provider = $agent->provider;
        if (!$provider || !$provider->is_active) {
            throw new \RuntimeException('AI Provider tidak aktif atau tidak ditemukan.');
        }

        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = decrypt($provider->api_key_encrypted);
        $model = $agent->model ?: $provider->default_model ?: 'gpt-4o-mini';

        $messages = [
            ['role' => 'system', 'content' => $agent->system_prompt],
        ];

        if ($context) {
            $messages[] = ['role' => 'system', 'content' => 'KONTEKS: ' . json_encode($context, JSON_UNESCAPED_UNICODE)];
        }

        $messages[] = ['role' => 'user', 'content' => $input];

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 4000,
            'stream' => true,
        ];

        return function () use ($baseUrl, $apiKey, $payload) {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', "{$baseUrl}/v1/chat/completions", [
                'headers' => [
                    'Authorization' => "Bearer {$apiKey}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'stream' => true,
            ]);

            $buffer = '';
            $body = $response->getBody();

            while (!$body->eof()) {
                $line = $this->readLine($body);
                if (str_starts_with($line, 'data: ')) {
                    $data = substr($line, 6);
                    if ($data === '[DONE]') break;
                    $chunk = json_decode($data, true);
                    $delta = $chunk['choices'][0]['delta'] ?? [];
                    if (isset($delta['content'])) {
                        yield $delta['content'];
                    }
                }
            }
        };
    }

    protected function readLine($stream): string
    {
        $buffer = '';
        while (!$stream->eof()) {
            $char = $stream->read(1);
            if ($char === "\n") break;
            $buffer .= $char;
        }
        return $buffer;
    }

    public function createWorkflow(array $data): AiWorkflow
    {
        return AiWorkflow::create([
            'company_id' => $data['company_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'trigger_event' => $data['trigger_event'],
            'agent_id' => $data['agent_id'],
            'steps' => $data['steps'] ?? [],
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    public function executeWorkflow(AiWorkflow $workflow, array $context = []): array
    {
        $steps = $workflow->steps ?? [];
        $results = [];
        $currentContext = $context;

        foreach ($steps as $index => $step) {
            $stepAgentId = $step['agent_id'] ?? $workflow->agent_id;
            $agent = AiAgent::find($stepAgentId);

            if (!$agent) {
                $results[] = [
                    'step' => $index + 1,
                    'name' => $step['name'] ?? "Langkah " . ($index + 1),
                    'success' => false,
                    'error' => 'Agent tidak ditemukan.',
                ];
                continue;
            }

            $prompt = $step['prompt'] ?? '';
            foreach ($currentContext as $key => $value) {
                $prompt = str_replace("{{$key}}", is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE), $prompt);
            }

            $result = $this->runAgent($agent, $prompt, $currentContext);

            if ($result['success'] && $result['content']) {
                $outputKey = $step['output_key'] ?? null;
                if ($outputKey) {
                    $currentContext[$outputKey] = $result['content'];
                }
            }

            $results[] = [
                'step' => $index + 1,
                'name' => $step['name'] ?? "Langkah " . ($index + 1),
                'success' => $result['success'],
                'content' => $result['content'] ?? null,
                'error' => $result['error'] ?? null,
                'agent' => $agent->name,
            ];

            if (!$result['success'] && ($step['halt_on_error'] ?? false)) {
                break;
            }
        }

        return [
            'success' => true,
            'workflow' => $workflow->name,
            'total_steps' => count($steps),
            'completed_steps' => count($results),
            'results' => $results,
            'final_context' => $currentContext,
        ];
    }

    public function getAvailableAgents(int $companyId): array
    {
        return AiAgent::where('company_id', $companyId)
            ->active()
            ->with('provider')
            ->get()
            ->toArray();
    }

    public function getAvailableWorkflows(int $companyId): array
    {
        return AiWorkflow::where('company_id', $companyId)
            ->active()
            ->with('agent')
            ->get()
            ->toArray();
    }

    protected function formatTools(array $tools): array
    {
        $formatted = [];
        foreach ($tools as $tool) {
            if (is_string($tool)) {
                $formatted[] = [
                    'type' => 'function',
                    'function' => [
                        'name' => $tool,
                        'description' => '',
                        'parameters' => ['type' => 'object', 'properties' => (object) []],
                    ],
                ];
            } elseif (is_array($tool)) {
                $formatted[] = [
                    'type' => 'function',
                    'function' => $tool,
                ];
            }
        }
        return $formatted;
    }
}
