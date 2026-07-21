<?php

namespace App\Services;

use App\Models\ContractedEquipment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\ServiceContract;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\VanInventory;
use App\Models\WorkOrder;
use App\Models\WorkOrderPart;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FieldServiceIntegrationService
{
    /**
     * Work order selesai → auto-invoice (jika billable).
     */
    public function createInvoiceFromWorkOrder(WorkOrder $wo): ?Invoice
    {
        if ($wo->invoice_id) {
            return $wo->invoice;
        }

        $totalCost = $wo->total_cost;

        if (!$totalCost || $totalCost <= 0) {
            $totalCost = ($wo->service_charge ?? 0) + ($wo->parts_cost ?? 0);
        }

        if ($totalCost <= 0) {
            return null;
        }

        return DB::transaction(function () use ($wo, $totalCost) {
            $invoice = Invoice::create([
                'company_id' => $wo->company_id,
                'invoice_number' => 'INV-WO-' . date('Ym') . '-' . str_pad($wo->id, 6, '0', STR_PAD_LEFT),
                'invoice_type' => 'field_service',
                'invoice_date' => now(),
                'due_date' => now()->addDays(14),
                'reference_entity' => WorkOrder::class,
                'reference_id' => $wo->id,
                'subtotal' => $totalCost,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $totalCost,
                'paid_amount' => 0,
                'remaining_amount' => $totalCost,
                'status' => 'unpaid',
                'notes' => 'Tagihan work order #' . $wo->wo_number
                    . ' | Klien: ' . ($wo->client?->name ?? '-')
                    . ' | Teknisi: ' . ($wo->technician?->first_name ?? '-'),
            ]);

            if ($wo->service_charge > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Biaya jasa servis - WO #' . $wo->wo_number,
                    'quantity' => 1,
                    'unit_price' => $wo->service_charge,
                    'tax_rate' => 0,
                    'amount' => $wo->service_charge,
                ]);
            }

            if ($wo->parts_cost > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Biaya spare part - WO #' . $wo->wo_number,
                    'quantity' => 1,
                    'unit_price' => $wo->parts_cost,
                    'tax_rate' => 0,
                    'amount' => $wo->parts_cost,
                ]);
            }

            if ($wo->labor_hours > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Biaya tenaga kerja (' . $wo->labor_hours . ' jam)',
                    'quantity' => $wo->labor_hours,
                    'unit_price' => 0,
                    'tax_rate' => 0,
                    'amount' => 0,
                ]);
            }

            $wo->update(['invoice_id' => $invoice->id]);

            return $invoice;
        });
    }

    /**
     * Parts digunakan dari van → auto-update van inventory + catat StockMovement.
     */
    public function onPartsUsed(WorkOrderPart $part): void
    {
        if (!$part->from_van_stock) {
            return;
        }

        DB::transaction(function () use ($part) {
            $wo = $part->workOrder;
            $technicianId = $wo->technician_id;

            if (!$technicianId) {
                return;
            }

            $vanInventories = VanInventory::where('product_id', $part->product_id)
                ->whereHas('van', function ($q) use ($technicianId) {
                    $q->where('technician_id', $technicianId);
                })
                ->get();

            if ($vanInventories->isEmpty()) {
                return;
            }

            $remaining = $part->quantity;

            foreach ($vanInventories as $inventory) {
                if ($remaining <= 0) {
                    break;
                }

                $deduct = min($inventory->quantity, $remaining);
                $inventory->quantity -= $deduct;
                $inventory->save();

                StockMovement::create([
                    'company_id' => $wo->company_id,
                    'product_id' => $part->product_id,
                    'warehouse_id' => null,
                    'movement_type' => 'out',
                    'reference_type' => WorkOrderPart::class,
                    'reference_id' => $part->id,
                    'quantity_in' => 0,
                    'quantity_out' => $deduct,
                    'unit_cost' => $part->unit_price,
                    'running_quantity' => $inventory->quantity,
                    'notes' => 'Pemakaian spare part dari van - WO #' . $wo->wo_number,
                    'movement_date' => now(),
                ]);

                $remaining -= $deduct;
            }
        });
    }

    /**
     * Service contract → auto-generate Work Orders sesuai jadwal.
     * Dipanggil via scheduler, memproses semua kontrak aktif yang jadwalnya jatuh tempo.
     */
    public function generateScheduledWorkOrders(): array
    {
        $activeContracts = ServiceContract::where('status', 'active')
            ->with(['client', 'contractedEquipment'])
            ->get();

        $generated = [];
        $now = now();

        foreach ($activeContracts as $contract) {
            $frequency = $contract->service_frequency;

            // Hitung kapan service terakhir dilakukan
            $lastWo = WorkOrder::where('service_contract_id', $contract->id)
                ->where('status', 'completed')
                ->orderByDesc('actual_end')
                ->first();

            $nextDue = $this->calculateNextDueDate($lastWo?->actual_end, $frequency);

            if ($nextDue && $nextDue->format('Y-m-d') === $now->format('Y-m-d')) {
                $equipment = $contract->contractedEquipment;

                foreach ($equipment as $equip) {
                    $wo = WorkOrder::create([
                        'company_id' => $contract->company_id,
                        'service_contract_id' => $contract->id,
                        'client_id' => $contract->client_id,
                        'equipment_id' => $equip->id,
                        'wo_number' => 'WO-SCH-' . date('ym') . '-' . str_pad($contract->id, 4, '0', STR_PAD_LEFT)
                            . '-' . str_pad($equip->id, 3, '0', STR_PAD_LEFT),
                        'service_type' => 'scheduled_maintenance',
                        'priority' => 'medium',
                        'description' => 'Maintenance terjadwal - ' . $equip->equipment_name
                            . ' (' . ($equip->brand ?? '') . ' ' . ($equip->model ?? '') . ')'
                            . ' | Serial: ' . ($equip->serial_number ?? 'N/A')
                            . ' | Lokasi: ' . $equip->location,
                        'scheduled_start' => $now,
                        'scheduled_end' => $now->copy()->addHours(4),
                        'status' => 'pending',
                        'notes' => 'Auto-generated dari kontrak servis #' . $contract->contract_number,
                    ]);

                    $generated[] = $wo;
                }
            }
        }

        return $generated;
    }

    /**
     * Work order selesai → update next_service_date pada contracted equipment.
     */
    public function onWorkOrderComplete(WorkOrder $wo): void
    {
        if ($wo->equipment_id) {
            $equipment = $wo->equipment;
            $contract = $wo->serviceContract;

            if ($equipment && $contract && $contract->service_frequency) {
                $nextService = $this->calculateNextDueDate(
                    $wo->actual_end ?? now(),
                    $contract->service_frequency
                );

                $equipment->update([
                    'last_service_date' => $wo->actual_end ?? now(),
                    'next_service_date' => $nextService,
                ]);
            }
        }
    }

    /**
     * Van restock → Purchase Requisition.
     * Ketika stok van di bawah reorder point, auto-buat PR.
     */
    public function createPrForVanRestock(VanInventory $inventory): PurchaseRequisition
    {
        $van = $inventory->van;
        $product = $inventory->product;

        return DB::transaction(function () use ($inventory, $van, $product) {
            $pr = PurchaseRequisition::create([
                'company_id' => $van->vehicle?->company_id ?? $inventory->product?->company_id,
                'pr_number' => 'PR-VAN-' . date('ym') . '-' . str_pad($inventory->id, 5, '0', STR_PAD_LEFT),
                'date_required' => now()->addDays(3),
                'notes' => 'Restok van teknisi ' . ($van->technician?->first_name ?? 'N/A')
                    . ' (Van #' . $van->id . ')'
                    . ' - Stok saat ini: ' . $inventory->quantity
                    . ', Min: ' . $inventory->min_quantity
                    . ', Reorder: ' . $inventory->reorder_point,
                'status' => 'draft',
            ]);

            $restockQty = $inventory->reorder_point && $inventory->reorder_point > 0
                ? $inventory->reorder_point
                : $inventory->min_quantity * 2;

            PurchaseRequisitionItem::create([
                'purchase_requisition_id' => $pr->id,
                'product_id' => $inventory->product_id,
                'quantity' => max(1, $restockQty),
                'unit_price' => $product->last_cost ?? 0,
                'notes' => 'Auto-restock dari van #' . $van->id,
            ]);

            return $pr;
        });
    }

    /**
     * Hitung tanggal service berikutnya berdasarkan frekuensi.
     */
    private function calculateNextDueDate(?Carbon $lastDate, ?string $frequency): ?Carbon
    {
        $base = $lastDate ?? now();

        return match ($frequency) {
            'daily' => $base->copy()->addDay(),
            'weekly' => $base->copy()->addWeek(),
            'biweekly' => $base->copy()->addWeeks(2),
            'monthly' => $base->copy()->addMonth(),
            'quarterly' => $base->copy()->addMonths(3),
            'semesterly' => $base->copy()->addMonths(6),
            'yearly' => $base->copy()->addYear(),
            default => null,
        };
    }
}
