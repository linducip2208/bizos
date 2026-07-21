<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\SodConflict;
use App\Models\SodRule;
use App\Models\User;
use Illuminate\Support\Collection;

class SodService
{
    /**
     * Create a new SoD conflict rule.
     */
    public function createRule(string $sensitiveFunction, string $conflictingFunction, string $riskLevel): SodRule
    {
        return SodRule::create([
            'company_id' => auth()->user()?->company_id ?? 1,
            'name' => "{$sensitiveFunction} vs {$conflictingFunction}",
            'sensitive_function' => $sensitiveFunction,
            'conflicting_function' => $conflictingFunction,
            'risk_level' => $riskLevel,
            'is_system_default' => false,
        ]);
    }

    /**
     * Check a user for all SoD conflicts.
     * Returns array of conflicts with rule, risk level, and recommendation.
     */
    public function checkUserConflicts(User $user): array
    {
        $userPermissions = $this->getUserPermissionNames($user);
        $rules = SodRule::where('is_active', true)->get();
        $conflicts = [];

        foreach ($rules as $rule) {
            $hasSensitive = in_array($rule->sensitive_function, $userPermissions);
            $hasConflicting = in_array($rule->conflicting_function, $userPermissions);

            if ($hasSensitive && $hasConflicting) {
                $conflicts[] = [
                    'rule_id' => $rule->id,
                    'rule_name' => $rule->name,
                    'sensitive_function' => $rule->sensitive_function,
                    'conflicting_function' => $rule->conflicting_function,
                    'risk_level' => $rule->risk_level,
                    'recommendation' => $this->getRecommendation($rule),
                    'compensating_controls' => $rule->compensating_controls,
                ];
            }
        }

        $this->logConflicts($user, $conflicts);

        return $conflicts;
    }

    /**
     * Validate permission assignment before granting.
     */
    public function validateAssignment(User $user, Permission $permission): array
    {
        $conflicts = [];

        $rules = SodRule::where('is_active', true)
            ->where(function ($q) use ($permission) {
                $q->where('sensitive_function', $permission->slug)
                    ->orWhere('conflicting_function', $permission->slug);
            })
            ->get();

        $userPermissions = $this->getUserPermissionNames($user);

        foreach ($rules as $rule) {
            if ($rule->sensitive_function === $permission->slug) {
                if (in_array($rule->conflicting_function, $userPermissions)) {
                    $conflicts[] = [
                        'rule' => $rule->name,
                        'conflict_with' => $rule->conflicting_function,
                        'risk_level' => $rule->risk_level,
                    ];
                }
            } elseif ($rule->conflicting_function === $permission->slug) {
                if (in_array($rule->sensitive_function, $userPermissions)) {
                    $conflicts[] = [
                        'rule' => $rule->name,
                        'conflict_with' => $rule->sensitive_function,
                        'risk_level' => $rule->risk_level,
                    ];
                }
            }
        }

        return [
            'allowed' => empty($conflicts),
            'conflicts' => $conflicts,
        ];
    }

    /**
     * Generate SoD conflict matrix.
     */
    public function getConflictMatrix(): array
    {
        $rules = SodRule::where('is_active', true)->get();
        $functions = [];

        foreach ($rules as $rule) {
            $functions[$rule->sensitive_function] = true;
            $functions[$rule->conflicting_function] = true;
        }

        $functions = array_keys($functions);
        sort($functions);
        $matrix = [];

        foreach ($functions as $row) {
            $matrix[$row] = [];
            foreach ($functions as $col) {
                if ($row === $col) {
                    $matrix[$row][$col] = '-';
                } else {
                    $conflict = $rules->first(function ($r) use ($row, $col) {
                        return ($r->sensitive_function === $row && $r->conflicting_function === $col)
                            || ($r->sensitive_function === $col && $r->conflicting_function === $row);
                    });
                    $matrix[$row][$col] = $conflict ? $conflict->risk_level : 'safe';
                }
            }
        }

        return [
            'functions' => $functions,
            'matrix' => $matrix,
        ];
    }

    /**
     * Get pre-built Indonesian ERP SoD rules (20+ standard rules).
     */
    public function getDefaultRules(): array
    {
        return [
            // Procurement
            ['sensitive' => 'buat-pr', 'conflicting' => 'approve-pr', 'risk' => 'high', 'desc' => 'Buat Purchase Requisition vs Approve PR'],
            ['sensitive' => 'buat-po', 'conflicting' => 'approve-po', 'risk' => 'critical', 'desc' => 'Buat Purchase Order vs Approve PO'],
            ['sensitive' => 'buat-po', 'conflicting' => 'terima-barang', 'risk' => 'high', 'desc' => 'Buat PO vs Terima Barang'],
            ['sensitive' => 'vendor-master', 'conflicting' => 'buat-po', 'risk' => 'high', 'desc' => 'Master Vendor vs Buat PO'],
            ['sensitive' => 'vendor-master', 'conflicting' => 'approve-po', 'risk' => 'critical', 'desc' => 'Master Vendor vs Approve PO'],
            ['sensitive' => 'vendor-master', 'conflicting' => 'approve-invoice', 'risk' => 'critical', 'desc' => 'Master Vendor vs Approve Invoice'],

            // Finance
            ['sensitive' => 'buat-invoice', 'conflicting' => 'approve-invoice', 'risk' => 'critical', 'desc' => 'Buat Invoice vs Approve Invoice'],
            ['sensitive' => 'buat-journal', 'conflicting' => 'approve-journal', 'risk' => 'high', 'desc' => 'Buat Journal Entry vs Approve Journal'],
            ['sensitive' => 'payment-run', 'conflicting' => 'bank-reconciliation', 'risk' => 'high', 'desc' => 'Payment Run vs Bank Reconciliation'],
            ['sensitive' => 'payment-run', 'conflicting' => 'approve-payment', 'risk' => 'critical', 'desc' => 'Payment Run vs Approve Payment'],
            ['sensitive' => 'setup-coa', 'conflicting' => 'buat-journal', 'risk' => 'medium', 'desc' => 'Setup COA vs Buat Journal'],
            ['sensitive' => 'manage-budget', 'conflicting' => 'approve-po', 'risk' => 'medium', 'desc' => 'Manage Budget vs Approve PO'],

            // Payroll
            ['sensitive' => 'input-payroll', 'conflicting' => 'approve-payroll', 'risk' => 'critical', 'desc' => 'Input Payroll vs Approve Payroll'],
            ['sensitive' => 'employee-master', 'conflicting' => 'input-payroll', 'risk' => 'high', 'desc' => 'Master Karyawan vs Input Payroll'],
            ['sensitive' => 'employee-master', 'conflicting' => 'approve-payroll', 'risk' => 'critical', 'desc' => 'Master Karyawan vs Approve Payroll'],

            // HR
            ['sensitive' => 'input-attendance', 'conflicting' => 'approve-attendance', 'risk' => 'medium', 'desc' => 'Input Attendance vs Approve Attendance'],
            ['sensitive' => 'manage-leave', 'conflicting' => 'approve-leave', 'risk' => 'medium', 'desc' => 'Manage Leave vs Approve Leave'],

            // Asset
            ['sensitive' => 'asset-disposal', 'conflicting' => 'approve-disposal', 'risk' => 'high', 'desc' => 'Asset Disposal vs Approve Disposal'],
            ['sensitive' => 'asset-record', 'conflicting' => 'asset-verification', 'risk' => 'medium', 'desc' => 'Asset Record vs Asset Verification'],

            // Inventory
            ['sensitive' => 'stock-adjustment', 'conflicting' => 'approve-adjustment', 'risk' => 'high', 'desc' => 'Stock Adjustment vs Approve Adjustment'],
            ['sensitive' => 'warehouse-master', 'conflicting' => 'stock-movement', 'risk' => 'medium', 'desc' => 'Master Gudang vs Stock Movement'],

            // Sales
            ['sensitive' => 'set-pricing', 'conflicting' => 'create-sales-order', 'risk' => 'medium', 'desc' => 'Set Pricing vs Create Sales Order'],
            ['sensitive' => 'manage-discount', 'conflicting' => 'approve-sales', 'risk' => 'high', 'desc' => 'Manage Discount vs Approve Sales'],

            // System
            ['sensitive' => 'manage-role', 'conflicting' => 'manage-user', 'risk' => 'critical', 'desc' => 'Manage Role vs Manage User'],
        ];
    }

    /**
     * Add compensating control for a rule.
     */
    public function addCompensatingControl(SodRule $rule, string $control): void
    {
        $existing = $rule->compensating_controls ? json_decode($rule->compensating_controls, true) : [];
        $existing[] = $control;
        $rule->update(['compensating_controls' => json_encode($existing)]);
    }

    /**
     * Resolve a detected SoD conflict.
     */
    public function resolveConflict(SodConflict $conflict, string $resolution, ?int $resolvedBy = null): void
    {
        $conflict->update([
            'status' => 'resolved',
            'resolution' => $resolution,
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy ?? auth()->id(),
        ]);
    }

    /**
     * Get all active conflicts.
     */
    public function getActiveConflicts(): Collection
    {
        return SodConflict::with(['user', 'rule'])
            ->where('status', 'detected')
            ->orderBy('risk_level', 'desc')
            ->get();
    }

    /**
     * Get all conflicts for a specific user.
     */
    public function getUserConflicts(int $userId): Collection
    {
        return SodConflict::with('rule')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Scan all users for SoD conflicts.
     */
    public function scanAllUsers(): array
    {
        $users = User::whereHas('role')->get();
        $totalConflicts = 0;

        foreach ($users as $user) {
            $conflicts = $this->checkUserConflicts($user);
            $totalConflicts += count($conflicts);
        }

        return [
            'users_scanned' => $users->count(),
            'total_conflicts' => $totalConflicts,
            'scanned_at' => now()->toISOString(),
        ];
    }

    /**
     * Generate recommendation for a conflict.
     */
    private function getRecommendation(SodRule $rule): string
    {
        return match($rule->risk_level) {
            'critical' => 'SEGERA: Hapus salah satu izin. Butuh approval minimal 2 level di atas.',
            'high' => 'Rekomendasi: Pisahkan ke 2 user berbeda. Tambahkan compensating control.',
            'medium' => 'Monitor: Tambahkan review berkala oleh supervisor.',
            default => 'Aman dengan monitoring rutin.',
        };
    }

    /**
     * Log detected conflicts.
     */
    private function logConflicts(User $user, array $conflicts): void
    {
        foreach ($conflicts as $conflict) {
            $existing = SodConflict::where('user_id', $user->id)
                ->where('sod_rule_id', $conflict['rule_id'])
                ->where('status', 'detected')
                ->exists();

            if (!$existing) {
                SodConflict::create([
                    'company_id' => $user->company_id ?? 1,
                    'sod_rule_id' => $conflict['rule_id'],
                    'user_id' => $user->id,
                    'sensitive_permission' => $conflict['sensitive_function'],
                    'conflicting_permission' => $conflict['conflicting_function'],
                    'risk_level' => $conflict['risk_level'],
                    'status' => 'detected',
                    'detected_at' => now(),
                ]);
            }
        }
    }

    /**
     * Get user's permission names from role.
     */
    private function getUserPermissionNames(User $user): array
    {
        if (!$user->role) return [];

        return $user->role->permissions()->pluck('slug')->toArray();
    }
}
