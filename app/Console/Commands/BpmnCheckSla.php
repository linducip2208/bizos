<?php

namespace App\Console\Commands;

use App\Models\BpmnTaskInstance;
use App\Models\Notification;
use Illuminate\Console\Command;

class BpmnCheckSla extends Command
{
    protected $signature = 'bpmn:check-sla';

    protected $description = 'Periksa BPMN task instances yang melewati SLA dan eskalasi.';

    public function handle(): int
    {
        $overdueTasks = BpmnTaskInstance::with(['processInstance', 'processInstance.process'])
            ->where('status', 'pending')
            ->whereNotNull('sla_deadline')
            ->where('sla_deadline', '<', now())
            ->get();

        $count = $overdueTasks->count();

        if ($count === 0) {
            $this->info('Tidak ada BPMN task SLA yang overdue.');
            return self::SUCCESS;
        }

        foreach ($overdueTasks as $task) {
            $processName = $task->processInstance?->process?->name ?? 'Unknown';
            $instanceCode = $task->processInstance?->instance_code ?? 'N/A';

            $this->warn("SLA Breached: {$task->task_name} (Instance: {$instanceCode}, Proses: {$processName})");

            $task->update(['priority' => $task->priority + 1]);

            $admins = \App\Models\User::whereHas('role', function ($q) {
                $q->whereIn('slug', ['super-admin', 'admin', 'owner', 'manager']);
            })->pluck('id')->toArray();

            foreach ($admins as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'notification_type' => 'bpmn_sla_breached',
                    'title' => 'BPMN SLA Breached',
                    'body' => "Task '{$task->task_name}' di proses '{$processName}' (Instance: {$instanceCode}) telah melewati SLA.",
                    'data' => json_encode([
                        'task_instance_id' => $task->id,
                        'process_instance_id' => $task->process_instance_id,
                        'task_name' => $task->task_name,
                        'sla_hours' => $task->sla_hours,
                        'sla_deadline' => $task->sla_deadline?->toDateTimeString(),
                    ]),
                    'channel' => 'database',
                    'is_read' => false,
                    'read_at' => null,
                    'sent_at' => null,
                ]);
            }
        }

        $this->info("{$count} BPMN task(s) SLA breached dan dieskalasi.");
        return self::SUCCESS;
    }
}
