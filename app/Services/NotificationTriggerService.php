<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Leave;
use App\Models\Invoice;
use App\Models\Overtime;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseOrder;
use App\Models\Reimbursement;
use App\Models\Ticket;
use App\Models\User;

class NotificationTriggerService
{
    protected array $triggerConfig = [
        'leave.submitted' => [
            'model' => Leave::class,
            'event' => 'created',
            'title' => 'Cuti Baru Diajukan',
            'body' => 'Cuti baru dari {employee} perlu persetujuan: {dates}',
            'notify' => ['manager', 'department_head'],
        ],
        'leave.approved' => [
            'model' => Leave::class,
            'event' => 'status_changed_approved',
            'title' => 'Cuti Disetujui',
            'body' => 'Cuti Anda disetujui: {dates}',
            'notify' => ['requester'],
        ],
        'leave.rejected' => [
            'model' => Leave::class,
            'event' => 'status_changed_rejected',
            'title' => 'Cuti Ditolak',
            'body' => 'Cuti Anda ditolak: {dates}. Alasan: {reason}',
            'notify' => ['requester'],
        ],
        'reimbursement.submitted' => [
            'model' => Reimbursement::class,
            'event' => 'created',
            'title' => 'Reimbursement Baru',
            'body' => 'Reimbursement baru dari {employee}: Rp {amount} — {category}',
            'notify' => ['manager', 'department_head'],
        ],
        'reimbursement.approved' => [
            'model' => Reimbursement::class,
            'event' => 'status_changed_approved',
            'title' => 'Reimbursement Disetujui',
            'body' => 'Reimbursement Anda disetujui: Rp {amount}',
            'notify' => ['requester'],
        ],
        'reimbursement.rejected' => [
            'model' => Reimbursement::class,
            'event' => 'status_changed_rejected',
            'title' => 'Reimbursement Ditolak',
            'body' => 'Reimbursement Anda ditolak: Rp {amount}. Alasan: {reason}',
            'notify' => ['requester'],
        ],
        'overtime.submitted' => [
            'model' => Overtime::class,
            'event' => 'created',
            'title' => 'Lembur Baru Diajukan',
            'body' => 'Lembur baru dari {employee} pada {date}: {duration} menit',
            'notify' => ['manager', 'department_head'],
        ],
        'overtime.approved' => [
            'model' => Overtime::class,
            'event' => 'status_changed_approved',
            'title' => 'Lembur Disetujui',
            'body' => 'Lembur Anda pada {date} disetujui: {duration} menit',
            'notify' => ['requester'],
        ],
        'overtime.rejected' => [
            'model' => Overtime::class,
            'event' => 'status_changed_rejected',
            'title' => 'Lembur Ditolak',
            'body' => 'Lembur Anda pada {date} ditolak',
            'notify' => ['requester'],
        ],
        'pr.created' => [
            'model' => PurchaseRequisition::class,
            'event' => 'created',
            'title' => 'Purchase Requisition Baru',
            'body' => 'PR #{number} dari {department} perlu persetujuan',
            'notify' => ['approvers', 'department_head'],
        ],
        'pr.approved' => [
            'model' => PurchaseRequisition::class,
            'event' => 'status_changed_approved',
            'title' => 'PR Disetujui',
            'body' => 'PR #{number} telah disetujui',
            'notify' => ['requester'],
        ],
        'pr.rejected' => [
            'model' => PurchaseRequisition::class,
            'event' => 'status_changed_rejected',
            'title' => 'PR Ditolak',
            'body' => 'PR #{number} ditolak: {reason}',
            'notify' => ['requester'],
        ],
        'po.created' => [
            'model' => PurchaseOrder::class,
            'event' => 'created',
            'title' => 'Purchase Order Baru',
            'body' => 'PO #{number} dari {supplier} perlu persetujuan',
            'notify' => ['approvers', 'department_head'],
        ],
        'po.approved' => [
            'model' => PurchaseOrder::class,
            'event' => 'status_changed_approved',
            'title' => 'PO Disetujui',
            'body' => 'PO #{number} telah disetujui — siap dikirim ke supplier',
            'notify' => ['requester'],
        ],
        'invoice.overdue' => [
            'model' => Invoice::class,
            'event' => 'overdue',
            'title' => 'Invoice Jatuh Tempo',
            'body' => 'Invoice #{number} jatuh tempo {days} hari lalu. Total: Rp {total}',
            'notify' => ['finance', 'creator'],
        ],
        'ticket.created' => [
            'model' => Ticket::class,
            'event' => 'created',
            'title' => 'Tiket Baru',
            'body' => 'Tiket baru #{number}: {subject} — Prioritas: {priority}',
            'notify' => ['assignee'],
        ],
        'ticket.assigned' => [
            'model' => Ticket::class,
            'event' => 'assigned_changed',
            'title' => 'Tiket Ditugaskan',
            'body' => 'Tiket #{number} ditugaskan kepada Anda: {subject}',
            'notify' => ['assignee'],
        ],
        'ticket.resolved' => [
            'model' => Ticket::class,
            'event' => 'status_changed_resolved',
            'title' => 'Tiket Diselesaikan',
            'body' => 'Tiket #{number}: {subject} telah diselesaikan',
            'notify' => ['creator', 'requester'],
        ],
    ];

    public function onModelCreated($model): void
    {
        $className = get_class($model);
        $triggers = $this->getTriggersForModel($className, 'created');

        foreach ($triggers as $key => $trigger) {
            $this->sendForTrigger($key, $trigger, $model, []);
        }
    }

    public function onModelUpdated($model, array $changes): void
    {
        $className = get_class($model);

        if (isset($changes['status'])) {
            $oldStatus = $changes['status'] !== $model->status ? $changes['status'] : null;
            if ($oldStatus) {
                $this->onStatusChanged($model, $oldStatus, $model->status);
            }
        }

        if (isset($changes['assigned_to']) && $changes['assigned_to'] !== $model->assigned_to) {
            $triggers = $this->getTriggersForModel($className, 'assigned_changed');
            foreach ($triggers as $key => $trigger) {
                $this->sendForTrigger($key, $trigger, $model, []);
            }
        }
    }

    public function onStatusChanged($model, string $oldStatus, string $newStatus): void
    {
        $className = get_class($model);

        $event = 'status_changed_' . $newStatus;
        $triggers = $this->getTriggersForModel($className, $event);

        if (empty($triggers)) {
            $eventGeneric = 'status_changed';
            $triggers = $this->getTriggersForModel($className, $eventGeneric);
        }

        foreach ($triggers as $key => $trigger) {
            $this->sendForTrigger($key, $trigger, $model, [
                'oldStatus' => $oldStatus,
                'newStatus' => $newStatus,
            ]);
        }
    }

    public function getTriggersForModel(string $modelClass, string $event): array
    {
        $matching = [];
        foreach ($this->triggerConfig as $key => $config) {
            if ($config['model'] === $modelClass && $config['event'] === $event) {
                $matching[$key] = $config;
            }
            if ($config['model'] === $modelClass && $event === 'status_changed' && str_starts_with($config['event'], 'status_changed_')) {
                $matching[$key] = $config;
            }
        }
        return $matching;
    }

    public function sendForTrigger(string $key, array $trigger, $model, array $context = []): void
    {
        $body = $this->interpolateBody($trigger['body'], $model, $context);

        $userIds = $this->getTargetUserIds($trigger['notify'], $model);

        foreach ($userIds as $userId) {
            NotificationService::send(
                userId: $userId,
                type: str_replace('.', '_', $key),
                title: $trigger['title'],
                body: $body,
                channel: 'in_app',
                data: [
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'trigger_key' => $key,
                ]
            );
        }
    }

    public function getTargetUserIds(array $roles, $model): array
    {
        $userIds = [];
        $requesterId = $model->employee_id ?? $model->requested_by ?? $model->created_by ?? null;
        $assignedTo = $model->assigned_to ?? null;

        foreach ($roles as $role) {
            switch ($role) {
                case 'requester':
                    if ($requesterId) {
                        $employee = Employee::find($requesterId);
                        if ($employee?->user) {
                            $userIds[] = $employee->user->id;
                        }
                    }
                    break;

                case 'manager':
                    if ($requesterId) {
                        $employee = Employee::with('department')->find($requesterId);
                        if ($employee?->department) {
                            $managerPosition = $employee->department->positions()
                                ->where('name', 'LIKE', '%manager%')
                                ->orWhere('name', 'LIKE', '%Manager%')
                                ->first();
                            if ($managerPosition) {
                                $managers = Employee::where('department_id', $employee->department_id)
                                    ->where('position_id', $managerPosition->id)
                                    ->where('id', '!=', $requesterId)
                                    ->get();
                                foreach ($managers as $manager) {
                                    if ($manager->user) {
                                        $userIds[] = $manager->user->id;
                                    }
                                }
                            }
                        }
                    }
                    break;

                case 'department_head':
                    if ($requesterId || isset($model->department_id)) {
                        $deptId = $model->department_id ?? Employee::find($requesterId)?->department_id;
                        if ($deptId) {
                            $managers = Employee::where('department_id', $deptId)
                                ->whereHas('designation', fn ($q) => $q->where('level', '<=', 2))
                                ->get();
                            foreach ($managers as $manager) {
                                if ($manager->user && $manager->id !== ($requesterId ?? 0)) {
                                    $userIds[] = $manager->user->id;
                                }
                            }
                        }
                    }
                    break;

                case 'approvers':
                    if (method_exists($model, 'approvalRequest') && $model->approvalRequest) {
                        $approvalRequest = $model->approvalRequest;
                        if ($approvalRequest) {
                            $level = $approvalRequest->currentApprovalLevel;
                            if ($level) {
                                $approverIds = $level->getApproverEmployeeIds();
                                foreach ($approverIds as $approverId) {
                                    $employee = Employee::find($approverId);
                                    if ($employee?->user) {
                                        $userIds[] = $employee->user->id;
                                    }
                                }
                            }
                        }
                    }
                    break;

                case 'assignee':
                    if ($assignedTo) {
                        $employee = Employee::find($assignedTo);
                        if ($employee?->user) {
                            $userIds[] = $employee->user->id;
                        }
                    }
                    break;

                case 'finance':
                    $financeUsers = User::whereHas('role', fn ($q) => $q->where('slug', 'admin'))
                        ->whereHas('employee', fn ($q) => $q->whereIn('department_id', function ($subQ) {
                            $subQ->select('id')->from('departments')->where('code', 'FA');
                        }))
                        ->get();
                    foreach ($financeUsers as $user) {
                        $userIds[] = $user->id;
                    }
                    break;

                case 'creator':
                    if (method_exists($model, 'createdBy') && $model->createdBy) {
                        $employee = $model->createdBy;
                        if ($employee->user) {
                            $userIds[] = $employee->user->id;
                        }
                    }
                    break;
            }
        }

        return array_unique($userIds);
    }

    public function interpolateBody(string $template, $model, array $context = []): string
    {
        $replacements = [];

        if ($model instanceof Leave) {
            $employeeName = $model->employee ? trim($model->employee->first_name . ' ' . $model->employee->last_name) : 'Unknown';
            $replacements['{employee}'] = $employeeName;
            $replacements['{dates}'] = ($model->start_date ? $model->start_date->format('d M') : '') . ' - ' . ($model->end_date ? $model->end_date->format('d M Y') : '');
            $replacements['{reason}'] = $model->rejection_reason ?? ($context['newStatus'] ?? '');
            $replacements['{days}'] = (string) ($model->total_days ?? '');
        } elseif ($model instanceof Reimbursement) {
            $employeeName = $model->employee ? trim($model->employee->first_name . ' ' . $model->employee->last_name) : 'Unknown';
            $replacements['{employee}'] = $employeeName;
            $replacements['{amount}'] = number_format($model->amount ?? 0, 0, ',', '.');
            $replacements['{category}'] = $model->category?->name ?? 'Tanpa Kategori';
            $replacements['{reason}'] = $model->rejection_reason ?? ($context['newStatus'] ?? '');
        } elseif ($model instanceof Overtime) {
            $employeeName = $model->employee ? trim($model->employee->first_name . ' ' . $model->employee->last_name) : 'Unknown';
            $replacements['{employee}'] = $employeeName;
            $replacements['{date}'] = $model->date ? $model->date->format('d M Y') : '';
            $replacements['{duration}'] = (string) ($model->duration_minutes ?? '0');
        } elseif ($model instanceof PurchaseRequisition) {
            $replacements['{number}'] = $model->pr_number ?? '#' . $model->id;
            $replacements['{department}'] = $model->department?->name ?? 'Unknown';
            $replacements['{reason}'] = $model->rejection_reason ?? ($context['newStatus'] ?? 'Ditolak');
        } elseif ($model instanceof PurchaseOrder) {
            $replacements['{number}'] = $model->po_number ?? '#' . $model->id;
            $replacements['{supplier}'] = $model->supplier?->name ?? 'Unknown';
        } elseif ($model instanceof Invoice) {
            $replacements['{number}'] = $model->invoice_number ?? '#' . $model->id;
            $daysOverdue = now()->startOfDay()->diffInDays($model->due_date?->startOfDay() ?? now());
            $replacements['{days}'] = ($daysOverdue > 0 ? abs($daysOverdue) : '0');
            $replacements['{total}'] = number_format($model->total ?? 0, 0, ',', '.');
        } elseif ($model instanceof Ticket) {
            $replacements['{number}'] = $model->ticket_number ?? '#' . $model->id;
            $replacements['{subject}'] = $model->subject ?? 'Tanpa Subjek';
            $replacements['{priority}'] = $model->priority ?? 'normal';
        }

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public function onInvoiceOverdue(Invoice $invoice): void
    {
        $key = 'invoice.overdue';
        if (!isset($this->triggerConfig[$key])) {
            return;
        }

        $trigger = $this->triggerConfig[$key];
        $this->sendForTrigger($key, $trigger, $invoice, []);
    }

    public function onTicketAssigned(Ticket $ticket): void
    {
        $key = 'ticket.assigned';
        if (!isset($this->triggerConfig[$key])) {
            return;
        }

        $trigger = $this->triggerConfig[$key];
        $this->sendForTrigger($key, $trigger, $ticket, []);
    }
}
