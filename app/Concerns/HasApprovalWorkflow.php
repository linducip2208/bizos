<?php

namespace App\Concerns;

use App\Models\ApprovalAction;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Services\ApprovalWorkflowService;

trait HasApprovalWorkflow
{
    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'module', 'module', 'module_id');
    }

    public function submitForApproval(?string $notes = null): ApprovalRequest
    {
        $workflow = ApprovalWorkflow::active()
            ->forModule($this->getApprovalModule())
            ->where('company_id', $this->company_id ?? 1)
            ->first();

        if (!$workflow) {
            throw new \RuntimeException("No active approval workflow found for module: {$this->getApprovalModule()}");
        }

        /** @var ApprovalWorkflowService $service */
        $service = app(ApprovalWorkflowService::class);

        return $service->submit(
            workflow: $workflow,
            module: $this->getApprovalModule(),
            moduleId: $this->id,
            title: $this->getApprovalTitle(),
            requesterId: $this->getApprovalRequesterId(),
            notes: $notes ?? null,
        );
    }

    public function approve(int $approverId, ?string $comment = null): ApprovalRequest
    {
        $request = $this->approvalRequest;

        if (!$request) {
            throw new \RuntimeException('No active approval request for this record.');
        }

        /** @var ApprovalWorkflowService $service */
        $service = app(ApprovalWorkflowService::class);

        return $service->approve($request, $approverId, $comment);
    }

    public function reject(int $approverId, ?string $comment = null): ApprovalRequest
    {
        $request = $this->approvalRequest;

        if (!$request) {
            throw new \RuntimeException('No active approval request for this record.');
        }

        /** @var ApprovalWorkflowService $service */
        $service = app(ApprovalWorkflowService::class);

        return $service->reject($request, $approverId, $comment);
    }

    public function getApprovalStatus(): string
    {
        $request = $this->approvalRequest;
        return $request?->status ?? 'draft';
    }

    public function getApprovalStatusLabel(): string
    {
        return match ($this->getApprovalStatus()) {
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'draft' => 'Draft',
            default => 'Tidak Diketahui',
        };
    }

    public function isPendingApproval(): bool
    {
        return $this->getApprovalStatus() === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->getApprovalStatus() === 'approved';
    }

    public function cancelApproval(): bool
    {
        $request = $this->approvalRequest;
        if (!$request || !$request->isPending()) {
            return false;
        }

        $request->update(['status' => 'cancelled', 'completed_at' => now()]);
        return true;
    }
}
