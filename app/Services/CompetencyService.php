<?php

namespace App\Services;

use App\Models\Competency;
use App\Models\Employee;
use App\Models\EmployeeCompetency;
use App\Models\Position;
use App\Models\PositionCompetency;
use Illuminate\Support\Collection;

class CompetencyService
{
    public function getPositionRequirements(Position $position): array
    {
        $requirements = PositionCompetency::where('position_id', $position->id)
            ->with('competency')
            ->get();

        return $requirements->map(fn($req) => [
            'competency_id' => $req->competency_id,
            'competency_name' => $req->competency->name,
            'competency_category' => $req->competency->category,
            'required_level' => $req->required_level,
            'weight' => $req->weight,
        ])->toArray();
    }

    public function getEmployeeGap(Employee $employee): array
    {
        if (!$employee->position_id) {
            return ['employee' => $employee->first_name . ' ' . $employee->last_name, 'gaps' => [], 'position' => null];
        }

        $requirements = PositionCompetency::where('position_id', $employee->position_id)
            ->with('competency')
            ->get();

        $currentLevels = EmployeeCompetency::where('employee_id', $employee->id)
            ->pluck('current_level', 'competency_id');

        $gaps = [];
        foreach ($requirements as $req) {
            $current = $currentLevels[$req->competency_id] ?? 0;
            $gap = $req->required_level - $current;

            $gaps[] = [
                'competency_id' => $req->competency_id,
                'competency' => $req->competency->name,
                'category' => $req->competency->category,
                'required_level' => $req->required_level,
                'current_level' => (int) $current,
                'gap' => $gap > 0 ? $gap : 0,
                'status' => $gap <= 0 ? 'terpenuhi' : ($gap <= 1 ? 'sedikit kurang' : 'perlu pengembangan'),
                'weight' => (float) $req->weight,
            ];
        }

        usort($gaps, fn($a, $b) => $b['gap'] <=> $a['gap']);

        $totalGap = array_sum(array_column($gaps, 'gap'));

        return [
            'employee' => $employee->first_name . ' ' . $employee->last_name,
            'position' => $employee->position?->name,
            'gaps' => $gaps,
            'total_gap' => $totalGap,
            'compliance_percent' => $requirements->count() > 0
                ? round((count(array_filter($gaps, fn($g) => $g['status'] === 'terpenuhi')) / $requirements->count()) * 100, 1)
                : 100,
        ];
    }

    public function getDepartmentSkillsMatrix(int $departmentId): array
    {
        $employees = Employee::where('department_id', $departmentId)
            ->where('status', 'active')
            ->with(['position', 'employeeCompetencies.competency'])
            ->get();

        $competencyIds = PositionCompetency::whereIn('position_id', $employees->pluck('position_id')->filter())
            ->pluck('competency_id')
            ->unique();

        $competencies = Competency::whereIn('id', $competencyIds)->get();

        $matrix = [];
        foreach ($employees as $employee) {
            $row = [
                'employee' => $employee->first_name . ' ' . $employee->last_name,
                'position' => $employee->position?->name,
            ];

            foreach ($competencies as $comp) {
                $current = $employee->employeeCompetencies
                    ->where('competency_id', $comp->id)
                    ->first()?->current_level ?? 0;

                $required = PositionCompetency::where('position_id', $employee->position_id)
                    ->where('competency_id', $comp->id)
                    ->first()?->required_level ?? 0;

                $gap = $required - $current;
                $color = $gap <= 0 ? 'green' : ($gap <= 1 ? 'yellow' : 'red');

                $row['competencies'][] = [
                    'name' => $comp->name,
                    'current' => $current,
                    'required' => $required,
                    'gap' => $gap,
                    'color' => $color,
                ];
            }

            $matrix[] = $row;
        }

        return [
            'department' => \App\Models\Department::find($departmentId)?->name,
            'competencies' => $competencies->pluck('name')->toArray(),
            'matrix' => $matrix,
        ];
    }

    public function suggestTraining(Employee $employee): array
    {
        $gap = $this->getEmployeeGap($employee);
        $unmetGaps = array_filter($gap['gaps'], fn($g) => $g['status'] !== 'terpenuhi');

        $suggestions = [];
        foreach ($unmetGaps as $missing) {
            $courses = \App\Models\Course::where(function ($q) use ($missing) {
                $q->where('title', 'like', "%{$missing['competency']}%")
                  ->orWhere('description', 'like', "%{$missing['competency']}%");
            })->limit(3)->get();

            $suggestions[] = [
                'competency' => $missing['competency'],
                'gap' => $missing['gap'],
                'suggested_courses' => $courses->map(fn($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'description' => $c->description,
                ])->toArray(),
            ];
        }

        return $suggestions;
    }

    public function getOrganizationSkillGap(): array
    {
        $competencies = Competency::with('positionCompetencies')->get();
        $employeeLevels = EmployeeCompetency::with('employee.position')->get();

        $gapSummary = [];
        foreach ($competencies as $comp) {
            $requiredAvg = $comp->positionCompetencies->avg('required_level') ?? 0;
            $currentAvg = $employeeLevels->where('competency_id', $comp->id)->avg('current_level') ?? 0;

            $gapSummary[] = [
                'competency' => $comp->name,
                'category' => $comp->category,
                'avg_required' => round($requiredAvg, 2),
                'avg_current' => round($currentAvg, 2),
                'gap' => round($requiredAvg - $currentAvg, 2),
            ];
        }

        usort($gapSummary, fn($a, $b) => $b['gap'] <=> $a['gap']);

        return [
            'top_gaps' => array_slice($gapSummary, 0, 10),
            'total_competencies' => count($gapSummary),
            'average_organization_gap' => count($gapSummary) > 0
                ? round(array_sum(array_column($gapSummary, 'gap')) / count($gapSummary), 2)
                : 0,
        ];
    }
}
