<?php

namespace App\Services\Dashboard;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

final class ProjectDashboardService extends AbstractDashboardService
{
    protected function build(DashboardFilter $filter, User $user): array
    {
        $projects = $this->tenant(Project::query(), $filter, false)
            ->when($filter->departmentId, fn ($q) => $q->where('department_id', $filter->departmentId))
            ->when($filter->projectId, fn ($q) => $q->whereKey($filter->projectId));
        $projectIds = (clone $projects)->pluck('id');

        return ['cards' => [
            ['label' => 'Proyek Aktif', 'value' => (clone $projects)->whereIn('status', ['planning', 'active'])->count(), 'format' => 'number'],
            ['label' => 'Nilai Anggaran', 'value' => (float) (clone $projects)->sum('budget'), 'format' => 'currency'],
            ['label' => 'Biaya Aktual', 'value' => (float) (clone $projects)->sum('actual_cost'), 'format' => 'currency'],
            ['label' => 'Tugas Terlambat', 'value' => Task::query()->whereIn('project_id', $projectIds)->whereNotIn('status', ['done', 'completed'])->whereDate('due_date', '<', today())->count(), 'format' => 'number'],
        ]];
    }
}
