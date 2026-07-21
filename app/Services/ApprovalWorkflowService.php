<?php

namespace App\Services;

use App\Models\ApprovalAction;
use App\Models\ApprovalDelegation;
use App\Models\ApprovalLevel;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\Employee;
use Illuminate\Support\Collection;

class ApprovalWorkflowService
{
    public function submit(
        ApprovalWorkflow $workflow,
        string $module,
        int $moduleId,
        string $title,
        int $requesterId,
        ?string $notes = null
    ): ApprovalRequest {
        $levels = $workflow->levels()->orderBy('level')->get();

        if ($levels->isEmpty()) {
            throw new \RuntimeException("Workflow '{$workflow->name}' has no approval levels configured.");
        }

        $request = ApprovalRequest::create([
            'company_id' => $workflow->company_id,
            'workflow_id' => $workflow->id,
            'module' => $module,
            'module_id' => $moduleId,
            'title' => $title,
            'requester_id' => $requesterId,
            'status' => 'pending',
            'current_level' => 1,
            'total_levels' => $levels->max('level'),
            'submitted_at' => now(),
            'notes' => $notes,
        ]);

        return $request->fresh(['workflow.levels', 'requester']);
    }

    public function canApprove(ApprovalRequest $request, int $employeeId): bool
    {
        if (!$request->isPending()) {
            return false;
        }

        $level = $this->getCurrentLevel($request);
        if (!$level) {
            return false;
        }

        $effectiveApprovers = $this->getEffectiveApproverIds($level, $request->requester_id);

        return in_array($employeeId, $effectiveApprovers);
    }

    public function approve(ApprovalRequest $request, int $approverId, ?string $comment = null): ApprovalRequest
    {
        if (!$request->isPending()) {
            throw new \RuntimeException('Approval request is not in pending status.');
        }

        if (!$this->canApprove($request, $approverId)) {
            throw new \RuntimeException('You are not authorized to approve this request.');
        }

        $level = $this->getCurrentLevel($request);

        ApprovalAction::create([
            'approval_request_id' => $request->id,
            'level_id' => $level->id,
            'approver_id' => $approverId,
            'action' => 'approve',
            'comment' => $comment,
            'action_at' => now(),
        ]);

        $approvedCount = $request->actions()
            ->where('level_id', $level->id)
            ->where('action', 'approve')
            ->count();

        $workflow = $request->workflow;
        $minApprovers = $workflow->min_approvers;

        if ($approvedCount < $minApprovers) {
            return $request->fresh(['actions', 'workflow.levels']);
        }

        if ($request->current_level >= $request->total_levels) {
            $request->update([
                'status' => 'approved',
                'completed_at' => now(),
            ]);

            $this->notifyModuleApproved($request);

            return $request->fresh(['actions', 'workflow.levels']);
        }

        $request->update([
            'current_level' => $request->current_level + 1,
        ]);

        return $request->fresh(['actions', 'workflow.levels']);
    }

    public function reject(ApprovalRequest $request, int $approverId, ?string $comment = null): ApprovalRequest
    {
        if (!$request->isPending()) {
            throw new \RuntimeException('Approval request is not in pending status.');
        }

        if (!$this->canApprove($request, $approverId)) {
            throw new \RuntimeException('You are not authorized to reject this request.');
        }

        $level = $this->getCurrentLevel($request);

        ApprovalAction::create([
            'approval_request_id' => $request->id,
            'level_id' => $level->id,
            'approver_id' => $approverId,
            'action' => 'reject',
            'comment' => $comment,
            'action_at' => now(),
        ]);

        $request->update([
            'status' => 'rejected',
            'completed_at' => now(),
        ]);

        $this->notifyModuleRejected($request, $comment ?? 'Ditolak');

        return $request->fresh(['actions', 'workflow.levels']);
    }

    public function delegate(ApprovalRequest $request, int $approverId, int $delegateId, ?string $comment = null): ApprovalRequest
    {
        if (!$request->isPending()) {
            throw new \RuntimeException('Approval request is not in pending status.');
        }

        if (!$this->canApprove($request, $approverId)) {
            throw new \RuntimeException('You are not authorized to delegate this request.');
        }

        $level = $this->getCurrentLevel($request);

        if (!$level->can_delegate) {
            throw new \RuntimeException('Delegation is not allowed for this approval level.');
        }

        ApprovalAction::create([
            'approval_request_id' => $request->id,
            'level_id' => $level->id,
            'approver_id' => $approverId,
            'action' => 'delegate',
            'delegated_to' => $delegateId,
            'comment' => $comment,
            'action_at' => now(),
        ]);

        return $request->fresh(['actions', 'workflow.levels']);
    }

    public function getPendingForEmployee(int $employeeId): Collection
    {
        $pendingRequests = ApprovalRequest::with(['workflow.levels', 'requester'])
            ->pending()
            ->get();

        return $pendingRequests->filter(function (ApprovalRequest $request) use ($employeeId) {
            return $this->canApprove($request, $employeeId);
        })->values();
    }

    public function checkSlaBreaches(): void
    {
        $pendingRequests = ApprovalRequest::with(['workflow.levels', 'requester'])
            ->pending()
            ->get();

        foreach ($pendingRequests as $request) {
            $level = $this->getCurrentLevel($request);
            if (!$level || !$level->sla_hours) {
                continue;
            }

            $slaDeadline = $request->submitted_at?->addHours($level->sla_hours);
            if (!$slaDeadline || now()->lt($slaDeadline)) {
                continue;
            }

            match ($level->sla_action) {
                'auto_reject' => $this->executeSlaAutoReject($request, $level),
                'auto_approve' => $this->executeSlaAutoApprove($request, $level),
                'escalate' => $this->executeSlaEscalate($request, $level),
                'remind' => $this->executeSlaRemind($request, $level),
                default => null,
            };
        }
    }

    protected function executeSlaAutoReject(ApprovalRequest $request, ApprovalLevel $level): void
    {
        $request->update([
            'status' => 'rejected',
            'completed_at' => now(),
        ]);

        $this->notifyModuleRejected($request, 'Auto-rejected due to SLA breach (level ' . $level->level . ')');
    }

    protected function executeSlaAutoApprove(ApprovalRequest $request, ApprovalLevel $level): void
    {
        if ($request->current_level >= $request->total_levels) {
            $request->update([
                'status' => 'approved',
                'completed_at' => now(),
            ]);
            $this->notifyModuleApproved($request);
        } else {
            $request->update([
                'current_level' => $request->current_level + 1,
            ]);
        }
    }

    protected function executeSlaEscalate(ApprovalRequest $request, ApprovalLevel $level): void
    {
        $nextLevel = ApprovalLevel::where('workflow_id', $request->workflow_id)
            ->where('level', '>', $request->current_level)
            ->orderBy('level')
            ->first();

        if ($nextLevel) {
            $request->update([
                'current_level' => $nextLevel->level,
            ]);
        }
    }

    protected function executeSlaRemind(ApprovalRequest $request, ApprovalLevel $level): void
    {
        $approverIds = $this->getEffectiveApproverIds($level, $request->requester_id);

        foreach ($approverIds as $approverId) {
            $employee = Employee::find($approverId);
            if ($employee && $employee->user) {
                \App\Models\Notification::create([
                    'user_id' => $employee->user->id,
                    'type' => 'approval_reminder',
                    'title' => 'Pengingat Approval: ' . $request->title,
                    'message' => "Approval request '{$request->title}' telah melewati SLA. Mohon segera ditindaklanjuti.",
                    'is_read' => false,
                ]);
            }
        }
    }

    private function getCurrentLevel(ApprovalRequest $request): ?ApprovalLevel
    {
        return ApprovalLevel::where('workflow_id', $request->workflow_id)
            ->where('level', $request->current_level)
            ->first();
    }

    private function getEffectiveApproverIds(ApprovalLevel $level, int $requestingEmployeeId): array
    {
        $approverIds = $level->getApproverEmployeeIds();

        if (empty($approverIds)) {
            return [];
        }

        $effectiveIds = [];

        foreach ($approverIds as $approverId) {
            if ($approverId === $requestingEmployeeId) {
                continue;
            }

            $delegation = ApprovalDelegation::active()
                ->forApprover($approverId)
                ->first();

            if ($delegation && $delegation->delegate_id !== $requestingEmployeeId) {
                $effectiveIds[] = $delegation->delegate_id;
            } else {
                $effectiveIds[] = $approverId;
            }
        }

        return array_unique($effectiveIds);
    }

    private function notifyModuleApproved(ApprovalRequest $request): void
    {
        $module = $request->module()->first();
        if ($module && method_exists($module, 'onApproved')) {
            $module->onApproved();
        }
    }

    private function notifyModuleRejected(ApprovalRequest $request, string $reason): void
    {
        $module = $request->module()->first();
        if ($module && method_exists($module, 'onRejected')) {
            $module->onRejected($reason);
        }
    }
}
