<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Lead;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LeadAssignmentService
{
    public function assignRoundRobin(Lead $lead, array $salespersonIds = []): ?Employee
    {
        if (empty($salespersonIds)) {
            $salespersonIds = Employee::where('company_id', $lead->company_id)
                ->where('status', 'active')
                ->whereHas('position', function ($q) {
                    $q->where('name', 'like', '%sales%')->orWhere('name', 'like', '%salesperson%');
                })
                ->pluck('id')
                ->toArray();
        }

        $cacheKey = 'lead_assignment_round_robin_' . $lead->company_id;
        $lastAssignedId = Cache::get($cacheKey);

        $availableIds = $this->filterAvailableSalespersons($salespersonIds);
        if (empty($availableIds)) {
            Log::warning('LeadAssignment: No active salespersons available', ['lead_id' => $lead->id]);
            return null;
        }

        $nextSalesperson = $this->findNextInQueue($availableIds, $lastAssignedId);

        if ($nextSalesperson) {
            $lead->update(['assigned_to' => $nextSalesperson->id]);
            Cache::put($cacheKey, $nextSalesperson->id, now()->addDays(30));
            Cache::put("lead_assignment_last_{$lead->id}", now()->toIso8601String(), now()->addDays(30));

            Log::info('LeadAssignment: Assigned round-robin', [
                'lead_id' => $lead->id,
                'employee_id' => $nextSalesperson->id,
            ]);

            return $nextSalesperson;
        }

        return null;
    }

    public function assignWeighted(Lead $lead, array $salespersonIds = []): ?Employee
    {
        if (empty($salespersonIds)) {
            $salespersonIds = Employee::where('company_id', $lead->company_id)
                ->where('status', 'active')
                ->pluck('id')
                ->toArray();
        }

        $availableIds = $this->filterAvailableSalespersons($salespersonIds);
        if (empty($availableIds)) return null;

        $employees = Employee::whereIn('id', $availableIds)->get();

        $weighted = [];
        foreach ($employees as $employee) {
            $leadCount = Lead::where('assigned_to', $employee->id)
                ->where('status', '!=', 'converted')
                ->count();
            $weight = $this->calculateWeight($employee);
            $score = $weight / max($leadCount, 1);
            $weighted[$employee->id] = $score;
        }

        arsort($weighted);
        $selectedId = array_key_first($weighted);
        $selected = $employees->firstWhere('id', $selectedId);

        if ($selected) {
            $lead->update(['assigned_to' => $selected->id]);
            Cache::put("lead_assignment_last_{$lead->id}", now()->toIso8601String(), now()->addDays(30));
            return $selected;
        }

        return null;
    }

    public function autoReassignStale(): int
    {
        $reassigned = 0;
        $staleLeads = Lead::where('status', '!=', 'converted')
            ->whereNotNull('assigned_to')
            ->where(function ($q) {
                $q->whereNull('next_follow_up')
                    ->orWhere('next_follow_up', '<', now()->subHours(24));
            })
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        foreach ($staleLeads as $lead) {
            $availableIds = Employee::where('company_id', $lead->company_id)
                ->where('status', 'active')
                ->where('id', '!=', $lead->assigned_to)
                ->pluck('id')
                ->toArray();

            $availableIds = $this->filterAvailableSalespersons($availableIds);

            if (!empty($availableIds)) {
                $nextId = $availableIds[array_rand($availableIds)];
                $oldAssignee = $lead->assigned_to;
                $lead->update(['assigned_to' => $nextId]);

                Log::info('LeadAssignment: Reassigned stale lead', [
                    'lead_id' => $lead->id,
                    'old_assignee' => $oldAssignee,
                    'new_assignee' => $nextId,
                ]);

                $reassigned++;
            }
        }

        return $reassigned;
    }

    protected function filterAvailableSalespersons(array $ids): array
    {
        if (empty($ids)) return [];

        return Employee::whereIn('id', $ids)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereDoesntHave('leaves', function ($sq) {
                    $sq->where('status', 'approved')
                        ->where('start_date', '<=', now()->toDateString())
                        ->where('end_date', '>=', now()->toDateString());
                });
            })
            ->pluck('id')
            ->toArray();
    }

    protected function findNextInQueue(array $availableIds, ?int $lastAssignedId): ?Employee
    {
        $employees = Employee::whereIn('id', $availableIds)
            ->orderBy('id')
            ->get();

        if ($employees->isEmpty()) return null;

        if ($lastAssignedId) {
            $currentIndex = $employees->search(function ($e) use ($lastAssignedId) {
                return $e->id === $lastAssignedId;
            });

            if ($currentIndex !== false) {
                $nextIndex = ($currentIndex + 1) % $employees->count();
                return $employees->values()->get($nextIndex);
            }
        }

        return $employees->first();
    }

    protected function calculateWeight(Employee $employee): float
    {
        $baseWeight = 1.0;
        $gradeWeight = match ($employee->grade?->name ?? '') {
            'Senior' => 2.0,
            'Manager' => 2.5,
            'Lead' => 1.5,
            default => 1.0,
        };

        return $baseWeight * $gradeWeight;
    }
}
