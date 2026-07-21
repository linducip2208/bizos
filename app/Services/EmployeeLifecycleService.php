<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\OnboardingChecklist;
use App\Models\OnboardingChecklistItem;
use App\Models\OnboardingProgress;
use App\Models\OnboardingProgressItem;
use App\Models\OffboardingChecklist;
use App\Models\OffboardingChecklistItem;
use App\Models\OffboardingProgress;
use App\Models\OffboardingProgressItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EmployeeLifecycleService
{
    // ============================================================
    // ONBOARDING
    // ============================================================

    public function startOnboarding(Employee $employee, ?OnboardingChecklist $checklist = null): OnboardingProgress
    {
        $checklist = $checklist ?? OnboardingChecklist::where('company_id', $employee->company_id)
            ->where('is_default', true)
            ->first();

        if (!$checklist) {
            throw new \RuntimeException('Tidak ada checklist onboarding yang tersedia. Buat checklist default terlebih dahulu.');
        }

        $existing = OnboardingProgress::where('employee_id', $employee->id)
            ->where('checklist_id', $checklist->id)
            ->where('status', '!=', 'completed')
            ->first();

        if ($existing) {
            return $existing;
        }

        $progress = OnboardingProgress::create([
            'employee_id' => $employee->id,
            'checklist_id' => $checklist->id,
            'started_at' => $employee->join_date ?? now(),
            'status' => 'in_progress',
        ]);

        foreach ($checklist->items as $item) {
            OnboardingProgressItem::create([
                'progress_id' => $progress->id,
                'checklist_item_id' => $item->id,
                'status' => 'pending',
                'notes' => $this->generateContextualNotes($item, $employee),
            ]);
        }

        return $progress->load('items.checklistItem');
    }

    public function getOnboardingTasks(Employee $employee): Collection
    {
        return OnboardingProgressItem::whereHas('progress', function ($q) use ($employee) {
            $q->where('employee_id', $employee->id)
                ->where('status', '!=', 'completed');
        })->with(['checklistItem', 'progress.checklist'])->get();
    }

    public function completeTask(OnboardingProgressItem $item, ?string $notes = null): void
    {
        $item->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $notes ?? $item->notes,
        ]);

        $this->checkAndCompleteOnboarding($item->progress);
    }

    public function skipTask(OnboardingProgressItem $item, ?string $notes = null): void
    {
        $item->update([
            'status' => 'skipped',
            'notes' => $notes ?? $item->notes,
        ]);

        $this->checkAndCompleteOnboarding($item->progress);
    }

    public function getOnboardingProgress(Employee $employee): array
    {
        $progress = OnboardingProgress::where('employee_id', $employee->id)
            ->where('status', '!=', 'completed')
            ->with(['items.checklistItem'])
            ->first();

        if (!$progress) {
            return ['overall' => 0, 'by_role' => [], 'total_items' => 0, 'completed_items' => 0];
        }

        $total = $progress->items->count();
        $completed = $progress->items->where('status', 'completed')->count();
        $overall = $total > 0 ? round(($completed / $total) * 100) : 0;

        $byRole = [];
        foreach ($progress->items as $item) {
            $role = $item->checklistItem?->assigned_role ?? 'lainnya';
            $roleLabel = match ($role) {
                'hr' => 'HR',
                'it' => 'IT',
                'finance' => 'Keuangan',
                'manager' => 'Manager',
                'employee' => 'Karyawan',
                default => 'Lainnya',
            };

            if (!isset($byRole[$role])) {
                $byRole[$role] = ['label' => $roleLabel, 'total' => 0, 'completed' => 0, 'percent' => 0];
            }
            $byRole[$role]['total']++;
            if ($item->status === 'completed') {
                $byRole[$role]['completed']++;
            }
        }

        foreach ($byRole as &$role) {
            $role['percent'] = $role['total'] > 0 ? round(($role['completed'] / $role['total']) * 100) : 0;
        }

        return [
            'overall' => $overall,
            'by_role' => array_values($byRole),
            'total_items' => $total,
            'completed_items' => $completed,
        ];
    }

    private function checkAndCompleteOnboarding(OnboardingProgress $progress): void
    {
        $progress->load('items.checklistItem');
        $required = $progress->items->filter(fn ($i) => $i->checklistItem?->is_required);
        $allRequiredDone = $required->every(fn ($i) => $i->status === 'completed' || $i->status === 'skipped');

        if ($allRequiredDone) {
            $progress->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }
    }

    private function generateContextualNotes(OnboardingChecklistItem $item, Employee $employee): ?string
    {
        return match ($item->assigned_role) {
            'it' => "Siapkan akun email & akses sistem untuk {$employee->first_name}",
            'hr' => "Proses dokumen kepegawaian untuk {$employee->first_name}",
            'finance' => "Siapkan data payroll & rekening untuk {$employee->first_name}",
            'manager' => "Briefing awal & perkenalan tim untuk {$employee->first_name}",
            default => null,
        };
    }

    // ============================================================
    // OFFBOARDING
    // ============================================================

    public function startOffboarding(Employee $employee, Carbon $resignationDate, Carbon $lastWorkingDate, string $reason): OffboardingProgress
    {
        $checklist = OffboardingChecklist::where('company_id', $employee->company_id)
            ->where('is_default', true)
            ->first();

        if (!$checklist) {
            throw new \RuntimeException('Tidak ada checklist offboarding yang tersedia. Buat checklist default terlebih dahulu.');
        }

        $existing = OffboardingProgress::where('employee_id', $employee->id)
            ->where('checklist_id', $checklist->id)
            ->where('clearance_status', '!=', 'completed')
            ->first();

        if ($existing) {
            return $existing;
        }

        $settlement = $this->calculateFinalSettlement($employee, $lastWorkingDate);

        $progress = OffboardingProgress::create([
            'employee_id' => $employee->id,
            'checklist_id' => $checklist->id,
            'resignation_date' => $resignationDate,
            'last_working_date' => $lastWorkingDate,
            'final_settlement_amount' => $settlement['total'],
            'clearance_status' => 'pending',
            'exit_interview_notes' => "Alasan pengunduran diri: {$reason}",
        ]);

        foreach ($checklist->items as $item) {
            OffboardingProgressItem::create([
                'progress_id' => $progress->id,
                'checklist_item_id' => $item->id,
                'status' => 'pending',
            ]);
        }

        $employee->update([
            'status' => 'resigned',
            'termination_date' => $lastWorkingDate,
            'termination_reason' => $reason,
        ]);

        return $progress->load('items.checklistItem');
    }

    public function calculateFinalSettlement(Employee $employee, Carbon $lastWorkingDate): array
    {
        $basicSalary = (float) $employee->basic_salary;
        $joinDate = $employee->join_date;
        $yearsOfService = $joinDate ? $joinDate->diffInYears($lastWorkingDate) : 0;

        $pesangon = $this->calculatePesangon($basicSalary, $yearsOfService);
        $upmk = $this->calculateUPMK($basicSalary, $yearsOfService);
        $uph = 0.15 * ($pesangon + $upmk);
        $sisaCutiBalance = $this->calculateLeaveBalanceSettlement($employee, $basicSalary);
        $thrProRata = $this->calculateThrProRata($employee, $lastWorkingDate);

        $total = $pesangon + $upmk + $uph + $sisaCutiBalance + $thrProRata;

        return [
            'pesangon' => $pesangon,
            'upmk' => $upmk,
            'uph' => $uph,
            'sisa_cuti_balance' => $sisaCutiBalance,
            'thr_pro_rata' => $thrProRata,
            'total' => $total,
            'years_of_service' => $yearsOfService,
            'basic_salary' => $basicSalary,
        ];
    }

    private function calculatePesangon(float $basicSalary, int $yearsOfService): float
    {
        $multiplier = match (true) {
            $yearsOfService < 1 => 1,
            $yearsOfService < 2 => 2,
            $yearsOfService < 3 => 3,
            $yearsOfService < 4 => 4,
            $yearsOfService < 5 => 5,
            $yearsOfService < 6 => 6,
            $yearsOfService < 7 => 7,
            $yearsOfService < 8 => 8,
            default => 9,
        };

        return $multiplier * $basicSalary;
    }

    private function calculateUPMK(float $basicSalary, int $yearsOfService): float
    {
        $multiplier = match (true) {
            $yearsOfService < 3 => 0,
            $yearsOfService < 6 => 2,
            $yearsOfService < 9 => 3,
            $yearsOfService < 12 => 4,
            $yearsOfService < 15 => 5,
            $yearsOfService < 18 => 6,
            $yearsOfService < 21 => 7,
            $yearsOfService < 24 => 8,
            default => 10,
        };

        return $multiplier * $basicSalary;
    }

    private function calculateLeaveBalanceSettlement(Employee $employee, float $basicSalary): float
    {
        $leaveBalance = $employee->leaveBalances()->sum('remaining_days') ?? 0;
        if ($leaveBalance <= 0) {
            return 0;
        }
        $dailyRate = $basicSalary / 25;
        return $leaveBalance * $dailyRate;
    }

    private function calculateThrProRata(Employee $employee, Carbon $lastWorkingDate): float
    {
        $basicSalary = (float) $employee->basic_salary;
        $currentYear = $lastWorkingDate->year;
        $startOfYear = Carbon::create($currentYear, 1, 1);
        $monthsWorked = $startOfYear->diffInMonths($lastWorkingDate);

        if ($monthsWorked < 1) {
            return 0;
        }
        if ($monthsWorked >= 12) {
            return $basicSalary;
        }

        return ($monthsWorked / 12) * $basicSalary;
    }

    public function completeClearance(OffboardingProgress $progress, string $department): void
    {
        $statusMap = [
            'it' => 'it_clear',
            'finance' => 'finance_clear',
            'hr' => 'hr_clear',
            'asset' => 'asset_clear',
        ];

        $newStatus = $statusMap[$department] ?? null;
        if (!$newStatus) {
            throw new \RuntimeException("Departemen tidak valid: {$department}");
        }

        $progress->update(['clearance_status' => $newStatus]);

        $progress->load('items');
        $allComplete = $progress->items->every(fn ($i) => in_array($i->status, ['completed', 'skipped']));

        if ($allComplete) {
            $progress->update(['clearance_status' => 'completed']);
        }
    }

    public function getExitInterviewSummary(OffboardingProgress $progress): array
    {
        $employee = $progress->employee;
        return [
            'employee_name' => $employee->first_name . ' ' . ($employee->last_name ?? ''),
            'department' => $employee->department?->name,
            'position' => $employee->position?->name,
            'join_date' => $employee->join_date?->format('d M Y'),
            'last_working_date' => $progress->last_working_date->format('d M Y'),
            'years_of_service' => $employee->join_date?->diffInYears($progress->last_working_date),
            'exit_interview_notes' => $progress->exit_interview_notes,
            'clearance_status' => $progress->clearance_status,
            'final_settlement' => $progress->final_settlement_amount,
            'completed_items' => $progress->items()->where('status', 'completed')->count(),
            'total_items' => $progress->items()->count(),
        ];
    }
}
