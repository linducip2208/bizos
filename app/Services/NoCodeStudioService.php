<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Illuminate\Support\Str;

class NoCodeStudioService extends WorkflowAutomationService
{
    public function executeParallelBranch(array $actions, array $context): array
    {
        $results = [];

        foreach ($actions as $action) {
            try {
                $results[] = $this->executeAction($action, $context);
            } catch (\Throwable $e) {
                $results[] = [
                    'type' => 'parallel_action',
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'type' => 'parallel_branch',
            'status' => 'completed',
            'branches' => count($actions),
            'results' => $results,
        ];
    }

    public function executeConditionalBranch(
        array $condition,
        array $trueActions,
        array $falseActions,
        array $context
    ): mixed {
        $conditionMet = $this->checkCondition($condition, $context);
        $actions = $conditionMet ? $trueActions : $falseActions;

        $results = [];
        foreach ($actions as $action) {
            try {
                $results[] = $this->executeAction($action, $context);
            } catch (\Throwable $e) {
                $results[] = [
                    'type' => 'conditional_action',
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'type' => 'conditional_branch',
            'condition_met' => $conditionMet,
            'branch' => $conditionMet ? 'true' : 'false',
            'results' => $results,
        ];
    }

    public function executeLoop(array $config, array $actions, array $context): array
    {
        $type = $config['type'] ?? 'fixed';
        $maxIterations = $config['max_iterations'] ?? 100;
        $results = [];
        $iteration = 0;

        match ($type) {
            'fixed' => $this->executeFixedLoop((int) ($config['count'] ?? 0), $actions, $context, $results, $iteration),
            'while' => $this->executeWhileLoop($config['condition'] ?? [], $actions, $context, $results, $iteration, $maxIterations),
            'for_each' => $this->executeForEachLoop($config['items'] ?? [], $actions, $context, $results, $iteration),
            default => null,
        };

        return [
            'type' => 'loop',
            'loop_type' => $type,
            'total_iterations' => $iteration,
            'results' => $results,
        ];
    }

    protected function executeFixedLoop(int $count, array $actions, array $context, array &$results, int &$iteration): void
    {
        for ($i = 0; $i < $count; $i++) {
            $loopContext = array_merge($context, ['loop_index' => $i, 'loop_total' => $count]);
            foreach ($actions as $action) {
                $results[] = $this->executeAction($action, $loopContext);
            }
            $iteration++;
        }
    }

    protected function executeWhileLoop(array $condition, array $actions, array $context, array &$results, int &$iteration, int $maxIterations): void
    {
        while ($this->checkCondition($condition, $context) && $iteration < $maxIterations) {
            $loopContext = array_merge($context, ['loop_index' => $iteration]);
            foreach ($actions as $action) {
                $results[] = $this->executeAction($action, $loopContext);
            }
            $iteration++;
        }
    }

    protected function executeForEachLoop(array $items, array $actions, array $context, array &$results, int &$iteration): void
    {
        foreach ($items as $index => $item) {
            if (is_array($item)) {
                $context = array_merge($context, $item);
            }
            $loopContext = array_merge($context, ['loop_index' => $index, 'loop_item' => $item, 'loop_total' => count($items)]);
            foreach ($actions as $action) {
                $results[] = $this->executeAction($action, $loopContext);
            }
            $iteration++;
        }
    }

    public function executeDelay(int $minutes): void
    {
        sleep(min($minutes * 60, 300));
    }

    public function transformData(array $data, array $operations): array
    {
        foreach ($operations as $op) {
            $type = $op['type'] ?? null;

            $data = match ($type) {
                'filter' => array_filter($data, function ($item) use ($op) {
                    $field = $op['field'] ?? null;
                    $operator = $op['operator'] ?? '=';
                    $value = $op['value'] ?? null;

                    $actual = is_array($item) ? ($item[$field] ?? null) : ($item->$field ?? null);

                    return match ($operator) {
                        '=' => $actual == $value,
                        '!=' => $actual != $value,
                        '>' => (float) $actual > (float) $value,
                        '<' => (float) $actual < (float) $value,
                        '>=' => (float) $actual >= (float) $value,
                        '<=' => (float) $actual <= (float) $value,
                        'contains' => str_contains((string) $actual, (string) $value),
                        default => true,
                    };
                }),
                'map' => array_map(function ($item) use ($op) {
                    if (isset($op['field'], $op['expression'])) {
                        $item[$op['field']] = $op['expression'];
                    }
                    return $item;
                }, $data),
                'aggregate' => $this->aggregateData($data, $op['method'] ?? 'sum', $op['field'] ?? null),
                'sort' => $this->sortData($data, $op['field'] ?? null, $op['direction'] ?? 'asc'),
                'limit' => array_slice($data, 0, (int) ($op['count'] ?? 10)),
                'group' => $this->groupData($data, $op['field'] ?? null),
                default => $data,
            };
        }

        return array_values($data);
    }

    protected function aggregateData(array $data, string $method, ?string $field): array
    {
        $values = array_map(function ($item) use ($field) {
            return (float) (is_array($item) ? ($item[$field] ?? 0) : ($item->$field ?? 0));
        }, $data);

        $result = match ($method) {
            'sum' => array_sum($values),
            'avg' => count($values) > 0 ? array_sum($values) / count($values) : 0,
            'count' => count($values),
            'min' => $values ? min($values) : 0,
            'max' => $values ? max($values) : 0,
            default => array_sum($values),
        };

        return ['aggregate' => $method, 'field' => $field, 'value' => $result];
    }

    protected function sortData(array $data, ?string $field, string $direction): array
    {
        usort($data, function ($a, $b) use ($field, $direction) {
            $valA = is_array($a) ? ($a[$field] ?? 0) : ($a->$field ?? 0);
            $valB = is_array($b) ? ($b[$field] ?? 0) : ($b->$field ?? 0);
            $cmp = $valA <=> $valB;
            return $direction === 'desc' ? -$cmp : $cmp;
        });

        return $data;
    }

    protected function groupData(array $data, ?string $field): array
    {
        if (!$field) {
            return $data;
        }

        $groups = [];
        foreach ($data as $item) {
            $key = is_array($item) ? ($item[$field] ?? 'unknown') : ($item->$field ?? 'unknown');
            $groups[$key][] = $item;
        }

        return $groups;
    }

    protected array $variableStore = [];

    public function setVariable(string $key, $value): void
    {
        $this->variableStore[$key] = $value;
    }

    public function getVariable(string $key): mixed
    {
        return $this->variableStore[$key] ?? null;
    }

    public function executeWithErrorHandling(array $actions, array $fallbackActions, array $context): array
    {
        try {
            $results = [];
            foreach ($actions as $action) {
                $results[] = $this->executeAction($action, $context);
            }
            return [
                'status' => 'success',
                'results' => $results,
                'fallback_used' => false,
            ];
        } catch (\Throwable $e) {
            $fallbackResults = [];
            foreach ($fallbackActions as $action) {
                try {
                    $fallbackResults[] = $this->executeAction($action, $context);
                } catch (\Throwable $fbErr) {
                    $fallbackResults[] = ['status' => 'fallback_error', 'error' => $fbErr->getMessage()];
                }
            }
            return [
                'status' => 'fallback',
                'error' => $e->getMessage(),
                'results' => $fallbackResults,
                'fallback_used' => true,
            ];
        }
    }

    public function registerWebhookTrigger(string $workflowId): string
    {
        $workflow = Workflow::findOrFail($workflowId);
        $token = Str::random(40);

        $workflow->update([
            'webhook_url' => $token,
            'workflow_type' => 'studio',
        ]);

        return config('app.url') . '/api/workflows/webhook/' . $token;
    }

    public function handleIncomingWebhook(string $token, array $payload): array
    {
        $workflow = Workflow::where('webhook_url', $token)
            ->where('is_active', true)
            ->first();

        if (!$workflow) {
            return ['status' => 'error', 'message' => 'Webhook not found or inactive.'];
        }

        $this->execute($workflow, $payload);

        return ['status' => 'success', 'workflow_id' => $workflow->id];
    }

    public function registerScheduleTrigger(string $workflowId, string $cronExpression): void
    {
        $workflow = Workflow::findOrFail($workflowId);
        $workflow->update([
            'schedule_cron' => $cronExpression,
            'workflow_type' => 'studio',
        ]);
    }

    public function getStudioBlocks(): array
    {
        return [
            [
                'type' => 'trigger',
                'name' => 'Event Trigger',
                'icon' => 'heroicon-o-bolt',
                'color' => '#f59e0b',
                'trigger_events' => $this->getAvailableTriggers(),
                'configSchema' => [
                    ['name' => 'trigger_event', 'type' => 'select', 'label' => 'Trigger Event', 'required' => true],
                ],
            ],
            [
                'type' => 'trigger',
                'name' => 'Webhook Trigger',
                'icon' => 'heroicon-o-link',
                'color' => '#8b5cf6',
                'configSchema' => [
                    ['name' => 'generate_webhook', 'type' => 'button', 'label' => 'Generate Webhook URL'],
                ],
            ],
            [
                'type' => 'trigger',
                'name' => 'Schedule Trigger',
                'icon' => 'heroicon-o-clock',
                'color' => '#06b6d4',
                'configSchema' => [
                    ['name' => 'cron_expression', 'type' => 'text', 'label' => 'Cron Expression', 'placeholder' => '0 8 * * *'],
                ],
            ],
            [
                'type' => 'action',
                'name' => 'Kirim WA',
                'icon' => 'heroicon-o-chat-bubble-left',
                'color' => '#22c55e',
                'configSchema' => [
                    ['name' => 'to', 'type' => 'text', 'label' => 'Nomor Tujuan'],
                    ['name' => 'message', 'type' => 'textarea', 'label' => 'Pesan'],
                ],
            ],
            [
                'type' => 'action',
                'name' => 'Kirim Email',
                'icon' => 'heroicon-o-envelope',
                'color' => '#3b82f6',
                'configSchema' => [
                    ['name' => 'to', 'type' => 'text', 'label' => 'Email Tujuan'],
                    ['name' => 'subject', 'type' => 'text', 'label' => 'Subjek'],
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Isi Email'],
                ],
            ],
            [
                'type' => 'action',
                'name' => 'Kirim Notifikasi',
                'icon' => 'heroicon-o-bell',
                'color' => '#f97316',
                'configSchema' => [
                    ['name' => 'user_id', 'type' => 'text', 'label' => 'User ID'],
                    ['name' => 'title', 'type' => 'text', 'label' => 'Judul'],
                    ['name' => 'body', 'type' => 'textarea', 'label' => 'Isi'],
                ],
            ],
            [
                'type' => 'action',
                'name' => 'Buat Tugas',
                'icon' => 'heroicon-o-clipboard-document-check',
                'color' => '#6366f1',
                'configSchema' => [
                    ['name' => 'project_id', 'type' => 'number', 'label' => 'Project ID'],
                    ['name' => 'title', 'type' => 'text', 'label' => 'Judul Tugas'],
                    ['name' => 'assigned_to', 'type' => 'text', 'label' => 'Assign ke Employee ID'],
                ],
            ],
            [
                'type' => 'action',
                'name' => 'Update Record',
                'icon' => 'heroicon-o-pencil-square',
                'color' => '#8b5cf6',
                'configSchema' => [
                    ['name' => 'model', 'type' => 'text', 'label' => 'Model Class (full)'],
                    ['name' => 'record_id', 'type' => 'text', 'label' => 'Record ID'],
                    ['name' => 'fields', 'type' => 'keyvalue', 'label' => 'Fields'],
                ],
            ],
            [
                'type' => 'action',
                'name' => 'Buat Record',
                'icon' => 'heroicon-o-document-plus',
                'color' => '#10b981',
                'configSchema' => [
                    ['name' => 'model', 'type' => 'text', 'label' => 'Model Class (full)'],
                    ['name' => 'fields', 'type' => 'keyvalue', 'label' => 'Fields'],
                ],
            ],
            [
                'type' => 'action',
                'name' => 'Webhook',
                'icon' => 'heroicon-o-globe-alt',
                'color' => '#ec4899',
                'configSchema' => [
                    ['name' => 'url', 'type' => 'text', 'label' => 'URL'],
                    ['name' => 'payload', 'type' => 'keyvalue', 'label' => 'Payload'],
                ],
            ],
            [
                'type' => 'logic',
                'name' => 'IF / ELSE',
                'icon' => 'heroicon-o-arrows-right-left',
                'color' => '#f59e0b',
                'configSchema' => [
                    ['name' => 'condition', 'type' => 'condition', 'label' => 'Kondisi'],
                ],
            ],
            [
                'type' => 'logic',
                'name' => 'Loop',
                'icon' => 'heroicon-o-arrow-path',
                'color' => '#06b6d4',
                'configSchema' => [
                    ['name' => 'type', 'type' => 'select', 'label' => 'Tipe Loop', 'options' => ['fixed' => 'Fixed', 'while' => 'While', 'for_each' => 'For Each']],
                    ['name' => 'count', 'type' => 'number', 'label' => 'Jumlah (Fixed)'],
                    ['name' => 'condition', 'type' => 'condition', 'label' => 'Kondisi (While)'],
                ],
            ],
            [
                'type' => 'logic',
                'name' => 'Delay',
                'icon' => 'heroicon-o-clock',
                'color' => '#64748b',
                'configSchema' => [
                    ['name' => 'minutes', 'type' => 'number', 'label' => 'Menit'],
                ],
            ],
            [
                'type' => 'logic',
                'name' => 'Try / Catch',
                'icon' => 'heroicon-o-shield-exclamation',
                'color' => '#ef4444',
                'configSchema' => [],
            ],
            [
                'type' => 'data',
                'name' => 'Transform Data',
                'icon' => 'heroicon-o-adjustments-horizontal',
                'color' => '#6366f1',
                'configSchema' => [
                    ['name' => 'operations', 'type' => 'json', 'label' => 'Operations'],
                ],
            ],
            [
                'type' => 'data',
                'name' => 'Set Variable',
                'icon' => 'heroicon-o-variable',
                'color' => '#8b5cf6',
                'configSchema' => [
                    ['name' => 'key', 'type' => 'text', 'label' => 'Nama Variabel'],
                    ['name' => 'value', 'type' => 'text', 'label' => 'Nilai'],
                ],
            ],
        ];
    }

    public function validateFlow(array $nodes, array $edges): array
    {
        $errors = [];

        if (empty($nodes)) {
            $errors[] = 'Flow harus memiliki minimal 1 node.';
        }

        $hasTrigger = false;
        $hasAction = false;

        foreach ($nodes as $node) {
            $type = $node['type'] ?? ($node['data']['type'] ?? null);
            $blockType = $node['block_type'] ?? ($node['data']['block_type'] ?? null);

            if ($blockType === 'trigger') {
                $hasTrigger = true;
            }
            if ($blockType === 'action' || $type === 'action') {
                $hasAction = true;
            }
        }

        if (!$hasTrigger) {
            $errors[] = 'Flow harus memiliki minimal 1 trigger.';
        }
        if (!$hasAction) {
            $errors[] = 'Flow harus memiliki minimal 1 action.';
        }

        $nodeIds = [];
        foreach ($nodes as $node) {
            $id = $node['id'] ?? null;
            if (!$id) {
                $errors[] = 'Setiap node harus memiliki ID.';
            } elseif (in_array($id, $nodeIds)) {
                $errors[] = "Duplicate node ID: {$id}.";
            } else {
                $nodeIds[] = $id;
            }
        }

        foreach ($edges as $edge) {
            $source = $edge['source'] ?? null;
            $target = $edge['target'] ?? null;
            if ($source && !in_array($source, $nodeIds)) {
                $errors[] = "Edge source '{$source}' tidak terhubung ke node manapun.";
            }
            if ($target && !in_array($target, $nodeIds)) {
                $errors[] = "Edge target '{$target}' tidak terhubung ke node manapun.";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function executeFlow(string $workflowId, array $input = []): array
    {
        $workflow = Workflow::findOrFail($workflowId);
        $start = microtime(true);

        $steps = [];
        $context = $input;

        try {
            $studioConfig = $workflow->studio_config ?? [];
            $nodes = $studioConfig['nodes'] ?? [];
            $edges = $studioConfig['edges'] ?? [];

            $executionOrder = $this->topologicalSort($nodes, $edges);

            foreach ($executionOrder as $nodeId) {
                $node = $this->findNode($nodes, $nodeId);
                if (!$node) {
                    continue;
                }

                $nodeStart = microtime(true);
                $blockType = $node['block_type'] ?? ($node['data']['block_type'] ?? 'action');
                $config = $node['config'] ?? ($node['data']['config'] ?? []);

                $output = $this->executeNode($node, $context);

                if (isset($output['context_updates'])) {
                    $context = array_merge($context, $output['context_updates']);
                }

                $stepDuration = (int) ((microtime(true) - $nodeStart) * 1000);

                $steps[] = [
                    'node_id' => $nodeId,
                    'node_name' => $node['label'] ?? $node['data']['label'] ?? $nodeId,
                    'block_type' => $blockType,
                    'status' => 'success',
                    'output' => $output,
                    'duration_ms' => $stepDuration,
                ];
            }

            $duration = (int) ((microtime(true) - $start) * 1000);

            WorkflowExecution::create([
                'workflow_id' => $workflow->id,
                'trigger_event' => $workflow->trigger_event ?? 'studio_manual',
                'input_context' => $input,
                'output_result' => $steps,
                'status' => 'success',
                'duration_ms' => $duration,
            ]);

            $workflow->increment('run_count');
            $workflow->update(['last_run_at' => now()]);

            return [
                'status' => 'success',
                'workflow_id' => $workflowId,
                'steps' => $steps,
                'total_duration_ms' => $duration,
                'final_output' => end($steps)['output'] ?? null,
            ];

        } catch (\Throwable $e) {
            $duration = (int) ((microtime(true) - $start) * 1000);

            WorkflowExecution::create([
                'workflow_id' => $workflow->id,
                'trigger_event' => $workflow->trigger_event ?? 'studio_manual',
                'input_context' => $input,
                'output_result' => $steps,
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'duration_ms' => $duration,
            ]);

            return [
                'status' => 'error',
                'workflow_id' => $workflowId,
                'error' => $e->getMessage(),
                'steps' => $steps,
                'total_duration_ms' => $duration,
            ];
        }
    }

    protected function executeNode(array $node, array $context): array
    {
        $blockType = $node['block_type'] ?? $node['data']['block_type'] ?? 'action';
        $blockName = $node['block_name'] ?? $node['data']['block_name'] ?? 'unknown';
        $config = $node['config'] ?? $node['data']['config'] ?? [];

        return match (true) {
            $blockName === 'IF / ELSE' || $blockType === 'logic' && str_contains($blockName ?? '', 'IF') => $this->handleConditionalNode($config, $context, $node),
            $blockName === 'Loop' || ($blockType === 'logic' && str_contains($blockName, 'Loop')) => $this->handleLoopNode($config, $context, $node),
            $blockName === 'Delay' => $this->handleDelayNode($config),
            $blockName === 'Transform Data' => $this->handleTransformNode($config, $context),
            $blockName === 'Set Variable' => $this->handleSetVariableNode($config),
            default => $this->handleActionNode($blockName, $config, $context),
        };
    }

    protected function handleConditionalNode(array $config, array $context, array $node): array
    {
        $condition = $config['condition'] ?? [];
        $trueNodes = $node['true_branch'] ?? [];
        $falseNodes = $node['false_branch'] ?? [];

        $result = $this->executeConditionalBranch(
            $condition,
            $trueNodes,
            $falseNodes,
            $context
        );

        return [
            'type' => 'conditional',
            'result' => $result,
        ];
    }

    protected function handleLoopNode(array $config, array $context, array $node): array
    {
        $actions = $node['loop_body'] ?? [];
        $result = $this->executeLoop($config, $actions, $context);

        return [
            'type' => 'loop',
            'result' => $result,
        ];
    }

    protected function handleDelayNode(array $config): array
    {
        $minutes = (int) ($config['minutes'] ?? 1);
        $this->executeDelay($minutes);

        return [
            'type' => 'delay',
            'delayed_minutes' => $minutes,
        ];
    }

    protected function handleTransformNode(array $config, array $context): array
    {
        $operations = $config['operations'] ?? [];
        $data = $context['data'] ?? $context;

        $result = $this->transformData($data, $operations);

        return [
            'type' => 'transform',
            'result' => $result,
            'context_updates' => ['data' => $result],
        ];
    }

    protected function handleSetVariableNode(array $config): array
    {
        $key = $config['key'] ?? null;
        $value = $config['value'] ?? null;

        if ($key) {
            $this->setVariable($key, $value);
        }

        return [
            'type' => 'set_variable',
            'key' => $key,
            'value' => $value,
            'context_updates' => [$key => $value],
        ];
    }

    protected function handleActionNode(string $blockName, array $config, array $context): array
    {
        $actionMapping = [
            'Kirim WA' => ['type' => 'send_wa', 'config' => $config],
            'Kirim Email' => ['type' => 'send_email', 'config' => $config],
            'Kirim Notifikasi' => ['type' => 'send_notification', 'config' => $config],
            'Buat Tugas' => ['type' => 'create_task', 'config' => $config],
            'Update Record' => ['type' => 'update_record', 'config' => $config],
            'Buat Record' => ['type' => 'create_record', 'config' => $config],
            'Webhook' => ['type' => 'webhook', 'config' => $config],
        ];

        $action = $actionMapping[$blockName] ?? ['type' => strtolower(str_replace(' ', '_', $blockName)), 'config' => $config];

        return $this->executeAction($action, $context);
    }

    protected function topologicalSort(array $nodes, array $edges): array
    {
        $nodeIds = array_column($nodes, 'id');
        $inDegree = array_fill_keys($nodeIds, 0);
        $adjacency = array_fill_keys($nodeIds, []);

        foreach ($edges as $edge) {
            $source = $edge['source'] ?? null;
            $target = $edge['target'] ?? null;

            if ($source && $target && in_array($source, $nodeIds) && in_array($target, $nodeIds)) {
                $adjacency[$source][] = $target;
                $inDegree[$target] = ($inDegree[$target] ?? 0) + 1;
            }
        }

        $queue = [];
        foreach ($inDegree as $id => $deg) {
            if ($deg === 0) {
                $queue[] = $id;
            }
        }

        $sorted = [];
        while (!empty($queue)) {
            $node = array_shift($queue);
            $sorted[] = $node;

            foreach ($adjacency[$node] ?? [] as $neighbor) {
                $inDegree[$neighbor]--;
                if ($inDegree[$neighbor] === 0) {
                    $queue[] = $neighbor;
                }
            }
        }

        return $sorted;
    }

    protected function findNode(array $nodes, string $nodeId): ?array
    {
        foreach ($nodes as $node) {
            if (($node['id'] ?? '') === $nodeId) {
                return $node;
            }
        }
        return null;
    }
}
