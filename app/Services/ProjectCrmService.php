<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectPhase;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProjectCrmService
{
    /**
     * Link project ke deal (post-sales delivery).
     */
    public function linkProjectToDeal(Project $project, Deal $deal): void
    {
        DB::transaction(function () use ($project, $deal) {
            $project->update([
                'client_id' => $deal->client_id,
                'deal_id' => $deal->id,
            ]);

            $deal->update(['project_id' => $project->id]);
        });
    }

    /**
     * Auto-create project dari deal (post-sales delivery).
     * Auto: project name = deal name, client = deal client, manager = deal owner.
     */
    public function createProjectFromDeal(Deal $deal, ?string $projectName = null): Project
    {
        return DB::transaction(function () use ($deal, $projectName) {
            if ($deal->status !== 'won' && $deal->status !== 'closed_won') {
                throw new \InvalidArgumentException('Hanya deal dengan status "won" yang dapat dikonversi ke project.');
            }

            $projectCode = $this->generateProjectCode($deal->company_id);

            $project = Project::create([
                'company_id' => $deal->company_id,
                'client_id' => $deal->client_id,
                'deal_id' => $deal->id,
                'manager_id' => $deal->assigned_to,
                'code' => $projectCode,
                'name' => $projectName ?? 'Proyek: ' . $deal->title,
                'description' => 'Auto-generated dari Deal #' . $deal->id . ': ' . $deal->title
                    . "\nNilai Kontrak: Rp " . number_format((float) $deal->expected_value, 0, ',', '.')
                    . ($deal->notes ? "\n\nCatatan Deal:\n" . $deal->notes : ''),
                'start_date' => now(),
                'end_date' => $deal->expected_close_date ?? now()->addMonths(3),
                'budget' => (float) ($deal->expected_value * 0.7),
                'actual_cost' => 0,
                'status' => 'planning',
                'priority' => 'high',
                'progress_percent' => 0,
            ]);

            if ($deal->assigned_to) {
                ProjectMember::create([
                    'project_id' => $project->id,
                    'employee_id' => $deal->assigned_to,
                    'role' => 'manager',
                    'joined_at' => now(),
                ]);
            }

            $defaultPhases = [
                ['name' => 'Inisiasi', 'weight' => 10, 'order' => 1],
                ['name' => 'Perencanaan', 'weight' => 15, 'order' => 2],
                ['name' => 'Eksekusi', 'weight' => 40, 'order' => 3],
                ['name' => 'Pengujian', 'weight' => 15, 'order' => 4],
                ['name' => 'Serah Terima', 'weight' => 10, 'order' => 5],
                ['name' => 'Go-Live & Support', 'weight' => 10, 'order' => 6],
            ];

            foreach ($defaultPhases as $phase) {
                $startDate = now()->addDays(($phase['order'] - 1) * 14);
                $endDate = $startDate->copy()->addDays(14);

                ProjectPhase::create([
                    'project_id' => $project->id,
                    'name' => $phase['name'],
                    'description' => 'Fase ' . $phase['name'] . ' — auto-generated dari deal',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'weight' => $phase['weight'],
                    'sort_order' => $phase['order'],
                    'status' => 'pending',
                    'progress_percent' => 0,
                ]);
            }

            $deal->update(['project_id' => $project->id]);

            return $project;
        });
    }

    /**
     * Progress update dari project ke CRM (update deal status/progress).
     */
    public function syncProgressToDeal(Project $project): void
    {
        DB::transaction(function () use ($project) {
            if (!$project->deal_id) return;

            $deal = Deal::find($project->deal_id);
            if (!$deal) return;

            $projectProgress = (float) $project->progress_percent;

            $dealStatus = match (true) {
                $projectProgress >= 100 => 'completed',
                $projectProgress >= 75 => 'fulfilling',
                $projectProgress >= 50 => 'fulfilling',
                $projectProgress >= 25 => 'fulfilling',
                $projectProgress > 0 => 'won',
                default => $deal->status,
            };

            $deal->update([
                'status' => $dealStatus,
            ]);

            if ($project->status === 'completed' && $deal->status !== 'completed') {
                $deal->update([
                    'status' => 'completed',
                    'actual_close_date' => now(),
                ]);
            }

            \Illuminate\Support\Facades\Log::info('Project progress synced to deal', [
                'project_id' => $project->id,
                'deal_id' => $deal->id,
                'project_progress' => $projectProgress,
                'deal_status' => $dealStatus,
            ]);
        });
    }

    /**
     * Dapatkan semua project dari client.
     */
    public function getClientProjects(Client $client): array
    {
        return Project::where('client_id', $client->id)
            ->with(['manager', 'deal'])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'code' => $project->code,
                    'name' => $project->name,
                    'status' => $project->status,
                    'progress' => (float) $project->progress_percent,
                    'budget' => (float) $project->budget,
                    'actual_cost' => (float) $project->actual_cost,
                    'manager' => $project->manager
                        ? $project->manager->first_name . ' ' . $project->manager->last_name
                        : '-',
                    'deal_title' => $project->deal->title ?? '-',
                    'start_date' => $project->start_date?->format('Y-m-d'),
                    'end_date' => $project->end_date?->format('Y-m-d'),
                    'profit_margin' => $project->budget > 0
                        ? round((1 - ((float) $project->actual_cost / (float) $project->budget)) * 100, 1)
                        : 0,
                ];
            })
            ->toArray();
    }

    /**
     * Dapatkan ringkasan relasi project-deal untuk dashboard.
     */
    public function getProjectDealOverview(int $companyId): array
    {
        $totalProjects = Project::where('company_id', $companyId)->count();
        $activeProjects = Project::where('company_id', $companyId)
            ->whereIn('status', ['planning', 'in_progress'])
            ->count();

        $linkedDeals = Deal::where('company_id', $companyId)
            ->whereNotNull('project_id')
            ->count();

        $unlinkedWonDeals = Deal::where('company_id', $companyId)
            ->whereIn('status', ['won', 'closed_won'])
            ->whereNull('project_id')
            ->count();

        $totalDealValue = (float) Deal::where('company_id', $companyId)
            ->whereIn('status', ['won', 'closed_won', 'fulfilling', 'completed'])
            ->sum('expected_value');

        $totalProjectBudget = (float) Project::where('company_id', $companyId)->sum('budget');
        $totalProjectCost = (float) Project::where('company_id', $companyId)->sum('actual_cost');

        $overallMargin = $totalProjectBudget > 0
            ? round((1 - ($totalProjectCost / $totalProjectBudget)) * 100, 1)
            : 0;

        return [
            'total_projects' => $totalProjects,
            'active_projects' => $activeProjects,
            'linked_deals' => $linkedDeals,
            'unlinked_won_deals' => $unlinkedWonDeals,
            'total_deal_value' => $totalDealValue,
            'total_project_budget' => $totalProjectBudget,
            'total_project_cost' => $totalProjectCost,
            'overall_margin_percent' => $overallMargin,
        ];
    }

    /**
     * Reconcile: cek deal tanpa project dan project tanpa deal.
     */
    public function reconcileDealsAndProjects(int $companyId): array
    {
        $dealsWithoutProjects = Deal::where('company_id', $companyId)
            ->whereIn('status', ['won', 'closed_won', 'fulfilling', 'completed'])
            ->whereNull('project_id')
            ->get()
            ->map(function ($deal) {
                return [
                    'deal_id' => $deal->id,
                    'title' => $deal->title,
                    'client' => $deal->client->name ?? '-',
                    'value' => (float) $deal->expected_value,
                    'status' => $deal->status,
                ];
            })
            ->values()
            ->toArray();

        $projectsWithoutDeals = Project::where('company_id', $companyId)
            ->whereNull('deal_id')
            ->whereNotNull('client_id')
            ->get()
            ->map(function ($project) {
                return [
                    'project_id' => $project->id,
                    'code' => $project->code,
                    'name' => $project->name,
                    'client' => $project->client->name ?? '-',
                    'budget' => (float) $project->budget,
                    'status' => $project->status,
                ];
            })
            ->values()
            ->toArray();

        return [
            'company_id' => $companyId,
            'deals_without_projects' => $dealsWithoutProjects,
            'deals_without_projects_count' => count($dealsWithoutProjects),
            'projects_without_deals' => $projectsWithoutDeals,
            'projects_without_deals_count' => count($projectsWithoutDeals),
        ];
    }

    protected function generateProjectCode(int $companyId): string
    {
        $year = date('Y');
        $prefix = 'PRJ-' . $year;

        $last = Project::where('company_id', $companyId)
            ->where('code', 'like', $prefix . '-%')
            ->orderBy('code', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->code, -4);
            $newNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        return $prefix . '-' . $newNum;
    }
}
