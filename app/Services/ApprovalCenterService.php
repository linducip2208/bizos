<?php

namespace App\Services;

use App\Models\ApprovalAction;
use App\Models\ApprovalDelegation;
use App\Models\ApprovalLevel;
use App\Models\ApprovalRequest;
use App\Models\Budget;
use App\Models\Contract;
use App\Models\Employee;
use App\Models\IntercompanyTransaction;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Reimbursement;
use App\Models\User;
use Illuminate\Support\Collection;

class ApprovalCenterService
{
    protected ApprovalWorkflowService $workflowService;

    public function __construct()
    {
        $this->workflowService = app(ApprovalWorkflowService::class);
    }

    /**
     * Get all pending approvals across all modules for a user.
     */
    public function getPendingApprovals(User $user): Collection
    {
        $employeeId = $user->employee_id;
        $results = collect();

        if (!$employeeId) {
            return $results;
        }

        // 1. Workflow-based approvals (through ApprovalRequest table)
        $results = $results->merge($this->getWorkflowPendingApprovals($employeeId));

        // 2. Direct-approval models (no workflow)
        $results = $results->merge($this->getDirectPendingApprovals($user));

        return $results->sortByDesc('submitted_at')->values();
    }

    /**
     * Get approval history for a user.
     */
    public function getApprovalHistory(User $user, int $limit = 50): Collection
    {
        $employeeId = $user->employee_id;
        $results = collect();

        if (!$employeeId) {
            return $results;
        }

        // 1. Actions taken on workflow approvals
        $actions = ApprovalAction::with(['request.requester', 'request.workflow'])
            ->where('approver_id', $employeeId)
            ->latest('action_at')
            ->limit($limit)
            ->get()
            ->map(function (ApprovalAction $action) {
                $request = $action->request;
                return [
                    'module' => $request->module ?? 'unknown',
                    'module_id' => $request->module_id ?? null,
                    'title' => $request->title ?? 'Tidak diketahui',
                    'requester_name' => $request->requester
                        ? trim($request->requester->first_name . ' ' . $request->requester->last_name)
                        : 'Unknown',
                    'status' => $action->action === 'approve' ? 'approved' : 'rejected',
                    'submitted_at' => $action->action_at?->format('Y-m-d H:i:s') ?? null,
                    'amount' => null,
                    'resolved_at' => $action->action_at?->format('Y-m-d H:i:s') ?? null,
                ];
            });

        $results = $results->merge($actions);

        // 2. Direct approvals already resolved
        $results = $results->merge($this->getDirectHistory($employeeId, $user));

        return $results->sortByDesc('resolved_at')->take($limit)->values();
    }

    /**
     * Get total count of pending approvals for the user.
     */
    public function getApprovalCount(User $user): int
    {
        return $this->getPendingApprovals($user)->count();
    }

    /**
     * Approve a pending item.
     */
    public function approve(string $module, int $id, User $user): bool
    {
        $employeeId = $user->employee_id;

        if (!$employeeId) {
            throw new \RuntimeException('Akun Anda tidak terhubung dengan data karyawan.');
        }

        return match ($module) {
            'leave', 'overtime', 'reimbursement', 'purchase_requisition',
            'purchase_order', 'budget' => $this->approveWorkflow($module, $id, $employeeId),
            'payment' => $this->approvePayment($id, $user),
            'contract' => $this->approveContract($id, $user),
            'intercompany_transaction' => $this->approveIntercompany($id, $user),
            'payroll' => $this->approvePayroll($id, $user),
            default => $this->approveGenericWorkflow($module, $id, $employeeId),
        };
    }

    /**
     * Reject a pending item.
     */
    public function reject(string $module, int $id, User $user, ?string $reason = null): bool
    {
        $employeeId = $user->employee_id;

        if (!$employeeId) {
            throw new \RuntimeException('Akun Anda tidak terhubung dengan data karyawan.');
        }

        return match ($module) {
            'leave', 'overtime', 'reimbursement', 'purchase_requisition',
            'purchase_order', 'budget' => $this->rejectWorkflow($module, $id, $employeeId, $reason),
            'payment' => $this->rejectPayment($id, $user, $reason),
            'contract' => $this->rejectContract($id, $user, $reason),
            'intercompany_transaction' => $this->rejectIntercompany($id, $user, $reason),
            'payroll' => $this->rejectPayroll($id, $user, $reason),
            default => $this->rejectGenericWorkflow($module, $id, $employeeId, $reason),
        };
    }

    // ─── Workflow-based helpers ───────────────────────────────────────────

    protected function getWorkflowPendingApprovals(int $employeeId): Collection
    {
        $delegated = $this->getDelegatedEmployeeIds($employeeId);

        $pendingRequests = ApprovalRequest::with(['requester', 'workflow.levels'])
            ->where('status', 'pending')
            ->get();

        return $pendingRequests->filter(function (ApprovalRequest $request) use ($employeeId, $delegated) {
            return $this->canUserApproveWorkflow($request, $employeeId, $delegated);
        })->map(function (ApprovalRequest $request) {
            return $this->formatWorkflowApproval($request);
        })->values();
    }

    protected function getEffectiveApproverIds(ApprovalLevel $level, int $requesterId): array
    {
        $selfExcluded = match ($level->approver_type) {
            'employee' => $level->approver_id !== $requesterId ? [$level->approver_id] : [],
            'role' => User::where('role_id', $level->approver_id)
                ->whereNotNull('employee_id')
                ->where('employee_id', '!=', $requesterId)
                ->pluck('employee_id')
                ->toArray(),
            'department' => Employee::where('department_id', $level->approver_id)
                ->where('id', '!=', $requesterId)
                ->pluck('id')
                ->toArray(),
            'position' => Employee::where('position_id', $level->approver_id)
                ->where('id', '!=', $requesterId)
                ->pluck('id')
                ->toArray(),
            default => [],
        };

        return $selfExcluded;
    }

    protected function getDelegatedEmployeeIds(int $approverId): array
    {
        return ApprovalDelegation::where('approver_id', $approverId)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->pluck('delegate_id')
            ->toArray();
    }

    protected function canUserApproveWorkflow(ApprovalRequest $request, int $employeeId, array $delegated): bool
    {
        $level = $request->workflow->levels
            ->where('level', $request->current_level)
            ->first();

        if (!$level) {
            return false;
        }

        $approverIds = $this->getEffectiveApproverIds($level, $request->requester_id);

        // Check if user is in the effective approver list
        if (in_array($employeeId, $approverIds)) {
            return true;
        }

        // Check delegation: if the original approver delegated to this employee
        foreach ($approverIds as $approverId) {
            if (in_array($employeeId, $this->getDelegatedEmployeeIds($approverId))) {
                return true;
            }
        }

        return false;
    }

    protected function formatWorkflowApproval(ApprovalRequest $request): array
    {
        $amount = $this->resolveAmount($request->module, $request->module_id);

        return [
            'type' => 'workflow',
            'module' => $request->module,
            'module_id' => $request->module_id,
            'approval_request_id' => $request->id,
            'title' => $request->title,
            'requester_name' => $request->requester
                ? trim($request->requester->first_name . ' ' . $request->requester->last_name)
                : 'Unknown',
            'status' => $request->status,
            'submitted_at' => $request->submitted_at?->format('Y-m-d H:i:s'),
            'amount' => $amount,
            'url' => $this->resolveUrl($request->module, $request->module_id),
            'icon' => $this->moduleIcon($request->module),
            'color' => $this->moduleColor($request->module),
            'module_label' => $this->moduleLabel($request->module),
        ];
    }

    // ─── Direct-approval helpers ───────────────────────────────────────────

    protected function getDirectPendingApprovals(User $user): Collection
    {
        $results = collect();
        $canApproveFinance = in_array($user->role?->slug, ['super-admin', 'admin', 'manager', 'finance']);
        $canApproveAll = in_array($user->role?->slug, ['super-admin', 'admin', 'manager']);

        // Payments
        if ($canApproveFinance) {
            $payments = Payment::with(['paymentMethod'])
                ->where('status', 'pending')
                ->latest()
                ->get()
                ->map(function (Payment $payment) {
                    return [
                        'type' => 'direct',
                        'module' => 'payment',
                        'module_id' => $payment->id,
                        'title' => 'Pembayaran #' . ($payment->payment_number ?? $payment->id),
                        'requester_name' => $payment->paymentMethod?->name ?? 'N/A',
                        'status' => 'pending',
                        'submitted_at' => $payment->created_at?->format('Y-m-d H:i:s'),
                        'amount' => $payment->amount,
                        'url' => '/admin/payments/' . $payment->id . '/edit',
                        'icon' => 'heroicon-o-banknotes',
                        'color' => 'emerald',
                        'module_label' => 'Pembayaran',
                    ];
                });
            $results = $results->merge($payments);
        }

        // Contracts
        if ($canApproveAll) {
            $contracts = Contract::with(['creator'])
                ->where('status', 'pending_approval')
                ->latest()
                ->get()
                ->map(function (Contract $contract) {
                    return [
                        'type' => 'direct',
                        'module' => 'contract',
                        'module_id' => $contract->id,
                        'title' => 'Kontrak #' . $contract->contract_number,
                        'requester_name' => $contract->creator?->name ?? 'N/A',
                        'status' => 'pending_approval',
                        'submitted_at' => $contract->created_at?->format('Y-m-d H:i:s'),
                        'amount' => $contract->value,
                        'url' => '/admin/contracts/' . $contract->id . '/edit',
                        'icon' => 'heroicon-o-document-text',
                        'color' => 'purple',
                        'module_label' => 'Kontrak',
                    ];
                });
            $results = $results->merge($contracts);
        }

        // Intercompany transactions
        if ($canApproveFinance) {
            $intercos = IntercompanyTransaction::with(['creator', 'fromCompany', 'toCompany'])
                ->where('status', 'pending_approval')
                ->latest()
                ->get()
                ->map(function (IntercompanyTransaction $trx) {
                    return [
                        'type' => 'direct',
                        'module' => 'intercompany_transaction',
                        'module_id' => $trx->id,
                        'title' => 'Transaksi Antar Perusahaan #' . ($trx->reference_number ?? $trx->id),
                        'requester_name' => $trx->creator?->name ?? 'N/A',
                        'status' => 'pending_approval',
                        'submitted_at' => $trx->created_at?->format('Y-m-d H:i:s'),
                        'amount' => $trx->amount,
                        'url' => '/admin/intercompany-transactions/' . $trx->id . '/edit',
                        'icon' => 'heroicon-o-arrows-right-left',
                        'color' => 'cyan',
                        'module_label' => 'Antar Perusahaan',
                    ];
                });
            $results = $results->merge($intercos);
        }

        // Payroll
        if ($canApproveFinance) {
            $payrolls = Payroll::with(['employee', 'period'])
                ->where('status', 'finalized')
                ->latest()
                ->get()
                ->map(function (Payroll $payroll) {
                    return [
                        'type' => 'direct',
                        'module' => 'payroll',
                        'module_id' => $payroll->id,
                        'title' => 'Penggajian: ' . ($payroll->employee?->first_name ?? '') . ' ' . ($payroll->employee?->last_name ?? ''),
                        'requester_name' => $payroll->period?->name ?? 'N/A',
                        'status' => 'pending_approval',
                        'submitted_at' => $payroll->created_at?->format('Y-m-d H:i:s'),
                        'amount' => $payroll->net_salary,
                        'url' => '/admin/payrolls/' . $payroll->id . '/edit',
                        'icon' => 'heroicon-o-currency-dollar',
                        'color' => 'orange',
                        'module_label' => 'Penggajian',
                    ];
                });
            $results = $results->merge($payrolls);
        }

        return $results;
    }

    protected function getDirectHistory(int $employeeId, User $user): Collection
    {
        $results = collect();

        // Payments confirmed by user
        $payments = Payment::where('confirmed_by', $user->id)
            ->whereIn('status', ['confirmed', 'rejected'])
            ->latest('confirmed_at')
            ->limit(50)
            ->get()
            ->map(fn (Payment $p) => [
                'module' => 'payment',
                'module_id' => $p->id,
                'title' => 'Pembayaran #' . ($p->payment_number ?? $p->id),
                'requester_name' => 'N/A',
                'status' => $p->status === 'confirmed' ? 'approved' : 'rejected',
                'submitted_at' => $p->created_at?->format('Y-m-d H:i:s'),
                'amount' => $p->amount,
                'resolved_at' => $p->confirmed_at?->format('Y-m-d H:i:s'),
            ]);
        $results = $results->merge($payments);

        // Contracts approved by user
        $contracts = Contract::where('approved_by', $user->id)
            ->whereIn('status', ['active', 'draft'])
            ->whereNotNull('approved_at')
            ->latest('approved_at')
            ->limit(50)
            ->get()
            ->map(fn (Contract $c) => [
                'module' => 'contract',
                'module_id' => $c->id,
                'title' => 'Kontrak #' . $c->contract_number,
                'requester_name' => 'N/A',
                'status' => $c->status === 'active' ? 'approved' : 'rejected',
                'submitted_at' => $c->created_at?->format('Y-m-d H:i:s'),
                'amount' => $c->value,
                'resolved_at' => $c->approved_at?->format('Y-m-d H:i:s'),
            ]);
        $results = $results->merge($contracts);

        // Intercompany transactions approved by user
        $intercos = IntercompanyTransaction::where('approved_by', $user->id)
            ->whereIn('status', ['approved', 'completed', 'rejected'])
            ->whereNotNull('approved_at')
            ->latest('approved_at')
            ->limit(50)
            ->get()
            ->map(fn (IntercompanyTransaction $t) => [
                'module' => 'intercompany_transaction',
                'module_id' => $t->id,
                'title' => 'Transaksi Antar Perusahaan #' . ($t->reference_number ?? $t->id),
                'requester_name' => 'N/A',
                'status' => in_array($t->status, ['approved', 'completed']) ? 'approved' : 'rejected',
                'submitted_at' => $t->created_at?->format('Y-m-d H:i:s'),
                'amount' => $t->amount,
                'resolved_at' => $t->approved_at?->format('Y-m-d H:i:s'),
            ]);
        $results = $results->merge($intercos);

        return $results;
    }

    // ─── Approve / Reject helpers ─────────────────────────────────────────

    protected function approveWorkflow(string $module, int $id, int $employeeId): bool
    {
        $model = $this->getModel($module, $id);

        if (!$model) {
            throw new \RuntimeException('Data tidak ditemukan.');
        }

        $request = $model->approvalRequest;

        if (!$request || !$request->isPending()) {
            throw new \RuntimeException('Approval ini sudah diproses sebelumnya.');
        }

        $this->workflowService->approve($request, $employeeId, 'Disetujui via Pusat Persetujuan.');

        // Trigger model's onApproved if status changed to approved
        if ($request->fresh()->isApproved()) {
            $model->onApproved();
        }

        return true;
    }

    protected function rejectWorkflow(string $module, int $id, int $employeeId, ?string $reason = null): bool
    {
        $model = $this->getModel($module, $id);

        if (!$model) {
            throw new \RuntimeException('Data tidak ditemukan.');
        }

        $request = $model->approvalRequest;

        if (!$request || !$request->isPending()) {
            throw new \RuntimeException('Approval ini sudah diproses sebelumnya.');
        }

        $this->workflowService->reject($request, $employeeId, $reason ?? 'Ditolak via Pusat Persetujuan.');
        $model->onRejected($reason ?? 'Ditolak via Pusat Persetujuan.');

        return true;
    }

    protected function approveGenericWorkflow(string $module, int $id, int $employeeId): bool
    {
        $request = ApprovalRequest::where('module', $module)
            ->where('module_id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$request) {
            throw new \RuntimeException('Approval tidak ditemukan atau sudah diproses.');
        }

        $this->workflowService->approve($request, $employeeId, 'Disetujui via Pusat Persetujuan.');
        return true;
    }

    protected function rejectGenericWorkflow(string $module, int $id, int $employeeId, ?string $reason = null): bool
    {
        $request = ApprovalRequest::where('module', $module)
            ->where('module_id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$request) {
            throw new \RuntimeException('Approval tidak ditemukan atau sudah diproses.');
        }

        $this->workflowService->reject($request, $employeeId, $reason ?? 'Ditolak via Pusat Persetujuan.');
        return true;
    }

    protected function approvePayment(int $id, User $user): bool
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            throw new \RuntimeException('Pembayaran sudah diproses sebelumnya.');
        }

        $payment->update([
            'status' => 'confirmed',
            'confirmed_by' => $user->id,
            'confirmed_at' => now(),
        ]);

        return true;
    }

    protected function rejectPayment(int $id, User $user, ?string $reason = null): bool
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status !== 'pending') {
            throw new \RuntimeException('Pembayaran sudah diproses sebelumnya.');
        }

        $payment->update([
            'status' => 'rejected',
            'confirmed_by' => $user->id,
            'confirmed_at' => now(),
            'notes' => $payment->notes . "\nAlasan penolakan: " . ($reason ?? 'Tidak disetujui'),
        ]);

        return true;
    }

    protected function approveContract(int $id, User $user): bool
    {
        $contract = Contract::findOrFail($id);

        if ($contract->status !== 'pending_approval') {
            throw new \RuntimeException('Kontrak sudah diproses sebelumnya.');
        }

        $contract->update([
            'status' => 'active',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return true;
    }

    protected function rejectContract(int $id, User $user, ?string $reason = null): bool
    {
        $contract = Contract::findOrFail($id);

        if ($contract->status !== 'pending_approval') {
            throw new \RuntimeException('Kontrak sudah diproses sebelumnya.');
        }

        $contract->update([
            'status' => 'draft',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return true;
    }

    protected function approveIntercompany(int $id, User $user): bool
    {
        $trx = IntercompanyTransaction::findOrFail($id);

        if ($trx->status !== 'pending_approval') {
            throw new \RuntimeException('Transaksi sudah diproses sebelumnya.');
        }

        $trx->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        app(IntercompanyTransactionService::class)->processApproval($trx->fresh());
        return true;
    }

    protected function rejectIntercompany(int $id, User $user, ?string $reason = null): bool
    {
        $trx = IntercompanyTransaction::findOrFail($id);

        if ($trx->status !== 'pending_approval') {
            throw new \RuntimeException('Transaksi sudah diproses sebelumnya.');
        }

        $trx->reject($user->id);
        return true;
    }

    protected function approvePayroll(int $id, User $user): bool
    {
        $payroll = Payroll::findOrFail($id);

        if ($payroll->status !== 'finalized') {
            throw new \RuntimeException('Penggajian sudah diproses sebelumnya.');
        }

        $payroll->update(['status' => 'paid']);
        return true;
    }

    protected function rejectPayroll(int $id, User $user, ?string $reason = null): bool
    {
        $payroll = Payroll::findOrFail($id);

        if ($payroll->status !== 'finalized') {
            throw new \RuntimeException('Penggajian sudah diproses sebelumnya.');
        }

        $payroll->update(['status' => 'draft', 'notes' => ($payroll->notes ?? '') . "\nDitolak: " . ($reason ?? 'Tidak disetujui')]);
        return true;
    }

    // ─── Model resolver ───────────────────────────────────────────────────

    protected function getModel(string $module, int $id): mixed
    {
        return match ($module) {
            'leave' => Leave::find($id),
            'overtime' => Overtime::find($id),
            'reimbursement' => Reimbursement::find($id),
            'purchase_requisition' => PurchaseRequisition::find($id),
            'purchase_order' => PurchaseOrder::find($id),
            'budget' => Budget::find($id),
            default => null,
        };
    }

    // ─── Formatting helpers ───────────────────────────────────────────────

    protected function resolveAmount(string $module, int $moduleId): ?float
    {
        $model = match ($module) {
            'reimbursement' => Reimbursement::find($moduleId),
            'purchase_order' => PurchaseOrder::find($moduleId),
            'budget' => Budget::withSum('budgetItems', 'amount')->find($moduleId),
            default => null,
        };

        if (!$model) {
            return null;
        }

        return match ($module) {
            'reimbursement' => (float) $model->amount,
            'purchase_order' => (float) $model->total,
            'budget' => (float) $model->budget_items_sum_amount,
            default => null,
        };
    }

    protected function resolveUrl(string $module, int $moduleId): string
    {
        return match ($module) {
            'leave' => '/admin/leaves/' . $moduleId . '/edit',
            'overtime' => '/admin/overtimes/' . $moduleId . '/edit',
            'reimbursement' => '/admin/reimbursements/' . $moduleId . '/edit',
            'purchase_requisition' => '/admin/purchase-requisitions/' . $moduleId . '/edit',
            'purchase_order' => '/admin/purchase-orders/' . $moduleId . '/edit',
            'budget' => '/admin/budgets/' . $moduleId . '/edit',
            default => '/admin/approval-requests/' . $moduleId,
        };
    }

    protected function moduleIcon(string $module): string
    {
        return match ($module) {
            'leave' => 'heroicon-o-calendar',
            'overtime' => 'heroicon-o-clock',
            'reimbursement' => 'heroicon-o-receipt-refund',
            'purchase_requisition' => 'heroicon-o-clipboard-document-list',
            'purchase_order' => 'heroicon-o-shopping-cart',
            'budget' => 'heroicon-o-chart-pie',
            'payment' => 'heroicon-o-banknotes',
            'contract' => 'heroicon-o-document-text',
            'intercompany_transaction' => 'heroicon-o-arrows-right-left',
            'payroll' => 'heroicon-o-currency-dollar',
            default => 'heroicon-o-document-check',
        };
    }

    protected function moduleColor(string $module): string
    {
        return match ($module) {
            'leave' => 'blue',
            'overtime' => 'gray',
            'reimbursement' => 'amber',
            'purchase_requisition' => 'rose',
            'purchase_order' => 'indigo',
            'budget' => 'emerald',
            'payment' => 'emerald',
            'contract' => 'purple',
            'intercompany_transaction' => 'cyan',
            'payroll' => 'orange',
            default => 'gray',
        };
    }

    protected function moduleLabel(string $module): string
    {
        return match ($module) {
            'leave' => 'Cuti',
            'overtime' => 'Lembur',
            'reimbursement' => 'Reimburse',
            'purchase_requisition' => 'Permintaan Pembelian',
            'purchase_order' => 'Pesanan Pembelian',
            'budget' => 'Anggaran',
            'payment' => 'Pembayaran',
            'contract' => 'Kontrak',
            'intercompany_transaction' => 'Antar Perusahaan',
            'payroll' => 'Penggajian',
            default => ucfirst($module),
        };
    }
}
