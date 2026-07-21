<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Position;
use App\Models\SuccessionPlan;
use Illuminate\Support\Collection;

class SuccessionService
{
    public function getSuccessionGap(): array
    {
        $positions = Position::where('is_active', true)->get();
        $plannedPositionIds = SuccessionPlan::pluck('position_id')->unique()->toArray();

        $gaps = $positions->filter(fn($p) => !in_array($p->id, $plannedPositionIds));

        return [
            'total_positions' => $positions->count(),
            'planned_positions' => count($plannedPositionIds),
            'gap_positions' => $gaps->count(),
            'gaps' => $gaps->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'department' => $p->department?->name,
            ])->values()->toArray(),
        ];
    }

    public function getReadinessMatrix(): array
    {
        $plans = SuccessionPlan::with(['position.department', 'successor', 'currentIncumbent'])->get();

        $matrix = [];
        foreach ($plans as $plan) {
            $readiness = $plan->readiness;
            $risk = $plan->risk_level;

            $matrix[] = [
                'position' => $plan->position?->name,
                'department' => $plan->position?->department?->name,
                'incumbent' => $plan->currentIncumbent?->first_name . ' ' . $plan->currentIncumbent?->last_name,
                'successor' => $plan->successor?->first_name . ' ' . $plan->successor?->last_name,
                'readiness' => $readiness,
                'risk_level' => $risk,
                'readiness_label' => $this->readinessLabel($readiness),
                'risk_label' => $this->riskLabel($risk),
            ];
        }

        return [
            'matrix' => $matrix,
            'summary' => [
                'ready_now' => $plans->where('readiness', 'ready_now')->count(),
                '1_year' => $plans->where('readiness', '1_year')->count(),
                '2_years' => $plans->where('readiness', '2_years')->count(),
                '3_plus_years' => $plans->where('readiness', '3_plus_years')->count(),
                'high_risk' => $plans->where('risk_level', 'high')->count(),
                'medium_risk' => $plans->where('risk_level', 'medium')->count(),
                'low_risk' => $plans->where('risk_level', 'low')->count(),
            ],
        ];
    }

    public function getSuccessionPipeline(Position $position): array
    {
        $plan = SuccessionPlan::where('position_id', $position->id)
            ->with(['successor', 'currentIncumbent'])
            ->first();

        if (!$plan) {
            return [
                'has_plan' => false,
                'position' => $position->name,
                'message' => 'Belum ada rencana suksesi untuk posisi ini.',
            ];
        }

        return [
            'has_plan' => true,
            'position' => $position->name,
            'incumbent' => $plan->currentIncumbent?->first_name . ' ' . $plan->currentIncumbent?->last_name,
            'successor' => $plan->successor?->first_name . ' ' . $plan->successor?->last_name,
            'readiness' => $this->readinessLabel($plan->readiness),
            'risk' => $this->riskLabel($plan->risk_level),
            'development_plan' => $plan->development_plan,
            'notes' => $plan->notes,
        ];
    }

    public function identifyHighPotentials(): Collection
    {
        return Employee::where('status', 'active')
            ->where('join_date', '>=', now()->subYears(5))
            ->with(['position', 'department', 'grade'])
            ->get()
            ->sortByDesc(function ($employee) {
                $tenure = now()->diffInYears($employee->join_date);
                $score = 0;
                if ($tenure >= 2) $score += 20;
                if ($employee->grade && $employee->grade->name) $score += 15;
                if ($employee->position) $score += 10;
                return $score;
            })
            ->take(20);
    }

    protected function readinessLabel(string $readiness): string
    {
        return match($readiness) {
            'ready_now' => 'Siap Sekarang',
            '1_year' => '1 Tahun',
            '2_years' => '2 Tahun',
            '3_plus_years' => '3+ Tahun',
            default => $readiness,
        };
    }

    protected function riskLabel(string $risk): string
    {
        return match($risk) {
            'high' => 'Tinggi',
            'medium' => 'Sedang',
            'low' => 'Rendah',
            default => $risk,
        };
    }
}
