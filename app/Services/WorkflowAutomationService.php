<?php

namespace App\Services;

use App\Models\Workflow;
use App\Models\WorkflowExecution;
use App\Services\NotificationService;

class WorkflowAutomationService
{
    public function evaluate(string $triggerEvent, array $context): void
    {
        $workflows = Workflow::where('trigger_event', $triggerEvent)
            ->where('is_active', true)
            ->get();

        foreach ($workflows as $workflow) {
            if ($this->evaluateConditions($workflow, $context)) {
                $this->execute($workflow, $context);
            }
        }
    }

    public function evaluateConditions(Workflow $workflow, array $context): bool
    {
        $conditions = $workflow->trigger_conditions;

        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            if (! $this->checkCondition($condition, $context)) {
                return false;
            }
        }

        return true;
    }

    protected function checkCondition(array $condition, array $context): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? '=';
        $value = $condition['value'] ?? null;

        if (! $field || ! array_key_exists($field, $context)) {
            return true;
        }

        $actual = $context[$field];

        return match ($operator) {
            '=' => $actual == $value,
            '!=' => $actual != $value,
            '>' => (float) $actual > (float) $value,
            '<' => (float) $actual < (float) $value,
            '>=' => (float) $actual >= (float) $value,
            '<=' => (float) $actual <= (float) $value,
            'contains' => str_contains((string) $actual, (string) $value),
            'not_contains' => ! str_contains((string) $actual, (string) $value),
            'in' => in_array($actual, (array) $value),
            'not_in' => ! in_array($actual, (array) $value),
            'between' => is_array($value) && count($value) === 2
                && (float) $actual >= (float) $value[0]
                && (float) $actual <= (float) $value[1],
            default => true,
        };
    }

    public function execute(Workflow $workflow, array $context): void
    {
        $start = microtime(true);

        $results = [];

        try {
            foreach ($workflow->actions as $action) {
                $results[] = $this->executeAction($action, $context);
            }

            $durationMs = (int) ((microtime(true) - $start) * 1000);

            $workflow->update([
                'run_count' => $workflow->run_count + 1,
                'last_run_at' => now(),
            ]);

            WorkflowExecution::create([
                'workflow_id' => $workflow->id,
                'trigger_event' => $workflow->trigger_event,
                'input_context' => $context,
                'output_result' => $results,
                'status' => 'success',
                'duration_ms' => $durationMs,
            ]);

        } catch (\Throwable $e) {
            $durationMs = (int) ((microtime(true) - $start) * 1000);

            $workflow->update([
                'run_count' => $workflow->run_count + 1,
                'last_run_at' => now(),
            ]);

            WorkflowExecution::create([
                'workflow_id' => $workflow->id,
                'trigger_event' => $workflow->trigger_event,
                'input_context' => $context,
                'output_result' => $results,
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'duration_ms' => $durationMs,
            ]);
        }
    }

    public function executeAction(array $action, array $context): array
    {
        $type = $action['type'] ?? null;
        $config = $action['config'] ?? [];

        return match ($type) {
            'send_notification' => $this->actionSendNotification($config, $context),
            'send_email' => $this->actionSendEmail($config, $context),
            'send_wa' => $this->actionSendWa($config, $context),
            'create_task' => $this->actionCreateTask($config, $context),
            'update_record' => $this->actionUpdateRecord($config, $context),
            'create_record' => $this->actionCreateRecord($config, $context),
            'create_journal' => $this->actionCreateJournal($config, $context),
            'webhook' => $this->actionCallWebhook($config, $context),
            default => ['type' => $type, 'result' => 'unknown_action'],
        };
    }

    protected function actionSendNotification(array $config, array $context): array
    {
        $userId = $config['user_id'] ?? null;
        $title = $this->resolveTemplate($config['title'] ?? '', $context);
        $body = $this->resolveTemplate($config['body'] ?? '', $context);

        if (! $userId) {
            $userId = $context['user_id'] ?? $context['employee_id'] ?? null;
        }

        if ($userId) {
            NotificationService::send(
                $userId,
                'workflow_automation',
                $title,
                $body,
                'in_app',
                ['workflow_context' => $context]
            );
        }

        return ['type' => 'send_notification', 'result' => 'sent'];
    }

    protected function actionSendEmail(array $config, array $context): array
    {
        $to = $this->resolveTemplate($config['to'] ?? '', $context);
        $subject = $this->resolveTemplate($config['subject'] ?? '', $context);
        $body = $this->resolveTemplate($config['body'] ?? '', $context);

        if ($to && $subject) {
            NotificationService::send(
                $context['user_id'] ?? 0,
                'workflow_automation',
                $subject,
                $body,
                'email',
                ['to' => $to, 'workflow_context' => $context]
            );
        }

        return ['type' => 'send_email', 'result' => 'queued'];
    }

    protected function actionSendWa(array $config, array $context): array
    {
        $to = $this->resolveTemplate($config['to'] ?? '', $context);
        $message = $this->resolveTemplate($config['message'] ?? '', $context);

        if ($to && $message) {
            NotificationService::send(
                $context['user_id'] ?? 0,
                'workflow_automation',
                'Pesan Otomatis',
                $message,
                'whatsapp',
                ['to' => $to, 'workflow_context' => $context]
            );
        }

        return ['type' => 'send_wa', 'result' => 'queued'];
    }

    protected function actionCreateTask(array $config, array $context): array
    {
        $projectId = $config['project_id'] ?? ($context['project_id'] ?? null);
        $title = $this->resolveTemplate($config['title'] ?? 'Task otomatis', $context);
        $description = $this->resolveTemplate($config['description'] ?? '', $context);
        $assignedTo = $config['assigned_to'] ?? ($context['employee_id'] ?? null);

        if ($projectId) {
            $task = \App\Models\Task::create([
                'project_id' => $projectId,
                'title' => $title,
                'description' => $description,
                'status' => 'todo',
                'priority' => $config['priority'] ?? 'medium',
                'due_date' => $config['due_date'] ?? null,
            ]);

            if ($assignedTo) {
                \App\Models\TaskAssignee::create([
                    'task_id' => $task->id,
                    'employee_id' => $assignedTo,
                ]);
            }

            return ['type' => 'create_task', 'result' => 'created', 'task_id' => $task->id];
        }

        return ['type' => 'create_task', 'result' => 'skipped_no_project'];
    }

    protected function actionUpdateRecord(array $config, array $context): array
    {
        $modelClass = $config['model'] ?? null;
        $recordId = $config['record_id'] ?? ($context['id'] ?? null);
        $fields = $config['fields'] ?? [];

        if ($modelClass && $recordId && ! empty($fields)) {
            $resolvedFields = [];
            foreach ($fields as $key => $value) {
                $resolvedFields[$key] = $this->resolveTemplate((string) $value, $context);
            }

            $record = $modelClass::find($recordId);
            if ($record) {
                $record->update($resolvedFields);
                return ['type' => 'update_record', 'result' => 'updated', 'model' => $modelClass, 'id' => $recordId];
            }
        }

        return ['type' => 'update_record', 'result' => 'skipped'];
    }

    protected function actionCreateRecord(array $config, array $context): array
    {
        $modelClass = $config['model'] ?? null;
        $fields = $config['fields'] ?? [];

        if ($modelClass && ! empty($fields)) {
            $resolvedFields = [];
            foreach ($fields as $key => $value) {
                $resolvedFields[$key] = $this->resolveTemplate((string) $value, $context);
            }

            $record = $modelClass::create($resolvedFields);
            return ['type' => 'create_record', 'result' => 'created', 'model' => $modelClass, 'id' => $record->id];
        }

        return ['type' => 'create_record', 'result' => 'skipped'];
    }

    protected function actionCreateJournal(array $config, array $context): array
    {
        $companyId = $config['company_id'] ?? ($context['company_id'] ?? null);
        $debitCoaId = $config['debit_coa_id'] ?? null;
        $creditCoaId = $config['credit_coa_id'] ?? null;
        $amount = (float) $this->resolveTemplate((string) ($config['amount'] ?? '0'), $context);
        $description = $this->resolveTemplate($config['description'] ?? 'Auto journal', $context);

        if ($companyId && $debitCoaId && $creditCoaId && $amount > 0) {
            $journal = \App\Models\Journal::create([
                'company_id' => $companyId,
                'journal_date' => now()->toDateString(),
                'description' => $description,
                'journal_type' => 'workflow_auto',
                'status' => 'posted',
            ]);

            \App\Models\JournalEntry::create([
                'journal_id' => $journal->id,
                'coa_id' => $debitCoaId,
                'debit' => $amount,
                'credit' => 0,
                'description' => $description,
            ]);

            \App\Models\JournalEntry::create([
                'journal_id' => $journal->id,
                'coa_id' => $creditCoaId,
                'debit' => 0,
                'credit' => $amount,
                'description' => $description,
            ]);

            return ['type' => 'create_journal', 'result' => 'created', 'journal_id' => $journal->id];
        }

        return ['type' => 'create_journal', 'result' => 'skipped'];
    }

    protected function actionCallWebhook(array $config, array $context): array
    {
        $url = $this->resolveTemplate($config['url'] ?? '', $context);
        $payload = $config['payload'] ?? [];

        if ($url) {
            try {
                $resolvedPayload = [];
                foreach ($payload as $key => $value) {
                    $resolvedPayload[$key] = $this->resolveTemplate((string) $value, $context);
                }

                \Illuminate\Support\Facades\Http::timeout(30)
                    ->post($url, array_merge($resolvedPayload, ['workflow_context' => $context]));

                return ['type' => 'webhook', 'result' => 'called', 'url' => $url];
            } catch (\Throwable $e) {
                return ['type' => 'webhook', 'result' => 'failed', 'error' => $e->getMessage()];
            }
        }

        return ['type' => 'webhook', 'result' => 'skipped'];
    }

    protected function resolveTemplate(string $template, array $context): string
    {
        $result = $template;
        foreach ($context as $key => $value) {
            if (is_scalar($value)) {
                $result = str_replace("{{$key}}", (string) $value, $result);
            }
        }

        $result = preg_replace('/\{\{.*?\}\}/', '', $result);

        return $result;
    }

    public function getAvailableTriggers(): array
    {
        return [
            ['event' => 'employee.created', 'label' => 'Karyawan Dibuat', 'category' => 'Karyawan'],
            ['event' => 'employee.resigned', 'label' => 'Karyawan Resign', 'category' => 'Karyawan'],
            ['event' => 'employee.birthday_today', 'label' => 'Ulang Tahun Karyawan', 'category' => 'Karyawan'],
            ['event' => 'employee.contract_expiring', 'label' => 'Kontrak Akan Habis', 'category' => 'Karyawan'],
            ['event' => 'attendance.late', 'label' => 'Absensi Terlambat', 'category' => 'Absensi'],
            ['event' => 'attendance.absent', 'label' => 'Absensi Tidak Hadir', 'category' => 'Absensi'],
            ['event' => 'attendance.overtime', 'label' => 'Lembur', 'category' => 'Absensi'],
            ['event' => 'attendance.clock_out_forgot', 'label' => 'Lupa Clock Out', 'category' => 'Absensi'],
            ['event' => 'leave.submitted', 'label' => 'Cuti Diajukan', 'category' => 'Cuti'],
            ['event' => 'leave.approved', 'label' => 'Cuti Disetujui', 'category' => 'Cuti'],
            ['event' => 'leave.rejected', 'label' => 'Cuti Ditolak', 'category' => 'Cuti'],
            ['event' => 'invoice.created', 'label' => 'Invoice Dibuat', 'category' => 'Invoice'],
            ['event' => 'invoice.overdue', 'label' => 'Invoice Jatuh Tempo', 'category' => 'Invoice'],
            ['event' => 'invoice.paid', 'label' => 'Invoice Dibayar', 'category' => 'Invoice'],
            ['event' => 'lead.created', 'label' => 'Lead Dibuat', 'category' => 'CRM'],
            ['event' => 'lead.qualified', 'label' => 'Lead Qualified', 'category' => 'CRM'],
            ['event' => 'lead.converted', 'label' => 'Lead Terkonversi', 'category' => 'CRM'],
            ['event' => 'deal.won', 'label' => 'Deal Menang', 'category' => 'CRM'],
            ['event' => 'deal.lost', 'label' => 'Deal Kalah', 'category' => 'CRM'],
            ['event' => 'ticket.created', 'label' => 'Tiket Dibuat', 'category' => 'Helpdesk'],
            ['event' => 'ticket.breached', 'label' => 'Tiket SLA Breach', 'category' => 'Helpdesk'],
            ['event' => 'ticket.closed', 'label' => 'Tiket Ditutup', 'category' => 'Helpdesk'],
            ['event' => 'stock.low_stock', 'label' => 'Stok Menipis', 'category' => 'Inventory'],
            ['event' => 'stock.expired', 'label' => 'Stok Kedaluwarsa', 'category' => 'Inventory'],
            ['event' => 'stock.negative', 'label' => 'Stok Negatif', 'category' => 'Inventory'],
            ['event' => 'payroll.processed', 'label' => 'Payroll Diproses', 'category' => 'Payroll'],
        ];
    }

    public function getAvailableActions(): array
    {
        return [
            ['type' => 'send_wa', 'label' => 'Kirim WhatsApp', 'category' => 'Komunikasi'],
            ['type' => 'send_email', 'label' => 'Kirim Email', 'category' => 'Komunikasi'],
            ['type' => 'send_notification', 'label' => 'Kirim Notifikasi In-App', 'category' => 'Komunikasi'],
            ['type' => 'create_task', 'label' => 'Buat Tugas', 'category' => 'Project'],
            ['type' => 'update_record', 'label' => 'Update Record', 'category' => 'Data'],
            ['type' => 'create_record', 'label' => 'Buat Record Baru', 'category' => 'Data'],
            ['type' => 'create_journal', 'label' => 'Buat Jurnal Otomatis', 'category' => 'Finance'],
            ['type' => 'webhook', 'label' => 'Panggil Webhook Eksternal', 'category' => 'Integrasi'],
        ];
    }

    public function validate(Workflow $workflow): bool
    {
        if (empty($workflow->trigger_event)) {
            return false;
        }

        if (empty($workflow->actions)) {
            return false;
        }

        $validTriggers = array_column($this->getAvailableTriggers(), 'event');
        if (! in_array($workflow->trigger_event, $validTriggers)) {
            return false;
        }

        $validActions = array_column($this->getAvailableActions(), 'type');
        foreach ($workflow->actions as $action) {
            if (! in_array($action['type'] ?? '', $validActions)) {
                return false;
            }
        }

        return true;
    }
}
