<?php

namespace App\Concerns;

use App\Models\ApprovalAction;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\Workflow;
use App\Services\ApprovalWorkflowService;
use App\Services\UnifiedWorkflowService;

trait HasApprovalWorkflow
{
    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'module', 'module', 'module_id');
    }

    protected function findApprovalWorkflow(): ?Workflow
    {
        $unified = Workflow::active()
            ->ofType(Workflow::TYPE_APPROVAL)
            ->forModule($this->getApprovalModule())
            ->where('company_id', $this->company_id ?? 1)
            ->first();

        if ($unified) {
            return $unified;
        }

        return null;
    }

    protected function findLegacyApprovalWorkflow(): ?ApprovalWorkflow
    {
        return ApprovalWorkflow::active()
            ->forModule($this->getApprovalModule())
            ->where('company_id', $this->company_id ?? 1)
            ->first();
    }

    public function submitForApproval(?string $notes = null): ApprovalRequest
    {
        $unified = $this->findApprovalWorkflow();

        if ($unified) {
            $service = app(UnifiedWorkflowService::class);

            $title = $this->getApprovalTitle();
            $requesterId = $this->getApprovalRequesterId();
            $module = $this->getApprovalModule();

            if (method_exists($this, 'getApprovalWorkflowName')) {
                $title = $this->getApprovalWorkflowName() . ' ' . ($this->name ?? '#' . $this->id);
            }

            return $service->submitForApproval(
                workflow: $unified,
                module: $module,
                moduleId: $this->id,
                title: $title,
                requesterId: $requesterId,
                notes: $notes,
            );
        }

        $legacy = $this->findLegacyApprovalWorkflow();

        if (!$legacy) {
            $module = $this->getApprovalModule();
            throw new \RuntimeException("Tidak ada workflow approval aktif untuk modul: {$module}");
        }

        $service = app(ApprovalWorkflowService::class);

        return $service->submit(
            workflow: $legacy,
            module: $this->getApprovalModule(),
            moduleId: $this->id,
            title: $this->getApprovalTitle(),
            requesterId: $this->getApprovalRequesterId(),
            notes: $notes,
        );
    }

    public function approve(int $approverId, ?string $comment = null): ApprovalRequest
    {
        $request = $this->approvalRequest;

        if (!$request) {
            throw new \RuntimeException('Tidak ada approval request aktif untuk record ini.');
        }

        $service = app(ApprovalWorkflowService::class);

        return $service->approve($request, $approverId, $comment);
    }

    public function reject(int $approverId, ?string $comment = null): ApprovalRequest
    {
        $request = $this->approvalRequest;

        if (!$request) {
            throw new \RuntimeException('Tidak ada approval request aktif untuk record ini.');
        }

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
