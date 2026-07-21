<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProgressBilling;
use App\Models\ProjectSiteInventory;
use App\Models\RabItem;
use App\Models\DailySiteReport;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ConstructionService
{
    public function calculateRabVsActual(Project $project): array
    {
        $rabItems = RabItem::where('project_id', $project->id)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        $summary = [
            'material' => ['budget' => 0, 'actual' => 0],
            'labor' => ['budget' => 0, 'actual' => 0],
            'equipment' => ['budget' => 0, 'actual' => 0],
            'subcontract' => ['budget' => 0, 'actual' => 0],
            'overhead' => ['budget' => 0, 'actual' => 0],
        ];

        foreach ($rabItems as $item) {
            $summary[$item->category]['budget'] += (float) $item->total_amount;
        }

        $summary['total_budget'] = $project->budget ?? array_sum(array_column($summary, 'budget'));
        $summary['total_actual'] = (float) ($project->actual_cost ?? 0);
        $summary['variance'] = $summary['total_budget'] - $summary['total_actual'];
        $summary['variance_percent'] = $summary['total_budget'] > 0
            ? round(($summary['variance'] / $summary['total_budget']) * 100, 2)
            : 0;

        return $summary;
    }

    public function generateProgressBilling(Project $project, float $progressPercent): ProgressBilling
    {
        $latestBilling = ProgressBilling::where('project_id', $project->id)
            ->orderByDesc('billing_period_end')
            ->first();

        $previousClaimed = $latestBilling?->current_claimed_percent ?? 0;
        $currentClaimed = $progressPercent - $previousClaimed;

        $grossAmount = $project->budget > 0
            ? ($project->budget * $currentClaimed) / 100
            : 0;

        $retentionPercent = 5.0;
        $retentionAmount = $grossAmount * ($retentionPercent / 100);
        $netAmount = $grossAmount - $retentionAmount;

        $billing = ProgressBilling::create([
            'company_id' => $project->company_id,
            'project_id' => $project->id,
            'billing_number' => 'PB-' . date('Ymd') . '-' . str_pad(
                ProgressBilling::where('project_id', $project->id)->count() + 1,
                3, '0', STR_PAD_LEFT
            ),
            'billing_period_start' => now()->startOfMonth(),
            'billing_period_end' => now()->endOfMonth(),
            'physical_progress_percent' => $progressPercent,
            'previous_claimed_percent' => $previousClaimed,
            'current_claimed_percent' => $currentClaimed,
            'gross_amount' => $grossAmount,
            'retention_percent' => $retentionPercent,
            'retention_amount' => $retentionAmount,
            'net_amount' => $netAmount,
            'status' => 'draft',
        ]);

        $project->update(['progress_percent' => $progressPercent]);

        return $billing;
    }

    public function releaseRetention(ProgressBilling $billing): void
    {
        if (empty($billing->invoice_id)) {
            $invoice = Invoice::create([
                'company_id' => $billing->company_id,
                'invoice_number' => 'INV-RET-' . $billing->billing_number,
                'invoice_type' => 'retention_release',
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'subtotal' => $billing->retention_amount,
                'tax_amount' => 0,
                'total' => $billing->retention_amount,
                'paid_amount' => 0,
                'remaining_amount' => $billing->retention_amount,
                'status' => 'unpaid',
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Pelepasan Retensi - ' . $billing->billing_number,
                'quantity' => 1,
                'unit_price' => $billing->retention_amount,
                'tax_rate' => 0,
                'amount' => $billing->retention_amount,
            ]);

            $billing->update(['invoice_id' => $invoice->id]);
        }
    }

    public function moveToProjectSite(int $productId, int $projectId, float $qty): void
    {
        $inventory = ProjectSiteInventory::firstOrCreate(
            [
                'company_id' => auth()->user()->company_id,
                'project_id' => $projectId,
                'product_id' => $productId,
            ],
            [
                'quantity_on_site' => 0,
                'quantity_used' => 0,
                'last_delivery_date' => now(),
            ]
        );

        $inventory->increment('quantity_on_site', $qty);
        $inventory->update(['last_delivery_date' => now()]);
    }

    public function returnFromProjectSite(int $productId, int $projectId, float $qty): void
    {
        $inventory = ProjectSiteInventory::where('project_id', $projectId)
            ->where('product_id', $productId)
            ->first();

        if ($inventory) {
            $remaining = $inventory->quantity_on_site - $inventory->quantity_used;
            if ($remaining >= $qty) {
                $inventory->decrement('quantity_on_site', $qty);
            }
        }
    }

    public function generateWeeklyReport(Project $project): array
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $reports = DailySiteReport::where('project_id', $project->id)
            ->whereBetween('report_date', [$startOfWeek, $endOfWeek])
            ->orderBy('report_date')
            ->get();

        $totalWorkers = $reports->sum('worker_count');
        $totalDays = $reports->count();

        $weatherDays = $reports->groupBy('weather')
            ->map(fn ($items) => $items->count())
            ->toArray();

        $allEquipment = [];
        $allMaterials = [];
        $allIssues = [];

        foreach ($reports as $report) {
            if ($report->heavy_equipment_used) {
                $allEquipment = array_merge($allEquipment, $report->heavy_equipment_used);
            }
            if ($report->materials_used) {
                $allMaterials = array_merge($allMaterials, $report->materials_used);
            }
            if ($report->issues) {
                $allIssues[] = [
                    'date' => $report->report_date->format('d M Y'),
                    'issue' => $report->issues,
                ];
            }
        }

        return [
            'period' => $startOfWeek->format('d M Y') . ' - ' . $endOfWeek->format('d M Y'),
            'total_reports' => $totalDays,
            'total_worker_days' => $totalWorkers,
            'avg_workers_per_day' => $totalDays > 0 ? round($totalWorkers / $totalDays, 1) : 0,
            'weather_breakdown' => $weatherDays,
            'equipment_used' => $allEquipment,
            'materials_used' => $allMaterials,
            'issues' => $allIssues,
        ];
    }
}
