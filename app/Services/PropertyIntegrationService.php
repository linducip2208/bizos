<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Coa;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\MaintenanceRequest;
use App\Models\ServiceChargeInvoice;
use App\Models\TenancyContract;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PropertyIntegrationService
{
    /**
     * Tagihan service charge → Invoice Finance (AR).
     * Membuat Journal entry: Dr. Piutang Sewa / AR Rent, Cr. Pendapatan Sewa.
     */
    public function postToFinance(ServiceChargeInvoice $serviceInvoice): Invoice
    {
        if ($serviceInvoice->finance_invoice_id) {
            return Invoice::find($serviceInvoice->finance_invoice_id);
        }

        return DB::transaction(function () use ($serviceInvoice) {
            $contract = $serviceInvoice->tenancyContract;
            $unit = $serviceInvoice->propertyUnit;

            $description = 'Tagihan sewa unit ' . ($unit->unit_number ?? $unit->id)
                . ' periode ' . $serviceInvoice->period_start?->format('d/m/Y')
                . ' - ' . $serviceInvoice->period_end?->format('d/m/Y');

            $invoice = Invoice::create([
                'company_id' => $serviceInvoice->company_id,
                'invoice_number' => 'INV-PROP-' . date('Ym') . '-' . str_pad($serviceInvoice->id, 6, '0', STR_PAD_LEFT),
                'invoice_type' => 'property_rent',
                'invoice_date' => now(),
                'due_date' => $serviceInvoice->due_date ?? now()->addDays(7),
                'reference_entity' => ServiceChargeInvoice::class,
                'reference_id' => $serviceInvoice->id,
                'subtotal' => $serviceInvoice->rent_amount + $serviceInvoice->service_charge
                    + $serviceInvoice->sinking_fund,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $serviceInvoice->total_amount,
                'paid_amount' => 0,
                'remaining_amount' => $serviceInvoice->total_amount,
                'status' => 'unpaid',
                'notes' => $description,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Biaya Sewa',
                'quantity' => 1,
                'unit_price' => $serviceInvoice->rent_amount,
                'tax_rate' => 0,
                'amount' => $serviceInvoice->rent_amount,
            ]);

            if ($serviceInvoice->service_charge > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Service Charge / IPL',
                    'quantity' => 1,
                    'unit_price' => $serviceInvoice->service_charge,
                    'tax_rate' => 0,
                    'amount' => $serviceInvoice->service_charge,
                ]);
            }

            if ($serviceInvoice->sinking_fund > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Sinking Fund',
                    'quantity' => 1,
                    'unit_price' => $serviceInvoice->sinking_fund,
                    'tax_rate' => 0,
                    'amount' => $serviceInvoice->sinking_fund,
                ]);
            }

            if ($serviceInvoice->electricity > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Biaya Listrik',
                    'quantity' => 1,
                    'unit_price' => $serviceInvoice->electricity,
                    'tax_rate' => 0,
                    'amount' => $serviceInvoice->electricity,
                ]);
            }

            if ($serviceInvoice->water > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Biaya Air',
                    'quantity' => 1,
                    'unit_price' => $serviceInvoice->water,
                    'tax_rate' => 0,
                    'amount' => $serviceInvoice->water,
                ]);
            }

            if ($serviceInvoice->other_charges > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'Biaya Lain-lain',
                    'quantity' => 1,
                    'unit_price' => $serviceInvoice->other_charges,
                    'tax_rate' => 0,
                    'amount' => $serviceInvoice->other_charges,
                ]);
            }

            // Buat journal entry: Dr. Piutang Sewa, Cr. Pendapatan Sewa
            $coaReceivable = Coa::where('code', 'like', '1-1-03%')
                ->where('company_id', $serviceInvoice->company_id)
                ->first();

            $coaRevenue = Coa::where('code', 'like', '4-1-01%')
                ->where('company_id', $serviceInvoice->company_id)
                ->first();

            if ($coaReceivable && $coaRevenue) {
                $journal = Journal::create([
                    'company_id' => $serviceInvoice->company_id,
                    'journal_number' => 'JRN-PROP-' . date('Ym') . '-' . str_pad($serviceInvoice->id, 6, '0', STR_PAD_LEFT),
                    'journal_date' => now(),
                    'journal_type' => 'revenue_recognition',
                    'description' => $description,
                    'total_debit' => $serviceInvoice->total_amount,
                    'total_credit' => $serviceInvoice->total_amount,
                    'reference_type' => ServiceChargeInvoice::class,
                    'reference_id' => $serviceInvoice->id,
                    'status' => 'posted',
                    'posted_at' => now(),
                ]);

                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $coaReceivable->id,
                    'description' => 'Piutang sewa - ' . $description,
                    'debit' => $serviceInvoice->total_amount,
                    'credit' => 0,
                ]);

                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $coaRevenue->id,
                    'description' => 'Pendapatan sewa - ' . $description,
                    'debit' => 0,
                    'credit' => $serviceInvoice->total_amount,
                ]);
            }

            $serviceInvoice->update(['finance_invoice_id' => $invoice->id]);

            return $invoice;
        });
    }

    /**
     * Generate tagihan sewa bulanan otomatis dari kontrak tenancy.
     */
    public function generateMonthlyRentInvoice(TenancyContract $contract): ServiceChargeInvoice
    {
        $now = now();
        $periodStart = $now->copy()->startOfMonth();
        $periodEnd = $now->copy()->endOfMonth();

        $exists = ServiceChargeInvoice::where('tenancy_contract_id', $contract->id)
            ->where('period_start', $periodStart->format('Y-m-d'))
            ->exists();

        if ($exists) {
            throw new \RuntimeException('Invoice untuk periode ini sudah ada.');
        }

        $dueDate = $now->copy()->setDay(min($contract->payment_due_day, $now->daysInMonth));

        $totalAmount = $contract->monthly_rent
            + $contract->service_charge_monthly
            + $contract->sinking_fund_monthly;

        $serviceInvoice = ServiceChargeInvoice::create([
            'company_id' => $contract->company_id,
            'property_unit_id' => $contract->property_unit_id,
            'tenancy_contract_id' => $contract->id,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'invoice_number' => 'SCI-' . date('Ym') . '-' . str_pad($contract->id, 5, '0', STR_PAD_LEFT),
            'rent_amount' => $contract->monthly_rent,
            'service_charge' => $contract->service_charge_monthly,
            'sinking_fund' => $contract->sinking_fund_monthly,
            'electricity' => 0,
            'water' => 0,
            'other_charges' => 0,
            'total_amount' => $totalAmount,
            'due_date' => $dueDate,
            'status' => 'unpaid',
        ]);

        return $serviceInvoice;
    }

    /**
     * Maintenance request → Work Order (Field Service).
     * Auto-assign ke teknisi yang tersedia.
     */
    public function createWorkOrder(MaintenanceRequest $request): WorkOrder
    {
        if ($request->work_order_id) {
            return WorkOrder::find($request->work_order_id);
        }

        $unit = $request->propertyUnit;

        $wo = WorkOrder::create([
            'company_id' => $request->company_id,
            'client_id' => $request->tenancy_contract?->client_id,
            'wo_number' => 'WO-PROP-' . date('ym') . '-' . str_pad($request->id, 5, '0', STR_PAD_LEFT),
            'service_type' => 'maintenance',
            'priority' => $request->priority ?? 'medium',
            'description' => 'Permintaan maintenance: ' . $request->description
                . ' | Unit: ' . ($unit->unit_number ?? $unit->id)
                . ' | Kategori: ' . $request->category,
            'reported_by' => $request->requested_by,
            'scheduled_start' => now(),
            'scheduled_end' => now()->addHours(4),
            'status' => 'pending',
            'notes' => 'Auto-generated dari Maintenance Request #' . $request->id,
        ]);

        $request->update([
            'work_order_id' => $wo->id,
            'status' => 'in_progress',
        ]);

        return $wo;
    }

    /**
     * Hitung denda keterlambatan pembayaran.
     */
    public function applyLateFee(ServiceChargeInvoice $invoice): void
    {
        if ($invoice->status !== 'unpaid') {
            return;
        }

        $dueDate = Carbon::parse($invoice->due_date);

        if (now()->lte($dueDate)) {
            return;
        }

        $contract = $invoice->tenancyContract;

        if (!$contract || !$contract->late_fee_percent || $contract->late_fee_percent <= 0) {
            return;
        }

        $daysLate = $dueDate->diffInDays(now());
        $lateFee = $invoice->total_amount * ($contract->late_fee_percent / 100) * $daysLate;

        $invoice->update([
            'other_charges' => $invoice->other_charges + $lateFee,
            'total_amount' => $invoice->total_amount + $lateFee,
        ]);

        // Update finance invoice juga jika sudah di-post
        if ($invoice->finance_invoice_id) {
            $financeInvoice = Invoice::find($invoice->finance_invoice_id);
            if ($financeInvoice && $financeInvoice->status === 'unpaid') {
                InvoiceItem::create([
                    'invoice_id' => $financeInvoice->id,
                    'description' => 'Denda keterlambatan ' . $daysLate . ' hari ('
                        . $contract->late_fee_percent . '%/hari)',
                    'quantity' => 1,
                    'unit_price' => $lateFee,
                    'tax_rate' => 0,
                    'amount' => $lateFee,
                ]);

                $financeInvoice->total += $lateFee;
                $financeInvoice->remaining_amount += $lateFee;
                $financeInvoice->save();
            }
        }
    }

    /**
     * Sinkronisasi Tenant → Client link.
     * Hubungkan data penyewa (TenancyContract) ke model Client CRM.
     */
    public function syncTenantToClient(TenancyContract $contract): Client
    {
        if ($contract->client_id) {
            return $contract->client;
        }

        $unit = $contract->propertyUnit;

        $client = Client::firstOrCreate(
            [
                'company_id' => $contract->company_id,
                'name' => 'Penyewa Unit ' . ($unit->unit_number ?? $unit->id),
            ],
            [
                'source' => 'property_tenancy',
                'notes' => 'Penyewa properti - Kontrak #' . $contract->contract_number
                    . ' | Periode: ' . $contract->start_date?->format('d/m/Y')
                    . ' - ' . $contract->end_date?->format('d/m/Y'),
            ]
        );

        $contract->update(['client_id' => $client->id]);

        return $client;
    }
}
