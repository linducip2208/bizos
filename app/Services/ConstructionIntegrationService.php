<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\DailySiteReport;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\ProgressBilling;
use App\Models\Project;
use App\Models\ProjectSiteInventory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\RabItem;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\SubcontractorContract;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConstructionIntegrationService
{
    /**
     * Sinkronisasi item RAB ke budget proyek.
     * Setiap item RAB menjadi BudgetItem di bawah budget proyek.
     */
    public function syncRabToProjectBudget(RabItem $rabItem, Project $project): BudgetItem
    {
        $budget = Budget::firstOrCreate(
            [
                'company_id' => $project->company_id,
                'project_id' => $project->id,
                'fiscal_year' => now()->year,
            ],
            [
                'name' => 'Anggaran Proyek ' . $project->name,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'status' => 'draft',
            ]
        );

        $budgetItem = BudgetItem::create([
            'budget_id' => $budget->id,
            'description' => $rabItem->description,
            'planned_amount' => $rabItem->total_amount,
            'actual_amount' => 0,
            'variance' => $rabItem->total_amount,
            'period_start' => $project->start_date,
            'period_end' => $project->end_date,
        ]);

        $project->update([
            'budget' => $project->budget + $rabItem->total_amount,
        ]);

        return $budgetItem;
    }

    /**
     * Sinkronisasi SEMUA item RAB milik proyek ke budget.
     */
    public function syncAllRabToProjectBudget(Project $project): void
    {
        $rabItems = RabItem::where('project_id', $project->id)
            ->whereNull('parent_id')
            ->get();

        foreach ($rabItems as $item) {
            if ($item->children()->exists()) {
                foreach ($item->children as $child) {
                    $this->syncRabToProjectBudget($child, $project);
                }
            } else {
                $this->syncRabToProjectBudget($item, $project);
            }
        }
    }

    /**
     * Bandingkan biaya aktual vs estimasi RAB.
     */
    public function compareActualVsRab(Project $project): array
    {
        $budget = Budget::where('project_id', $project->id)->first();

        if (!$budget) {
            return ['error' => 'Budget proyek belum dibuat'];
        }

        $comparison = [];
        $totalPlanned = 0;
        $totalActual = 0;

        foreach ($budget->budgetItems as $item) {
            $comparison[] = [
                'deskripsi' => $item->description,
                'anggaran' => $item->planned_amount,
                'aktual' => $item->actual_amount,
                'selisih' => $item->planned_amount - $item->actual_amount,
                'persentase' => $item->planned_amount > 0
                    ? round(($item->actual_amount / $item->planned_amount) * 100, 2)
                    : 0,
            ];
            $totalPlanned += $item->planned_amount;
            $totalActual += $item->actual_amount;
        }

        return [
            'total_anggaran' => $totalPlanned,
            'total_aktual' => $totalActual,
            'selisih_total' => $totalPlanned - $totalActual,
            'detail' => $comparison,
        ];
    }

    /**
     * Progress billing disetujui → auto-buat invoice.
     * Amount = net_amount (setelah retensi).
     */
    public function createInvoiceFromProgress(ProgressBilling $billing): Invoice
    {
        if ($billing->invoice_id) {
            return $billing->invoice;
        }

        return DB::transaction(function () use ($billing) {
            $project = $billing->project;

            $invoice = Invoice::create([
                'company_id' => $billing->company_id,
                'invoice_number' => 'INV-CON-' . date('Ym') . '-' . str_pad($billing->id, 6, '0', STR_PAD_LEFT),
                'invoice_type' => 'construction_progress',
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'reference_entity' => ProgressBilling::class,
                'reference_id' => $billing->id,
                'subtotal' => $billing->net_amount,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $billing->net_amount,
                'paid_amount' => 0,
                'remaining_amount' => $billing->net_amount,
                'status' => 'unpaid',
                'notes' => 'Tagihan progres #' . $billing->billing_number
                    . ' | Progres fisik: ' . $billing->physical_progress_percent . '%'
                    . ' | Retensi: ' . ($billing->retention_amount ?? 0),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Tagihan progres konstruksi - Periode '
                    . $billing->billing_period_start?->format('d/m/Y')
                    . ' s/d ' . $billing->billing_period_end?->format('d/m/Y'),
                'quantity' => 1,
                'unit_price' => $billing->net_amount,
                'tax_rate' => 0,
                'amount' => $billing->net_amount,
            ]);

            $billing->update(['invoice_id' => $invoice->id]);

            // Update actual_cost proyek
            $project->increment('actual_cost', $billing->net_amount);

            // Update BudgetItem actual_amount jika budget terhubung
            $budget = Budget::where('project_id', $project->id)->first();
            if ($budget) {
                $items = $budget->budgetItems;
                if ($items->isNotEmpty()) {
                    $perItem = $billing->net_amount / $items->count();
                    foreach ($items as $item) {
                        $item->actual_amount += $perItem;
                        $item->variance = $item->planned_amount - $item->actual_amount;
                        $item->save();
                    }
                }
            }

            return $invoice;
        });
    }

    /**
     * Pencatatan pemakaian material di site → pengurangan inventory.
     */
    public function recordMaterialUsage(ProjectSiteInventory $inventory, float $qty): void
    {
        DB::transaction(function () use ($inventory, $qty) {
            $inventory->quantity_on_site -= $qty;
            $inventory->quantity_used += $qty;
            $inventory->save();

            StockMovement::create([
                'company_id' => $inventory->company_id,
                'product_id' => $inventory->product_id,
                'warehouse_id' => $inventory->warehouse_id,
                'movement_type' => 'out',
                'reference_type' => ProjectSiteInventory::class,
                'reference_id' => $inventory->id,
                'quantity_in' => 0,
                'quantity_out' => $qty,
                'unit_cost' => $inventory->product->last_cost ?? 0,
                'running_quantity' => $inventory->quantity_on_site,
                'notes' => 'Pemakaian material proyek: ' . $inventory->project?->name,
                'movement_date' => now(),
            ]);

            $stockBalance = StockBalance::firstOrNew([
                'company_id' => $inventory->company_id,
                'product_id' => $inventory->product_id,
                'warehouse_id' => $inventory->warehouse_id,
            ]);
            $stockBalance->quantity = max(0, ($stockBalance->quantity ?? 0) - $qty);
            $stockBalance->save();
        });
    }

    /**
     * Laporan harian → auto-record absensi pekerja.
     * Worker list di daily report disinkronisasi ke attendance.
     */
    public function syncWorkerAttendance(DailySiteReport $report): array
    {
        $createdAttendances = [];
        $reportDate = Carbon::parse($report->report_date);

        if (!$report->worker_count || $report->worker_count <= 0) {
            return $createdAttendances;
        }

        $project = $report->project;
        $members = $project->projectMembers()->with('employee')->get();

        if ($members->isEmpty()) {
            return $createdAttendances;
        }

        foreach ($members as $member) {
            if (!$member->employee_id) {
                continue;
            }

            $exists = Attendance::where('employee_id', $member->employee_id)
                ->whereDate('date', $reportDate)
                ->exists();

            if ($exists) {
                continue;
            }

            $attendance = Attendance::create([
                'employee_id' => $member->employee_id,
                'date' => $reportDate,
                'status' => 'present',
                'work_type' => 'wfo',
                'notes' => 'Auto-sync dari laporan harian proyek: ' . $project->name
                    . ' (#' . $report->id . ')',
            ]);

            $createdAttendances[] = $attendance;
        }

        return $createdAttendances;
    }

    /**
     * Kontrak subkontraktor → Purchase Order ke Supplier.
     */
    public function createPoFromSubcontract(SubcontractorContract $contract): PurchaseOrder
    {
        $supplier = $contract->supplier;

        return DB::transaction(function () use ($contract, $supplier) {
            $po = PurchaseOrder::create([
                'company_id' => $contract->company_id,
                'po_number' => 'PO-SUB-' . date('Ym') . '-' . str_pad($contract->id, 5, '0', STR_PAD_LEFT),
                'supplier_id' => $supplier->id,
                'order_date' => now(),
                'expected_date' => $contract->end_date ?? now()->addMonths(3),
                'subtotal' => $contract->contract_amount,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'shipping_cost' => 0,
                'total' => $contract->contract_amount,
                'notes' => 'PO dari kontrak subkontraktor #' . $contract->contract_number
                    . ' - ' . $contract->scope_of_work,
                'status' => 'draft',
            ]);

            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'description' => $contract->scope_of_work,
                'quantity' => 1,
                'unit_price' => $contract->contract_amount,
                'tax_rate' => 0,
                'amount' => $contract->contract_amount,
            ]);

            return $po;
        });
    }
}
