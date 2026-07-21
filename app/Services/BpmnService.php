<?php

namespace App\Services;

use App\Models\BpmnProcess;
use App\Models\BpmnProcessInstance;
use App\Models\BpmnTaskInstance;
use App\Models\BpmnProcessVariable;
use App\Models\BpmnExecutionLog;
use Illuminate\Support\Str;
use SimpleXMLElement;

class BpmnService
{
    protected array $elementTypes = [
        'startEvent' => 'Start Event',
        'endEvent' => 'End Event',
        'userTask' => 'User Task',
        'serviceTask' => 'Service Task',
        'scriptTask' => 'Script Task',
        'sendTask' => 'Send Task',
        'receiveTask' => 'Receive Task',
        'manualTask' => 'Manual Task',
        'businessRuleTask' => 'Business Rule Task',
        'exclusiveGateway' => 'Exclusive Gateway (XOR)',
        'inclusiveGateway' => 'Inclusive Gateway (OR)',
        'parallelGateway' => 'Parallel Gateway (AND)',
        'eventBasedGateway' => 'Event-Based Gateway',
        'complexGateway' => 'Complex Gateway',
        'intermediateCatchEvent' => 'Intermediate Catch Event',
        'intermediateThrowEvent' => 'Intermediate Throw Event',
        'boundaryEvent' => 'Boundary Event',
        'sequenceFlow' => 'Sequence Flow',
    ];

    public function createProcess(array $data): BpmnProcess
    {
        return BpmnProcess::create([
            'company_id' => $data['company_id'] ?? auth()->user()?->company_id,
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'description' => $data['description'] ?? null,
            'bpmn_xml' => $data['bpmn_xml'] ?? null,
            'diagram_svg' => $data['diagram_svg'] ?? null,
            'is_prebuilt' => $data['is_prebuilt'] ?? false,
            'is_active' => $data['is_active'] ?? true,
            'sla_hours' => $data['sla_hours'] ?? null,
        ]);
    }

    public function getBpmnElements(string $bpmnXml): array
    {
        if (empty(trim($bpmnXml))) {
            return ['pools' => [], 'lanes' => [], 'tasks' => [], 'events' => [], 'gateways' => [], 'flows' => [], 'error' => 'BPMN XML kosong'];
        }

        try {
            $xml = new SimpleXMLElement($bpmnXml);
            $xml->registerXPathNamespace('bpmn', 'http://www.omg.org/spec/BPMN/20100524/MODEL');
            $xml->registerXPathNamespace('bpmndi', 'http://www.omg.org/spec/BPMN/20100524/DI');

            $elements = [
                'pools' => $this->parseElements($xml, '//bpmn:participant', 'participant'),
                'lanes' => $this->parseElements($xml, '//bpmn:lane', 'lane'),
                'tasks' => $this->parseTasks($xml),
                'events' => $this->parseEvents($xml),
                'gateways' => $this->parseGateways($xml),
                'flows' => $this->parseFlows($xml),
            ];

            return $elements;
        } catch (\Throwable $e) {
            return [
                'pools' => [], 'lanes' => [], 'tasks' => [], 'events' => [],
                'gateways' => [], 'flows' => [],
                'error' => 'Gagal parsing BPMN XML: ' . $e->getMessage(),
            ];
        }
    }

    protected function parseElements(SimpleXMLElement $xml, string $xpath, string $type): array
    {
        $result = [];
        $nodes = $xml->xpath($xpath);

        if (!$nodes) return $result;

        foreach ($nodes as $node) {
            $result[] = [
                'id' => (string) $node['id'],
                'name' => (string) ($node['name'] ?? ''),
                'type' => $type,
                'process_ref' => (string) ($node['processRef'] ?? ''),
            ];
        }

        return $result;
    }

    protected function parseTasks(SimpleXMLElement $xml): array
    {
        $tasks = [];
        $taskTypes = ['userTask', 'serviceTask', 'scriptTask', 'sendTask', 'receiveTask', 'manualTask', 'businessRuleTask', 'task'];

        foreach ($taskTypes as $type) {
            $nodes = $xml->xpath("//bpmn:{$type}");
            if (!$nodes) continue;

            foreach ($nodes as $node) {
                $tasks[] = [
                    'id' => (string) $node['id'],
                    'name' => (string) ($node['name'] ?? ''),
                    'type' => $type,
                    'default_flow' => (string) ($node['default'] ?? ''),
                ];
            }
        }

        return $tasks;
    }

    protected function parseEvents(SimpleXMLElement $xml): array
    {
        $events = [];
        $eventTypes = ['startEvent', 'endEvent', 'intermediateCatchEvent', 'intermediateThrowEvent', 'boundaryEvent'];

        foreach ($eventTypes as $type) {
            $nodes = $xml->xpath("//bpmn:{$type}");
            if (!$nodes) continue;

            foreach ($nodes as $node) {
                $events[] = [
                    'id' => (string) $node['id'],
                    'name' => (string) ($node['name'] ?? ''),
                    'type' => $type,
                    'attached_to' => (string) ($node['attachedToRef'] ?? ''),
                ];
            }
        }

        return $events;
    }

    protected function parseGateways(SimpleXMLElement $xml): array
    {
        $gateways = [];
        $gatewayTypes = ['exclusiveGateway', 'inclusiveGateway', 'parallelGateway', 'eventBasedGateway', 'complexGateway'];

        foreach ($gatewayTypes as $type) {
            $nodes = $xml->xpath("//bpmn:{$type}");
            if (!$nodes) continue;

            foreach ($nodes as $node) {
                $gateways[] = [
                    'id' => (string) $node['id'],
                    'name' => (string) ($node['name'] ?? ''),
                    'type' => $type,
                    'default_flow' => (string) ($node['default'] ?? ''),
                ];
            }
        }

        return $gateways;
    }

    protected function parseFlows(SimpleXMLElement $xml): array
    {
        $flows = [];
        $nodes = $xml->xpath('//bpmn:sequenceFlow');

        if (!$nodes) return $flows;

        foreach ($nodes as $node) {
            $condition = null;
            $conditionExpr = $node->xpath('bpmn:conditionExpression');
            if ($conditionExpr && count($conditionExpr) > 0) {
                $condition = (string) $conditionExpr[0];
            }

            $flows[] = [
                'id' => (string) $node['id'],
                'name' => (string) ($node['name'] ?? ''),
                'source_ref' => (string) $node['sourceRef'],
                'target_ref' => (string) $node['targetRef'],
                'condition' => $condition,
            ];
        }

        return $flows;
    }

    public function startProcess(int $processId, array $variables = []): BpmnProcessInstance
    {
        $process = BpmnProcess::findOrFail($processId);

        if (empty(trim($process->bpmn_xml))) {
            throw new \RuntimeException('Proses "' . $process->name . '" tidak memiliki definisi BPMN.');
        }

        $elements = $this->getBpmnElements($process->bpmn_xml);

        if (!empty($elements['error'])) {
            throw new \RuntimeException('BPMN XML tidak valid: ' . $elements['error']);
        }

        $instanceCode = strtoupper(Str::random(3)) . '-' . date('Ymd') . '-' . str_pad(
            BpmnProcessInstance::where('process_id', $processId)->whereDate('created_at', today())->count() + 1,
            4, '0', STR_PAD_LEFT
        );

        $instance = BpmnProcessInstance::create([
            'process_id' => $processId,
            'company_id' => $process->company_id,
            'instance_code' => $instanceCode,
            'status' => 'running',
            'current_element_id' => null,
            'current_element_name' => 'Started',
            'process_variables' => $variables,
            'started_by' => auth()->id(),
            'started_at' => now(),
        ]);

        foreach ($variables as $key => $value) {
            BpmnProcessVariable::create([
                'process_instance_id' => $instance->id,
                'variable_name' => $key,
                'variable_value' => is_scalar($value) ? (string) $value : json_encode($value),
                'variable_type' => $this->detectVariableType($value),
            ]);
        }

        $this->logExecution($instance->id, null, 'Process Started', 'process_started', [
            'process_name' => $process->name,
            'variables' => $variables,
        ]);

        if (!empty($elements['events'])) {
            $startEvents = array_filter($elements['events'], fn($e) => $e['type'] === 'startEvent');
            if (!empty($startEvents)) {
                $startEvent = reset($startEvents);
                $instance->update([
                    'current_element_id' => $startEvent['id'],
                    'current_element_name' => $startEvent['name'] ?: 'Start',
                ]);

                $nextElements = $this->getNextElements($elements, $startEvent['id']);
                foreach ($nextElements as $next) {
                    $this->createTaskInstance($instance, $next, $elements, $variables, $process->sla_hours);
                }
            }
        }

        return $instance->fresh();
    }

    public function completeTask(int $taskInstanceId, array $output = []): void
    {
        $taskInstance = BpmnTaskInstance::with('processInstance')->findOrFail($taskInstanceId);

        if ($taskInstance->status === 'completed' || $taskInstance->status === 'cancelled') {
            throw new \RuntimeException('Task sudah ' . ($taskInstance->status === 'completed' ? 'selesai' : 'dibatalkan') . '.');
        }

        $taskInstance->update([
            'status' => 'completed',
            'output_variables' => $output,
            'completed_at' => now(),
        ]);

        $duration = $taskInstance->started_at
            ? now()->diffInSeconds($taskInstance->started_at)
            : 0;

        $this->logExecution(
            $taskInstance->process_instance_id,
            $taskInstance->element_id,
            $taskInstance->task_name,
            'task_completed',
            ['output' => $output],
            auth()->id(),
            $duration
        );

        $process = $taskInstance->processInstance->process;
        if (empty(trim($process->bpmn_xml))) return;

        $elements = $this->getBpmnElements($process->bpmn_xml);
        if (!empty($elements['error'])) return;

        $instance = $taskInstance->processInstance;
        $allVars = $this->getProcessVariables($instance->id);

        if (!empty($output)) {
            foreach ($output as $key => $value) {
                $this->setProcessVariable($instance->id, $key, $value);
            }
            $allVars = array_merge($allVars, $output);
        }

        $nextElements = $this->getNextElements($elements, $taskInstance->element_id);

        foreach ($nextElements as $next) {
            if (str_contains($next['type'], 'Gateway')) {
                $flowId = $this->evaluateGatewayFlow($next, $elements, $allVars);

                $this->logExecution(
                    $instance->id,
                    $next['id'],
                    $next['name'],
                    'gateway_evaluated',
                    ['selected_flow' => $flowId, 'variables' => $allVars]
                );

                if ($flowId) {
                    $targetElements = array_filter($elements['flows'], fn($f) => $f['id'] === $flowId);
                    foreach ($targetElements as $flow) {
                        $target = $this->findElementById($elements, $flow['target_ref']);
                        if ($target) {
                            if ($target['type'] === 'endEvent') {
                                $this->completeProcessInstance($instance);
                            } else {
                                $this->createTaskInstance($instance, $target, $elements, $allVars, $process->sla_hours);
                            }
                        }
                    }
                }
            } elseif ($next['type'] === 'endEvent') {
                $this->completeProcessInstance($instance);
            } else {
                $this->createTaskInstance($instance, $next, $elements, $allVars, $process->sla_hours);
            }
        }

        $allTaskInstances = BpmnTaskInstance::where('process_instance_id', $instance->id)->get();
        $allCompleted = $allTaskInstances->every(fn($t) => $t->status === 'completed' || $t->status === 'cancelled');

        if ($allCompleted && $instance->status === 'running') {
            $this->completeProcessInstance($instance);
        }
    }

    protected function completeProcessInstance(BpmnProcessInstance $instance): void
    {
        $instance->update([
            'status' => 'completed',
            'current_element_id' => null,
            'current_element_name' => 'Completed',
            'completed_at' => now(),
        ]);

        $this->logExecution($instance->id, null, 'Process Completed', 'process_completed', [
            'duration' => $instance->started_at ? $instance->started_at->diffForHumans($instance->completed_at, true) : 'N/A',
        ]);
    }

    protected function createTaskInstance(BpmnProcessInstance $instance, array $element, array $allElements, array $variables, ?string $defaultSlaHours): ?BpmnTaskInstance
    {
        if (in_array($element['type'], ['startEvent', 'endEvent'])) return null;

        $taskType = 'user_task';
        if (str_contains($element['type'], 'Task')) {
            $taskType = str_replace('Task', '_task', $element['type']);
            $taskType = Str::snake($taskType);
        }

        $taskInstance = BpmnTaskInstance::create([
            'process_instance_id' => $instance->id,
            'element_id' => $element['id'],
            'task_name' => $element['name'] ?: $element['id'],
            'type' => $taskType,
            'status' => 'pending',
            'gateway_type' => null,
            'input_variables' => $variables,
            'sla_hours' => $defaultSlaHours ? (float) $defaultSlaHours : null,
            'priority' => 0,
            'sla_deadline' => $defaultSlaHours ? now()->addHours((float) $defaultSlaHours) : null,
        ]);

        $instance->update([
            'current_element_id' => $element['id'],
            'current_element_name' => $element['name'] ?: $element['id'],
        ]);

        $this->logExecution($instance->id, $element['id'], $element['name'] ?? $element['id'], 'task_started', [
            'type' => $taskType,
            'sla_hours' => $defaultSlaHours,
        ]);

        if ($taskType === 'service_task' || $taskType === 'script_task') {
            $taskInstance->update([
                'status' => 'completed',
                'completed_at' => now(),
                'output_variables' => ['auto_completed' => true],
            ]);

            $this->completeTask($taskInstance->id, ['auto_completed' => true]);
        }

        return $taskInstance;
    }

    protected function getNextElements(array $elements, string $elementId): array
    {
        $nextElements = [];
        $outgoingFlows = array_filter($elements['flows'], fn($f) => $f['source_ref'] === $elementId);

        foreach ($outgoingFlows as $flow) {
            $target = $this->findElementById($elements, $flow['target_ref']);
            if ($target) {
                $target['incoming_flow_id'] = $flow['id'];
                $target['incoming_flow_condition'] = $flow['condition'];
                $nextElements[] = $target;
            }
        }

        return $nextElements;
    }

    protected function findElementById(array $elements, string $id): ?array
    {
        foreach (['tasks', 'events', 'gateways'] as $category) {
            foreach ($elements[$category] ?? [] as $el) {
                if ($el['id'] === $id) return $el;
            }
        }
        return null;
    }

    public function evaluateGatewayFlow(array $gateway, array $elements, array $variables): ?string
    {
        $gatewayType = str_replace('Gateway', '', $gateway['type']);
        $outgoingFlows = array_filter($elements['flows'], fn($f) => $f['source_ref'] === $gateway['id']);

        return match ($gatewayType) {
            'exclusive' => $this->evaluateExclusiveGateway($outgoingFlows, $variables, $gateway),
            'inclusive' => $this->evaluateInclusiveGateway($outgoingFlows, $variables),
            'parallel' => $this->evaluateParallelGateway($outgoingFlows),
            'eventBased' => $this->evaluateEventBasedGateway($outgoingFlows, $variables),
            default => !empty($outgoingFlows) ? reset($outgoingFlows)['id'] : null,
        };
    }

    protected function evaluateExclusiveGateway(array $flows, array $variables, array $gateway): ?string
    {
        foreach ($flows as $flow) {
            if (!empty($flow['condition'])) {
                if ($this->evaluateConditionExpression($flow['condition'], $variables)) {
                    return $flow['id'];
                }
            }
        }

        if (!empty($gateway['default_flow'])) {
            return $gateway['default_flow'];
        }

        return !empty($flows) ? reset($flows)['id'] : null;
    }

    protected function evaluateInclusiveGateway(array $flows, array $variables): ?string
    {
        foreach ($flows as $flow) {
            if (empty($flow['condition']) || $this->evaluateConditionExpression($flow['condition'], $variables)) {
                return $flow['id'];
            }
        }

        return !empty($flows) ? reset($flows)['id'] : null;
    }

    protected function evaluateParallelGateway(array $flows): ?string
    {
        return !empty($flows) ? reset($flows)['id'] : null;
    }

    protected function evaluateEventBasedGateway(array $flows, array $variables): ?string
    {
        return !empty($flows) ? reset($flows)['id'] : null;
    }

    protected function evaluateConditionExpression(string $condition, array $variables): bool
    {
        $condition = trim($condition);

        if (empty($condition)) return true;

        if (preg_match('/^\$\{(.+)\}$/', $condition, $matches)) {
            $condition = trim($matches[1]);
        }

        foreach ($variables as $key => $value) {
            $condition = str_replace($key, var_export($value, true), $condition);
        }

        try {
            return (bool) eval("return ({$condition});");
        } catch (\Throwable $e) {
            \Log::warning('BPMN condition evaluation failed: ' . $e->getMessage() . ' | Condition: ' . $condition);
        }

        return false;
    }

    public function signalEvent(int $processInstanceId, string $eventName): void
    {
        $instance = BpmnProcessInstance::findOrFail($processInstanceId);
        $process = $instance->process;

        if (empty(trim($process->bpmn_xml))) return;

        $elements = $this->getBpmnElements($process->bpmn_xml);

        $catchEvents = array_filter($elements['events'], function ($e) use ($eventName) {
            return $e['type'] === 'intermediateCatchEvent' && ($e['name'] === $eventName || empty($e['name']));
        });

        foreach ($catchEvents as $event) {
            $instance->update([
                'current_element_id' => $event['id'],
                'current_element_name' => $event['name'] ?: 'Event: ' . $eventName,
            ]);

            $this->logExecution($instance->id, $event['id'], $eventName, 'task_completed', [
                'event_type' => 'signal',
            ]);

            $nextElements = $this->getNextElements($elements, $event['id']);
            $allVars = $this->getProcessVariables($instance->id);
            foreach ($nextElements as $next) {
                $this->createTaskInstance($instance, $next, $elements, $allVars, $process->sla_hours);
            }
        }
    }

    public function getProcessVariables(int $processInstanceId): array
    {
        $vars = BpmnProcessVariable::where('process_instance_id', $processInstanceId)->get();
        $result = [];

        foreach ($vars as $var) {
            $result[$var->variable_name] = $this->castVariableValue($var->variable_value, $var->variable_type);
        }

        return $result;
    }

    public function setProcessVariable(int $processInstanceId, string $name, $value): void
    {
        BpmnProcessVariable::updateOrCreate(
            ['process_instance_id' => $processInstanceId, 'variable_name' => $name],
            [
                'variable_value' => is_scalar($value) ? (string) $value : json_encode($value),
                'variable_type' => $this->detectVariableType($value),
            ]
        );
    }

    protected function detectVariableType($value): string
    {
        return match (true) {
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_bool($value) => 'boolean',
            is_array($value) => 'json',
            is_object($value) => 'json',
            default => 'string',
        };
    }

    protected function castVariableValue(?string $value, string $type): mixed
    {
        if ($value === null) return null;

        return match ($type) {
            'integer' => (int) $value,
            'float' => (float) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    protected function logExecution(int $processInstanceId, ?string $elementId, ?string $elementName, string $eventType, array $eventData = [], ?int $actorId = null, ?float $duration = null): void
    {
        BpmnExecutionLog::create([
            'process_instance_id' => $processInstanceId,
            'element_id' => $elementId,
            'element_name' => $elementName,
            'event_type' => $eventType,
            'event_data' => $eventData,
            'actor_user_id' => $actorId ?? auth()->id(),
            'duration_seconds' => $duration,
            'logged_at' => now(),
        ]);
    }

    public function mineProcess(int $processId, string $period = '30 days'): array
    {
        $process = BpmnProcess::findOrFail($processId);
        $since = match ($period) {
            '7 days' => now()->subDays(7),
            '30 days' => now()->subDays(30),
            '90 days' => now()->subDays(90),
            '1 year' => now()->subYear(),
            default => now()->subDays(30),
        };

        $instances = BpmnProcessInstance::where('process_id', $processId)
            ->where('created_at', '>=', $since)
            ->with(['taskInstances', 'executionLogs'])
            ->get();

        $durations = [];
        foreach ($instances as $inst) {
            foreach ($inst->taskInstances as $task) {
                if ($task->started_at && $task->completed_at) {
                    $durations[$task->element_id][] = $task->started_at->diffInHours($task->completed_at);
                }
            }
        }

        $bottlenecks = [];
        $avgDurations = [];
        $taskCounts = [];

        foreach ($durations as $elementId => $times) {
            $avg = array_sum($times) / count($times);
            $median = $this->calculateMedian($times);
            $avgDurations[$elementId] = round($avg, 2);

            $taskName = BpmnTaskInstance::where('element_id', $elementId)
                ->whereHas('processInstance', fn($q) => $q->where('process_id', $processId))
                ->value('task_name') ?? $elementId;

            if ($avg > ($median * 2) && $avg > 1) {
                $bottlenecks[] = [
                    'element_id' => $elementId,
                    'task_name' => $taskName,
                    'avg_duration_hours' => round($avg, 2),
                    'median_duration_hours' => round($median, 2),
                    'instances' => count($times),
                    'severity' => $avg > ($median * 3) ? 'high' : 'medium',
                ];
            }
        }

        $avgTotalDuration = 0;
        $completedCount = $instances->where('status', 'completed')->count();
        if ($completedCount > 0) {
            $totalDuration = 0;
            foreach ($instances->where('status', 'completed') as $inst) {
                if ($inst->started_at && $inst->completed_at) {
                    $totalDuration += $inst->started_at->diffInHours($inst->completed_at);
                }
            }
            $avgTotalDuration = $totalDuration / $completedCount;
        }

        $taskStats = BpmnTaskInstance::whereHas('processInstance', fn($q) => $q->where('process_id', $processId))
            ->selectRaw('task_name, element_id, AVG(TIMESTAMPDIFF(MINUTE, started_at, completed_at)) as avg_minutes, COUNT(*) as count')
            ->whereNotNull('completed_at')
            ->groupBy('element_id', 'task_name')
            ->get()
            ->map(fn($t) => [
                'task_name' => $t->task_name,
                'element_id' => $t->element_id,
                'avg_minutes' => round((float) $t->avg_minutes, 1),
                'count' => $t->count,
            ])
            ->toArray();

        return [
            'total_instances' => $instances->count(),
            'completed_instances' => $completedCount,
            'running_instances' => $instances->where('status', 'running')->count(),
            'avg_total_duration_hours' => round($avgTotalDuration, 2),
            'bottlenecks' => $bottlenecks,
            'actual_flow' => $taskStats,
            'deviations' => $this->detectDeviations($process),
            'improvement_suggestions' => $this->generateImprovementSuggestions($bottlenecks, $taskStats),
        ];
    }

    public function getBottlenecks(int $processId): array
    {
        $mining = $this->mineProcess($processId, '30 days');
        return $mining['bottlenecks'] ?? [];
    }

    public function getConformance(int $processId): array
    {
        $process = BpmnProcess::findOrFail($processId);

        if (empty(trim($process->bpmn_xml))) {
            return ['conformance_percent' => 0, 'message' => 'Tidak ada BPMN XML untuk dibandingkan.'];
        }

        $elements = $this->getBpmnElements($process->bpmn_xml);
        if (!empty($elements['error'])) {
            return ['conformance_percent' => 0, 'message' => $elements['error']];
        }

        $designedTasks = array_column($elements['tasks'], 'id');
        $designedFlowPaths = array_map(fn($f) => $f['source_ref'] . '->' . $f['target_ref'], $elements['flows']);

        $executionLogs = BpmnExecutionLog::whereHas('processInstance', fn($q) => $q->where('process_id', $processId))
            ->where('event_type', 'task_completed')
            ->get();

        $executedElements = $executionLogs->pluck('element_id')->unique()->toArray();
        $matchingTasks = array_intersect($executedElements, $designedTasks);
        $unexpectedTasks = array_diff($executedElements, $designedTasks);
        $skippedTasks = array_diff($designedTasks, $executedElements);

        $conformancePercent = count($designedTasks) > 0
            ? round((count($matchingTasks) / count($designedTasks)) * 100, 1)
            : 100;

        return [
            'conformance_percent' => $conformancePercent,
            'designed_tasks_count' => count($designedTasks),
            'executed_tasks_count' => count($executedElements),
            'matching_tasks' => count($matchingTasks),
            'unexpected_executions' => array_values($unexpectedTasks),
            'skipped_tasks' => array_values($skippedTasks),
            'designed_flow_paths_count' => count($designedFlowPaths),
        ];
    }

    protected function calculateMedian(array $numbers): float
    {
        sort($numbers);
        $count = count($numbers);

        if ($count === 0) return 0;
        if ($count % 2 === 0) {
            return ($numbers[$count / 2 - 1] + $numbers[$count / 2]) / 2;
        }

        return $numbers[floor($count / 2)];
    }

    protected function detectDeviations(BpmnProcess $process): array
    {
        return [];
    }

    protected function generateImprovementSuggestions(array $bottlenecks, array $taskStats): array
    {
        $suggestions = [];

        foreach ($bottlenecks as $bn) {
            if ($bn['severity'] === 'high') {
                $suggestions[] = "Otomatisasi task '{$bn['task_name']}' — rata-rata {$bn['avg_duration_hours']} jam (severity: tinggi). Pertimbangkan service task atau script task untuk mengurangi waktu.";
            } else {
                $suggestions[] = "Evaluasi task '{$bn['task_name']}' — rata-rata {$bn['avg_duration_hours']} jam. Tambahkan SLA atau tentukan assignee default untuk mempercepat.";
            }
        }

        if (empty($suggestions)) {
            $suggestions[] = 'Proses berjalan efisien. Tidak ada bottleneck signifikan terdeteksi.';
        }

        return $suggestions;
    }

    public function getSlaStatus(int $processInstanceId): array
    {
        $tasks = BpmnTaskInstance::where('process_instance_id', $processInstanceId)
            ->where('status', '!=', 'completed')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('sla_deadline')
            ->get();

        $slaBreached = $tasks->filter(fn($t) => $t->sla_deadline?->isPast());

        return [
            'overall_sla_status' => $slaBreached->isEmpty() ? 'on_track' : 'breached',
            'breached_count' => $slaBreached->count(),
            'tasks' => $tasks->map(fn($t) => [
                'name' => $t->task_name,
                'sla_hours' => $t->sla_hours,
                'elapsed_hours' => $t->started_at ? round(now()->diffInHours($t->started_at), 1) : 0,
                'sla_deadline' => $t->sla_deadline?->toDateTimeString(),
                'status' => $t->sla_deadline?->isPast() ? 'breached' : 'on_track',
            ])->toArray(),
        ];
    }

    public function getPrebuiltProcesses(): array
    {
        $prebuilt = [
            [
                'name' => 'Employee Onboarding',
                'category' => 'HR',
                'description' => 'Proses onboarding karyawan baru: dari offer letter hingga siap bekerja.',
            ],
            [
                'name' => 'Purchase-to-Pay',
                'category' => 'Procurement',
                'description' => 'Proses procurement: purchase requisition → PO → goods receipt → invoice → payment.',
            ],
            [
                'name' => 'Order-to-Cash',
                'category' => 'Sales',
                'description' => 'Proses penjualan: sales order → delivery → invoice → payment received.',
            ],
            [
                'name' => 'Issue-to-Resolution',
                'category' => 'Helpdesk',
                'description' => 'Proses helpdesk: ticket submission → triage → assignment → resolution → review.',
            ],
            [
                'name' => 'Expense Reimbursement',
                'category' => 'Finance',
                'description' => 'Proses reimbursement: submission → manager approval → finance verification → payment.',
            ],
            [
                'name' => 'Leave Approval',
                'category' => 'HR',
                'description' => 'Proses cuti: employee request → manager approval → HR verification → leave granted.',
            ],
            [
                'name' => 'Asset Procurement',
                'category' => 'Finance',
                'description' => 'Proses pengadaan aset: request → budget check → approval → purchase → asset registration.',
            ],
        ];

        foreach ($prebuilt as $template) {
            $existing = BpmnProcess::where('name', $template['name'])
                ->where('is_prebuilt', true)
                ->exists();

            if (!$existing) {
                BpmnProcess::create([
                    'company_id' => 1,
                    'name' => $template['name'],
                    'category' => $template['category'],
                    'description' => $template['description'],
                    'is_prebuilt' => true,
                    'is_active' => true,
                    'version' => 1,
                ]);
            }
        }

        return $prebuilt;
    }

    public function getBpmnStats(): array
    {
        return [
            'total_processes' => BpmnProcess::count(),
            'active_processes' => BpmnProcess::where('is_active', true)->count(),
            'running_instances' => BpmnProcessInstance::where('status', 'running')->count(),
            'completed_instances' => BpmnProcessInstance::where('status', 'completed')->count(),
            'total_task_instances' => BpmnTaskInstance::count(),
            'pending_tasks' => BpmnTaskInstance::where('status', 'pending')->count(),
            'overdue_tasks' => BpmnTaskInstance::where('status', 'pending')
                ->whereNotNull('sla_deadline')
                ->where('sla_deadline', '<', now())
                ->count(),
            'today_completed' => BpmnTaskInstance::whereDate('completed_at', today())->count(),
        ];
    }
}
