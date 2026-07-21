<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Models\ApprovalRequest;
use App\Models\ApprovalAction;
use App\Models\ApprovalLevel;
use App\Models\BpmnProcessInstance;
use App\Models\BpmnTaskInstance;
use App\Models\BpmnProcessVariable;
use App\Models\BpmnExecutionLog;
use Illuminate\Support\Str;
use SimpleXMLElement;

class UnifiedWorkflowService
{
    public function createWorkflow(array $data): Workflow
    {
        return Workflow::create([
            'company_id' => $data['company_id'] ?? auth()->user()?->company_id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'workflow_type' => $data['workflow_type'] ?? Workflow::TYPE_SIMPLE,
            'trigger_event' => $data['trigger_event'] ?? null,
            'trigger_conditions' => $data['trigger_conditions'] ?? null,
            'actions' => $data['actions'] ?? [],
            'bpmn_xml' => $data['bpmn_xml'] ?? null,
            'bpmn_svg' => $data['bpmn_svg'] ?? null,
            'approval_levels' => $data['approval_levels'] ?? null,
            'sla_hours' => $data['sla_hours'] ?? null,
            'module' => $data['module'] ?? null,
            'min_approvers' => $data['min_approvers'] ?? 1,
            'category' => $data['category'] ?? null,
            'studio_config' => $data['studio_config'] ?? null,
            'enabled_blocks' => $data['enabled_blocks'] ?? null,
            'webhook_url' => $data['webhook_url'] ?? null,
            'schedule_cron' => $data['schedule_cron'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $data['created_by'] ?? (auth()->id() ? Employee::where('user_id', auth()->id())->value('id') : null),
        ]);
    }

    public function execute(Workflow $workflow, array $context): array
    {
        return match ($workflow->workflow_type) {
            Workflow::TYPE_SIMPLE => $this->executeSimple($workflow, $context),
            Workflow::TYPE_AUTOMATION => $this->executeAutomation($workflow, $context),
            Workflow::TYPE_APPROVAL => $this->executeApproval($workflow, $context),
            Workflow::TYPE_BPMN => $this->executeBpmn($workflow, $context),
            default => $this->executeSimple($workflow, $context),
        };
    }

    public function executeSimple(Workflow $workflow, array $context): array
    {
        $automationService = app(WorkflowAutomationService::class);
        $results = [];
        $start = microtime(true);

        try {
            foreach ($workflow->actions as $action) {
                $results[] = $automationService->executeAction($action, $context);
            }

            $durationMs = (int)((microtime(true) - $start) * 1000);
            $this->recordSuccess($workflow, $context, $results, $durationMs);

            return ['status' => 'success', 'results' => $results, 'duration_ms' => $durationMs];
        } catch (\Throwable $e) {
            $durationMs = (int)((microtime(true) - $start) * 1000);
            $this->recordError($workflow, $context, $results, $e->getMessage(), $durationMs);

            return ['status' => 'error', 'message' => $e->getMessage(), 'results' => $results, 'duration_ms' => $durationMs];
        }
    }

    public function executeAutomation(Workflow $workflow, array $context): array
    {
        $noCodeService = app(NoCodeStudioService::class);
        return $noCodeService->executeFlow($workflow->id, $context);
    }

    public function executeApproval(Workflow $workflow, array $context): array
    {
        if (empty($workflow->approval_levels)) {
            return ['status' => 'error', 'message' => 'Workflow approval tidak memiliki level persetujuan.'];
        }

        $module = $context['module'] ?? $workflow->module;
        $moduleId = $context['module_id'] ?? ($context['id'] ?? null);
        $title = $context['title'] ?? "Approval: {$workflow->name}";
        $requesterId = $context['requester_id'] ?? ($context['employee_id'] ?? 0);
        $notes = $context['notes'] ?? null;

        if (!$module || !$moduleId) {
            return ['status' => 'error', 'message' => 'Konteks module dan module_id diperlukan untuk approval.'];
        }

        $request = $this->submitForApproval($workflow, $module, (int)$moduleId, $title, (int)$requesterId, $notes);

        return [
            'status' => 'success',
            'approval_request_id' => $request->id,
            'current_level' => $request->current_level,
            'total_levels' => $request->total_levels,
        ];
    }

    public function submitForApproval(
        Workflow $workflow,
        string $module,
        int $moduleId,
        string $title,
        int $requesterId,
        ?string $notes = null
    ): ApprovalRequest {
        $levels = $workflow->approval_levels ?? [];

        if (empty($levels)) {
            throw new \RuntimeException("Workflow '{$workflow->name}' tidak memiliki level persetujuan.");
        }

        $totalLevels = max(array_column($levels, 'level'));

        $request = ApprovalRequest::create([
            'company_id' => $workflow->company_id,
            'workflow_id' => 0,
            'unified_workflow_id' => $workflow->id,
            'module' => $module,
            'module_id' => $moduleId,
            'title' => $title,
            'requester_id' => $requesterId,
            'status' => 'pending',
            'current_level' => 1,
            'total_levels' => $totalLevels,
            'submitted_at' => now(),
            'notes' => $notes,
        ]);

        return $request->fresh();
    }

    public function executeBpmn(Workflow $workflow, array $context): array
    {
        if (empty(trim($workflow->bpmn_xml ?? ''))) {
            return ['status' => 'error', 'message' => 'Workflow BPMN tidak memiliki definisi XML.'];
        }

        try {
            $parsed = $this->parseBpmnXml($workflow->bpmn_xml);

            if (!empty($parsed['error'])) {
                return ['status' => 'error', 'message' => $parsed['error']];
            }

            $instance = $this->startBpmnInstance($workflow, $context);

            return [
                'status' => 'success',
                'instance_code' => $instance->instance_code,
                'instance_id' => $instance->id,
                'current_element' => $instance->current_element_name,
            ];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function startBpmnInstance(Workflow $workflow, array $variables = []): BpmnProcessInstance
    {
        if (empty(trim($workflow->bpmn_xml ?? ''))) {
            throw new \RuntimeException("Workflow '{$workflow->name}' tidak memiliki definisi BPMN.");
        }

        $parsed = $this->parseBpmnXml($workflow->bpmn_xml);
        if (!empty($parsed['error'])) {
            throw new \RuntimeException('BPMN XML tidak valid: ' . $parsed['error']);
        }

        $instanceCode = strtoupper(Str::random(3)) . '-' . date('Ymd') . '-' . str_pad(
            BpmnProcessInstance::whereHas('process', fn($q) => $q->where('id', '>', 0))
                ->whereDate('created_at', today())->count() + 1,
            4, '0', STR_PAD_LEFT
        );

        $instance = BpmnProcessInstance::create([
            'process_id' => 0,
            'unified_workflow_id' => $workflow->id,
            'company_id' => $workflow->company_id,
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
                'variable_value' => is_scalar($value) ? (string)$value : json_encode($value),
                'variable_type' => $this->detectVariableType($value),
            ]);
        }

        $this->logBpmnEvent($instance->id, null, 'Process Started', 'process_started', [
            'workflow_name' => $workflow->name,
            'variables' => $variables,
        ]);

        if (!empty($parsed['events'])) {
            $startEvents = array_filter($parsed['events'], fn($e) => $e['type'] === 'startEvent');
            if (!empty($startEvents)) {
                $startEvent = reset($startEvents);
                $instance->update([
                    'current_element_id' => $startEvent['id'],
                    'current_element_name' => $startEvent['name'] ?: 'Start',
                ]);

                $nextElements = $this->getNextBpmnElements($parsed, $startEvent['id']);
                foreach ($nextElements as $next) {
                    $this->createBpmnTaskInstance($instance, $next, $parsed, $variables, $workflow->sla_hours);
                }
            }
        }

        return $instance->fresh();
    }

    public function parseBpmnXml(string $xml): array
    {
        if (empty(trim($xml))) {
            return ['pools' => [], 'lanes' => [], 'tasks' => [], 'events' => [], 'gateways' => [], 'flows' => [], 'error' => 'BPMN XML kosong'];
        }

        try {
            $sxml = new SimpleXMLElement($xml);
            $sxml->registerXPathNamespace('bpmn', 'http://www.omg.org/spec/BPMN/20100524/MODEL');
            $sxml->registerXPathNamespace('bpmndi', 'http://www.omg.org/spec/BPMN/20100524/DI');

            return [
                'pools' => $this->parseBpmnNodes($sxml, '//bpmn:participant', 'participant'),
                'lanes' => $this->parseBpmnNodes($sxml, '//bpmn:lane', 'lane'),
                'tasks' => $this->parseBpmnTasks($sxml),
                'events' => $this->parseBpmnEvents($sxml),
                'gateways' => $this->parseBpmnGateways($sxml),
                'flows' => $this->parseBpmnFlows($sxml),
            ];
        } catch (\Throwable $e) {
            return [
                'pools' => [], 'lanes' => [], 'tasks' => [], 'events' => [],
                'gateways' => [], 'flows' => [],
                'error' => 'Gagal parsing BPMN XML: ' . $e->getMessage(),
            ];
        }
    }

    protected function parseBpmnNodes(SimpleXMLElement $xml, string $xpath, string $type): array
    {
        $result = [];
        $nodes = $xml->xpath($xpath);
        if (!$nodes) return $result;

        foreach ($nodes as $node) {
            $result[] = [
                'id' => (string)$node['id'],
                'name' => (string)($node['name'] ?? ''),
                'type' => $type,
                'process_ref' => (string)($node['processRef'] ?? ''),
            ];
        }
        return $result;
    }

    protected function parseBpmnTasks(SimpleXMLElement $xml): array
    {
        $tasks = [];
        $taskTypes = ['userTask', 'serviceTask', 'scriptTask', 'sendTask', 'receiveTask', 'manualTask', 'businessRuleTask', 'task'];

        foreach ($taskTypes as $type) {
            $nodes = $xml->xpath("//bpmn:{$type}");
            if (!$nodes) continue;
            foreach ($nodes as $node) {
                $tasks[] = [
                    'id' => (string)$node['id'],
                    'name' => (string)($node['name'] ?? ''),
                    'type' => $type,
                    'default_flow' => (string)($node['default'] ?? ''),
                ];
            }
        }
        return $tasks;
    }

    protected function parseBpmnEvents(SimpleXMLElement $xml): array
    {
        $events = [];
        $eventTypes = ['startEvent', 'endEvent', 'intermediateCatchEvent', 'intermediateThrowEvent', 'boundaryEvent'];

        foreach ($eventTypes as $type) {
            $nodes = $xml->xpath("//bpmn:{$type}");
            if (!$nodes) continue;
            foreach ($nodes as $node) {
                $events[] = [
                    'id' => (string)$node['id'],
                    'name' => (string)($node['name'] ?? ''),
                    'type' => $type,
                    'attached_to' => (string)($node['attachedToRef'] ?? ''),
                ];
            }
        }
        return $events;
    }

    protected function parseBpmnGateways(SimpleXMLElement $xml): array
    {
        $gateways = [];
        $gatewayTypes = ['exclusiveGateway', 'inclusiveGateway', 'parallelGateway', 'eventBasedGateway', 'complexGateway'];

        foreach ($gatewayTypes as $type) {
            $nodes = $xml->xpath("//bpmn:{$type}");
            if (!$nodes) continue;
            foreach ($nodes as $node) {
                $gateways[] = [
                    'id' => (string)$node['id'],
                    'name' => (string)($node['name'] ?? ''),
                    'type' => $type,
                    'default_flow' => (string)($node['default'] ?? ''),
                ];
            }
        }
        return $gateways;
    }

    protected function parseBpmnFlows(SimpleXMLElement $xml): array
    {
        $flows = [];
        $nodes = $xml->xpath('//bpmn:sequenceFlow');
        if (!$nodes) return $flows;

        foreach ($nodes as $node) {
            $condition = null;
            $conditionExpr = $node->xpath('bpmn:conditionExpression');
            if ($conditionExpr && count($conditionExpr) > 0) {
                $condition = (string)$conditionExpr[0];
            }

            $flows[] = [
                'id' => (string)$node['id'],
                'name' => (string)($node['name'] ?? ''),
                'source_ref' => (string)$node['sourceRef'],
                'target_ref' => (string)$node['targetRef'],
                'condition' => $condition,
            ];
        }
        return $flows;
    }

    protected function createBpmnTaskInstance(BpmnProcessInstance $instance, array $element, array $allElements, array $variables, ?int $slaHours): ?BpmnTaskInstance
    {
        if (in_array($element['type'], ['startEvent', 'endEvent'])) return null;

        $taskType = 'user_task';
        if (str_contains($element['type'], 'Task')) {
            $taskType = str_replace('Task', '_task', $element['type']);
            $taskType = Str::snake($taskType);
        }

        $slaHoursFloat = $slaHours ? (float)$slaHours : null;

        $taskInstance = BpmnTaskInstance::create([
            'process_instance_id' => $instance->id,
            'element_id' => $element['id'],
            'task_name' => $element['name'] ?: $element['id'],
            'type' => $taskType,
            'status' => 'pending',
            'gateway_type' => null,
            'input_variables' => $variables,
            'sla_hours' => $slaHoursFloat,
            'priority' => 0,
            'sla_deadline' => $slaHoursFloat ? now()->addHours($slaHoursFloat) : null,
        ]);

        $instance->update([
            'current_element_id' => $element['id'],
            'current_element_name' => $element['name'] ?: $element['id'],
        ]);

        $this->logBpmnEvent($instance->id, $element['id'], $element['name'] ?? $element['id'], 'task_started', [
            'type' => $taskType,
            'sla_hours' => $slaHoursFloat,
        ]);

        if ($taskType === 'service_task' || $taskType === 'script_task') {
            $taskInstance->update([
                'status' => 'completed',
                'completed_at' => now(),
                'output_variables' => ['auto_completed' => true],
            ]);
            $this->advanceBpmnProcess($taskInstance, ['auto_completed' => true]);
        }

        return $taskInstance;
    }

    public function advanceBpmnProcess(BpmnTaskInstance $taskInstance, array $output = []): void
    {
        if ($taskInstance->status === 'completed' || $taskInstance->status === 'cancelled') return;

        $taskInstance->update([
            'status' => 'completed',
            'output_variables' => $output,
            'completed_at' => now(),
        ]);

        $duration = $taskInstance->started_at ? now()->diffInSeconds($taskInstance->started_at) : 0;

        $this->logBpmnEvent($taskInstance->process_instance_id, $taskInstance->element_id, $taskInstance->task_name, 'task_completed', ['output' => $output], auth()->id(), $duration);

        $instance = $taskInstance->processInstance;
        $workflow = Workflow::find($instance->unified_workflow_id);

        if (!$workflow || empty(trim($workflow->bpmn_xml ?? ''))) return;

        $elements = $this->parseBpmnXml($workflow->bpmn_xml);
        if (!empty($elements['error'])) return;

        $allVars = $this->getAllBpmnVariables($instance->id);
        if (!empty($output)) {
            foreach ($output as $key => $value) {
                $this->setBpmnVariable($instance->id, $key, $value);
            }
            $allVars = array_merge($allVars, $output);
        }

        $nextElements = $this->getNextBpmnElements($elements, $taskInstance->element_id);

        foreach ($nextElements as $next) {
            if (str_contains($next['type'], 'Gateway')) {
                $flowId = $this->evaluateBpmnGateway($next, $elements, $allVars);

                $this->logBpmnEvent($instance->id, $next['id'], $next['name'], 'gateway_evaluated', [
                    'selected_flow' => $flowId,
                    'variables' => $allVars,
                ]);

                if ($flowId) {
                    $targetElements = array_filter($elements['flows'], fn($f) => $f['id'] === $flowId);
                    foreach ($targetElements as $flow) {
                        $target = $this->findBpmnElement($elements, $flow['target_ref']);
                        if ($target) {
                            if ($target['type'] === 'endEvent') {
                                $this->completeBpmnInstance($instance);
                            } else {
                                $this->createBpmnTaskInstance($instance, $target, $elements, $allVars, $workflow->sla_hours);
                            }
                        }
                    }
                }
            } elseif ($next['type'] === 'endEvent') {
                $this->completeBpmnInstance($instance);
            } else {
                $this->createBpmnTaskInstance($instance, $next, $elements, $allVars, $workflow->sla_hours);
            }
        }

        $allTaskInstances = BpmnTaskInstance::where('process_instance_id', $instance->id)->get();
        $allCompleted = $allTaskInstances->every(fn($t) => $t->status === 'completed' || $t->status === 'cancelled');
        if ($allCompleted && $instance->status === 'running') {
            $this->completeBpmnInstance($instance);
        }
    }

    protected function completeBpmnInstance(BpmnProcessInstance $instance): void
    {
        $instance->update([
            'status' => 'completed',
            'current_element_id' => null,
            'current_element_name' => 'Completed',
            'completed_at' => now(),
        ]);

        $this->logBpmnEvent($instance->id, null, 'Process Completed', 'process_completed', [
            'duration' => $instance->started_at ? $instance->started_at->diffForHumans($instance->completed_at, true) : 'N/A',
        ]);
    }

    protected function getNextBpmnElements(array $elements, string $elementId): array
    {
        $nextElements = [];
        $outgoingFlows = array_filter($elements['flows'], fn($f) => $f['source_ref'] === $elementId);

        foreach ($outgoingFlows as $flow) {
            $target = $this->findBpmnElement($elements, $flow['target_ref']);
            if ($target) {
                $target['incoming_flow_id'] = $flow['id'];
                $target['incoming_flow_condition'] = $flow['condition'];
                $nextElements[] = $target;
            }
        }
        return $nextElements;
    }

    protected function findBpmnElement(array $elements, string $id): ?array
    {
        foreach (['tasks', 'events', 'gateways'] as $category) {
            foreach ($elements[$category] ?? [] as $el) {
                if ($el['id'] === $id) return $el;
            }
        }
        return null;
    }

    public function evaluateBpmnGateway(array $gateway, array $elements, array $variables): ?string
    {
        $gatewayType = str_replace('Gateway', '', $gateway['type']);
        $outgoingFlows = array_filter($elements['flows'], fn($f) => $f['source_ref'] === $gateway['id']);

        return match ($gatewayType) {
            'exclusive' => $this->evalExclusiveGateway($outgoingFlows, $variables, $gateway),
            'inclusive' => $this->evalInclusiveGateway($outgoingFlows, $variables),
            'parallel' => !empty($outgoingFlows) ? reset($outgoingFlows)['id'] : null,
            'eventBased' => !empty($outgoingFlows) ? reset($outgoingFlows)['id'] : null,
            default => !empty($outgoingFlows) ? reset($outgoingFlows)['id'] : null,
        };
    }

    protected function evalExclusiveGateway(array $flows, array $variables, array $gateway): ?string
    {
        foreach ($flows as $flow) {
            if (!empty($flow['condition']) && $this->evalBpmnCondition($flow['condition'], $variables)) {
                return $flow['id'];
            }
        }
        if (!empty($gateway['default_flow'])) return $gateway['default_flow'];
        return !empty($flows) ? reset($flows)['id'] : null;
    }

    protected function evalInclusiveGateway(array $flows, array $variables): ?string
    {
        foreach ($flows as $flow) {
            if (empty($flow['condition']) || $this->evalBpmnCondition($flow['condition'], $variables)) {
                return $flow['id'];
            }
        }
        return !empty($flows) ? reset($flows)['id'] : null;
    }

    protected function evalBpmnCondition(string $condition, array $variables): bool
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
            return (bool)eval("return ({$condition});");
        } catch (\Throwable $e) {
            \Log::warning('BPMN condition eval failed: ' . $e->getMessage() . ' | ' . $condition);
        }
        return false;
    }

    public function getAllBpmnVariables(int $instanceId): array
    {
        $vars = BpmnProcessVariable::where('process_instance_id', $instanceId)->get();
        $result = [];
        foreach ($vars as $var) {
            $result[$var->variable_name] = $this->castVariableValue($var->variable_value, $var->variable_type);
        }
        return $result;
    }

    public function setBpmnVariable(int $instanceId, string $name, $value): void
    {
        BpmnProcessVariable::updateOrCreate(
            ['process_instance_id' => $instanceId, 'variable_name' => $name],
            [
                'variable_value' => is_scalar($value) ? (string)$value : json_encode($value),
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
            'integer' => (int)$value,
            'float' => (float)$value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    protected function logBpmnEvent(int $instanceId, ?string $elementId, ?string $elementName, string $eventType, array $eventData = [], ?int $actorId = null, ?float $duration = null): void
    {
        BpmnExecutionLog::create([
            'process_instance_id' => $instanceId,
            'element_id' => $elementId,
            'element_name' => $elementName,
            'event_type' => $eventType,
            'event_data' => $eventData,
            'actor_user_id' => $actorId ?? auth()->id(),
            'duration_seconds' => $duration,
            'logged_at' => now(),
        ]);
    }

    protected function recordSuccess(Workflow $workflow, array $context, array $results, int $durationMs): void
    {
        $workflow->update(['run_count' => $workflow->run_count + 1, 'last_run_at' => now()]);
        WorkflowExecution::create([
            'workflow_id' => $workflow->id,
            'trigger_event' => $workflow->trigger_event,
            'input_context' => $context,
            'output_result' => $results,
            'status' => 'success',
            'duration_ms' => $durationMs,
        ]);
    }

    protected function recordError(Workflow $workflow, array $context, array $results, string $error, int $durationMs): void
    {
        $workflow->update(['run_count' => $workflow->run_count + 1, 'last_run_at' => now()]);
        WorkflowExecution::create([
            'workflow_id' => $workflow->id,
            'trigger_event' => $workflow->trigger_event,
            'input_context' => $context,
            'output_result' => $results,
            'status' => 'error',
            'error_message' => $error,
            'duration_ms' => $durationMs,
        ]);
    }

    public function validateWorkflow(Workflow $workflow): array
    {
        $errors = [];

        if (empty($workflow->name)) {
            $errors[] = 'Nama workflow harus diisi.';
        }

        if (empty($workflow->workflow_type) || !in_array($workflow->workflow_type, array_keys(Workflow::types()))) {
            $errors[] = 'Tipe workflow tidak valid.';
        }

        if (in_array($workflow->workflow_type, [Workflow::TYPE_SIMPLE, Workflow::TYPE_AUTOMATION])) {
            if (empty($workflow->trigger_event)) {
                $errors[] = 'Trigger event harus diisi untuk workflow simpel/otomasi.';
            }
            if (empty($workflow->actions)) {
                $errors[] = 'Minimal satu aksi harus ditentukan.';
            }
        }

        if ($workflow->workflow_type === Workflow::TYPE_APPROVAL) {
            if (empty($workflow->approval_levels)) {
                $errors[] = 'Level persetujuan harus dikonfigurasi.';
            }
            if (empty($workflow->module)) {
                $errors[] = 'Module harus ditentukan untuk workflow approval.';
            }
        }

        if ($workflow->workflow_type === Workflow::TYPE_BPMN) {
            if (empty(trim($workflow->bpmn_xml ?? ''))) {
                $errors[] = 'Definisi BPMN XML tidak boleh kosong.';
            } else {
                $parsed = $this->parseBpmnXml($workflow->bpmn_xml);
                if (!empty($parsed['error'])) {
                    $errors[] = $parsed['error'];
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function getAvailableTriggers(): array
    {
        $automationSvc = app(WorkflowAutomationService::class);
        return $automationSvc->getAvailableTriggers();
    }

    public function getAvailableActions(): array
    {
        $automationSvc = app(WorkflowAutomationService::class);
        return $automationSvc->getAvailableActions();
    }

    public function getBpmnSlaStatus(int $instanceId): array
    {
        $bpmnSvc = app(BpmnService::class);
        return $bpmnSvc->getSlaStatus($instanceId);
    }
}
