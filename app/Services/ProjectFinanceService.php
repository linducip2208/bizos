<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\TimesheetEntry;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProjectFinanceService
{
    public function createInvoiceFromTimesheet(
        Project $project,
        string $periodStart,
        string $periodEnd
    ): Invoice {
        $periodStart = Carbon::parse($periodStart)->startOfDay();
        $periodEnd = Carbon::parse($periodEnd)->endOfDay();

        $entries = TimesheetEntry::whereHas('timesheet', function ($q) use ($project, $periodStart, $periodEnd) {
            $q->whereHas('employee', function ($eq) use ($project) {
                $eq->whereHas('taskAssignees', function ($tq) use ($project) {
                    $tq->whereHas('task', function ($taskQ) use ($project) {
                        $taskQ->where('project_id', $project->id);
                    });
                });
            })
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->where('status', 'approved');
        })
        ->where('is_billable', true)
        ->where('is_billed', false)
        ->with(['task', 'timesheet.employee'])
        ->get();

        if ($entries->isEmpty()) {
            throw new \RuntimeException('Tidak ada entri timesheet yang bisa ditagihkan.');
        }

        $client = $project->client;
        $company = $project->company;

        $groupedByTask = $entries->groupBy('task_id');

        $subtotal = 0;
        $lineItems = [];

        foreach ($groupedByTask as $taskId => $taskEntries) {
            $task = $taskEntries->first()->task;
            $totalHours = $taskEntries->sum('hours');
            $hourlyRate = (float) ($project->budget ?? 0) > 0
                ? $this->getImpliedHourlyRate($project, $taskEntries)
                : 100000;
            $lineAmount = round($totalHours * $hourlyRate, 2);
            $subtotal += $lineAmount;

            $employeeNames = $taskEntries->map(function ($e) {
                return $e->timesheet->employee->first_name ?? 'N/A';
            })->unique()->implode(', ');

            $lineItems[] = [
                'description' => ($task->title ?? 'Tugas #' . $taskId)
                    . ' — ' . $totalHours . ' jam'
                    . ' (' . $employeeNames . ')',
                'quantity' => $totalHours,
                'unit_price' => $hourlyRate,
                'amount' => $lineAmount,
                'entries' => $taskEntries,
            ];
        }

        $taxRate = 0.11;
        $taxAmount = round($subtotal * $taxRate, 2);
        $total = round($subtotal + $taxAmount, 2);

        $invoice = DB::transaction(function () use (
            $project, $client, $company, $subtotal, $taxAmount, $total,
            $lineItems, $entries, $periodStart, $periodEnd
        ) {
            $invoiceNumber = $this->generateInvoiceNumber($company, $project);

            $invoice = Invoice::create([
                'company_id' => $company->id,
                'invoice_number' => $invoiceNumber,
                'invoice_type' => 'project_billing',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'reference_entity' => Project::class,
                'reference_id' => $project->id,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'paid_amount' => 0,
                'remaining_amount' => $total,
                'status' => 'draft',
                'notes' => 'Tagihan project: ' . $project->name
                    . ' — Periode: ' . $periodStart->format('d M Y')
                    . ' s/d ' . $periodEnd->format('d M Y'),
            ]);

            foreach ($lineItems as $line) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $line['description'],
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'tax_rate' => $taxRate,
                    'amount' => $line['amount'],
                ]);

                $entryIds = $line['entries']->pluck('id');
                TimesheetEntry::whereIn('id', $entryIds)->update([
                    'invoice_id' => $invoice->id,
                    'is_billed' => true,
                ]);
            }

            return $invoice;
        });

        return $invoice;
    }

    public function getProjectProfitability(Project $project): array
    {
        $budget = (float) ($project->budget ?? 0);

        $billableEntries = TimesheetEntry::where('is_billable', true)
            ->where('is_billed', true)
            ->whereHas('timesheet.employee.taskAssignees.task', function ($q) use ($project) {
                $q->where('project_id', $project->id);
            })
            ->sum('hours');

        $hourlyRate = $this->getImpliedHourlyRate($project);
        $actualCost = round($billableEntries * $hourlyRate, 2);
        $actualCostFromProject = (float) ($project->actual_cost ?? 0);

        $revenueInvoiced = InvoiceItem::whereHas('invoice', function ($q) use ($project) {
            $q->where('reference_entity', Project::class)
                ->where('reference_id', $project->id);
        })->sum('amount');

        $margin = round($revenueInvoiced - $actualCost, 2);
        $marginPercent = $revenueInvoiced > 0
            ? round(($margin / $revenueInvoiced) * 100, 2)
            : 0;

        return [
            'project_name' => $project->name,
            'budget' => $budget,
            'actual_cost_timesheet' => $actualCost,
            'actual_cost_from_project' => $actualCostFromProject,
            'revenue_invoiced' => $revenueInvoiced,
            'margin' => $margin,
            'margin_percent' => $marginPercent,
            'is_profitable' => $margin > 0,
            'billable_hours' => $billableEntries,
        ];
    }

    public function invoiceMilestone(Milestone $milestone): Invoice
    {
        if ($milestone->status !== 'completed') {
            throw new \RuntimeException('Hanya milestone yang sudah selesai dapat ditagihkan.');
        }

        if ($milestone->invoice_id) {
            throw new \RuntimeException('Milestone ini sudah memiliki invoice.');
        }

        $project = $milestone->project;
        $company = $project->company;

        $milestoneAmount = $project->budget > 0
            ? round((float) $project->budget / max($project->milestones()->count(), 1), 2)
            : 0;

        $taxRate = 0.11;
        $taxAmount = round($milestoneAmount * $taxRate, 2);
        $total = round($milestoneAmount + $taxAmount, 2);

        $invoice = DB::transaction(function () use (
            $company, $project, $milestone, $milestoneAmount, $taxRate, $taxAmount, $total
        ) {
            $invoiceNumber = $this->generateInvoiceNumber($company, $project);

            $invoice = Invoice::create([
                'company_id' => $company->id,
                'invoice_number' => $invoiceNumber,
                'invoice_type' => 'milestone_billing',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'reference_entity' => Milestone::class,
                'reference_id' => $milestone->id,
                'subtotal' => $milestoneAmount,
                'discount_amount' => 0,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'paid_amount' => 0,
                'remaining_amount' => $total,
                'status' => 'draft',
                'notes' => 'Tagihan milestone: ' . $milestone->name
                    . ' — Project: ' . $project->name,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Milestone: ' . $milestone->name
                    . ' (Project: ' . $project->name . ')',
                'quantity' => 1,
                'unit_price' => $milestoneAmount,
                'tax_rate' => $taxRate,
                'amount' => $milestoneAmount,
            ]);

            $milestone->update(['invoice_id' => $invoice->id]);

            return $invoice;
        });

        return $invoice;
    }

    public function getUnbilledEntries(int $projectId): Collection
    {
        return TimesheetEntry::where('is_billable', true)
            ->where('is_billed', false)
            ->whereHas('timesheet.employee.taskAssignees.task', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })
            ->with(['task', 'timesheet.employee'])
            ->get();
    }

    public function getUnbilledHoursByProject(int $projectId): float
    {
        return (float) TimesheetEntry::where('is_billable', true)
            ->where('is_billed', false)
            ->whereHas('timesheet.employee.taskAssignees.task', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })
            ->sum('hours');
    }

    protected function generateInvoiceNumber(Company $company, Project $project): string
    {
        $prefix = 'INV-PROJ-' . strtoupper(substr($project->code ?? 'XX', 0, 6));
        $date = now()->format('Ymd');
        $count = Invoice::where('company_id', $company->id)
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return $prefix . '-' . $date . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    protected function getImpliedHourlyRate(Project $project, ?Collection $entries = null): float
    {
        if ($entries && $entries->isNotEmpty()) {
            $employee = $entries->first()->timesheet->employee ?? null;
            if ($employee && (float) ($employee->hourly_rate ?? 0) > 0) {
                return (float) $employee->hourly_rate;
            }
        }

        $budget = (float) ($project->budget ?? 0);
        if ($budget <= 0) {
            return 100000;
        }

        $totalEstimateHours = $project->tasks()->sum('estimated_hours');

        if ($totalEstimateHours > 0) {
            return round($budget / $totalEstimateHours, 2);
        }

        return 100000;
    }
}
