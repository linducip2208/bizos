<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\Deal;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\PayrollPeriod;
use App\Models\PosTransaction;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialIntegrationService
{
    // ─── PAYROLL → FINANCE ───────────────────────────────────────

    public function postPayrollToJournal(PayrollPeriod $period): Journal
    {
        if ($period->status !== 'completed') {
            throw new \RuntimeException('Periode payroll harus berstatus "completed" untuk posting jurnal.');
        }

        if ($period->journal_id) {
            $existing = Journal::find($period->journal_id);
            if ($existing && $existing->status === 'posted') {
                throw new \RuntimeException('Jurnal sudah diposting untuk periode ini: ' . $existing->journal_number);
            }
        }

        $payrolls = $period->payrolls()->whereIn('status', ['finalized', 'paid'])->get();

        if ($payrolls->isEmpty()) {
            throw new \RuntimeException('Tidak ada data payroll yang finalized/paid untuk diposting.');
        }

        $totalGross = $payrolls->sum('gross_salary');
        $totalIncomeComponents = $payrolls->sum('total_income_components');
        $totalDeductionComponents = $payrolls->sum('total_deduction_components');
        $totalPph21 = $payrolls->sum('pph21_amount');
        $totalBpjsTkJht = $payrolls->sum('bpjs_tk_jht');
        $totalBpjsTkJp = $payrolls->sum('bpjs_tk_jp');
        $totalBpjsTkJkk = $payrolls->sum('bpjs_tk_jkk');
        $totalBpjsTkJkm = $payrolls->sum('bpjs_tk_jkm');
        $totalBpjsTk = $totalBpjsTkJht + $totalBpjsTkJp + $totalBpjsTkJkk + $totalBpjsTkJkm;
        $totalBpjsKes = $payrolls->sum('bpjs_kes');
        $totalNet = $payrolls->sum('net_salary');

        $totalGrossFull = $totalGross + $totalIncomeComponents;

        $coaSalaryExpense = $this->findCoa($period->company_id, '5-1000');
        $coaPph21Payable = $this->findCoa($period->company_id, '2-2000');
        $coaBpjsTkPayable = $this->findCoa($period->company_id, '2-2001');
        $coaBpjsKesPayable = $this->findCoa($period->company_id, '2-2002');
        $coaBank = $this->findCoa($period->company_id, '1-1200');

        $journalNumber = $this->generateJournalNumber($period->company_id, 'PRL');

        return DB::transaction(function () use (
            $period, $journalNumber,
            $totalGrossFull, $totalPph21, $totalBpjsTk, $totalBpjsKes, $totalNet,
            $coaSalaryExpense, $coaPph21Payable, $coaBpjsTkPayable, $coaBpjsKesPayable, $coaBank
        ) {
            $journal = Journal::create([
                'company_id' => $period->company_id,
                'journal_number' => $journalNumber,
                'journal_date' => $period->payment_date ?? Carbon::now()->format('Y-m-d'),
                'journal_type' => 'general',
                'description' => "Payroll {$period->period_code}",
                'total_debit' => $totalGrossFull,
                'total_credit' => $totalGrossFull,
                'reference_type' => PayrollPeriod::class,
                'reference_id' => $period->id,
                'status' => 'posted',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            $entries = [];

            // Dr. Beban Gaji
            $entries[] = [
                'journal_id' => $journal->id,
                'coa_id' => $coaSalaryExpense->id,
                'description' => "Beban Gaji - {$period->period_code}",
                'debit' => $totalGrossFull,
                'credit' => 0,
            ];

            // Cr. Utang PPh21
            if ($totalPph21 > 0) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'coa_id' => $coaPph21Payable->id,
                    'description' => "Utang PPh21 - {$period->period_code}",
                    'debit' => 0,
                    'credit' => $totalPph21,
                ];
            }

            // Cr. Utang BPJS TK
            if ($totalBpjsTk > 0) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'coa_id' => $coaBpjsTkPayable->id,
                    'description' => "Utang BPJS TK - {$period->period_code}",
                    'debit' => 0,
                    'credit' => $totalBpjsTk,
                ];
            }

            // Cr. Utang BPJS Kesehatan
            if ($totalBpjsKes > 0) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'coa_id' => $coaBpjsKesPayable->id,
                    'description' => "Utang BPJS Kes - {$period->period_code}",
                    'debit' => 0,
                    'credit' => $totalBpjsKes,
                ];
            }

            // Cr. Bank/Kas
            if ($totalNet > 0) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'coa_id' => $coaBank->id,
                    'description' => "Pembayaran Gaji - {$period->period_code}",
                    'debit' => 0,
                    'credit' => $totalNet,
                ];
            }

            JournalEntry::insert($entries);

            $period->update(['journal_id' => $journal->id]);

            return $journal->fresh(['journalEntries.coa']);
        });
    }

    // ─── POS TRANSACTION → FINANCE ────────────────────────────────

    public function postPosTransactionToJournal(PosTransaction $transaction): Journal
    {
        if ($transaction->payment_status !== 'paid') {
            throw new \RuntimeException('Transaksi POS harus berstatus "paid" untuk posting jurnal.');
        }

        if ($transaction->journal_id) {
            $existing = Journal::find($transaction->journal_id);
            if ($existing && $existing->status === 'posted') {
                throw new \RuntimeException('Jurnal sudah diposting untuk transaksi ini: ' . $existing->journal_number);
            }
        }

        $coaCash = $this->findCoa($transaction->company_id, '1-1100');
        $coaSalesRevenue = $this->findCoa($transaction->company_id, '4-1000');
        $coaPpnPayable = $this->findCoa($transaction->company_id, '2-2003');

        $netSales = $transaction->subtotal - $transaction->discount_total;
        $taxTotal = $transaction->tax_total;
        $grandTotal = $transaction->grand_total;

        $journalNumber = $this->generateJournalNumber($transaction->company_id, 'POS');

        return DB::transaction(function () use (
            $transaction, $journalNumber,
            $grandTotal, $netSales, $taxTotal,
            $coaCash, $coaSalesRevenue, $coaPpnPayable
        ) {
            $journal = Journal::create([
                'company_id' => $transaction->company_id,
                'journal_number' => $journalNumber,
                'journal_date' => Carbon::parse($transaction->transaction_date)->format('Y-m-d'),
                'journal_type' => 'sales',
                'description' => "Penjualan POS {$transaction->receipt_number}",
                'total_debit' => $grandTotal,
                'total_credit' => $grandTotal,
                'reference_type' => PosTransaction::class,
                'reference_id' => $transaction->id,
                'status' => 'posted',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            $entries = [];

            // Dr. Kas/Bank
            $entries[] = [
                'journal_id' => $journal->id,
                'coa_id' => $coaCash->id,
                'description' => "Penerimaan Kas - {$transaction->receipt_number}",
                'debit' => $grandTotal,
                'credit' => 0,
            ];

            // Cr. Pendapatan Penjualan
            $entries[] = [
                'journal_id' => $journal->id,
                'coa_id' => $coaSalesRevenue->id,
                'description' => "Pendapatan - {$transaction->receipt_number}",
                'debit' => 0,
                'credit' => $netSales,
            ];

            // Cr. Utang PPN (jika ada)
            if ($taxTotal > 0) {
                $entries[] = [
                    'journal_id' => $journal->id,
                    'coa_id' => $coaPpnPayable->id,
                    'description' => "PPN Keluaran - {$transaction->receipt_number}",
                    'debit' => 0,
                    'credit' => $taxTotal,
                ];
            }

            JournalEntry::insert($entries);

            $transaction->update(['journal_id' => $journal->id]);

            return $journal->fresh(['journalEntries.coa']);
        });
    }

    // ─── CRM DEAL → INVOICE ──────────────────────────────────────

    public function createInvoiceFromDeal(Deal $deal): Invoice
    {
        if ($deal->status !== 'won') {
            throw new \RuntimeException('Deal harus berstatus "won" untuk membuat invoice.');
        }

        if ($deal->invoice_id) {
            $existing = Invoice::find($deal->invoice_id);
            if ($existing) {
                throw new \RuntimeException('Invoice sudah dibuat untuk deal ini: ' . $existing->invoice_number);
            }
        }

        if (!$deal->client_id) {
            throw new \RuntimeException('Deal tidak memiliki klien terkait. Harap pilih klien terlebih dahulu.');
        }

        $invoiceNumber = $this->generateInvoiceNumber($deal->company_id, 'INV');
        $invoiceDate = Carbon::now()->format('Y-m-d');
        $dueDate = Carbon::now()->addDays(30)->format('Y-m-d');

        $amount = $deal->expected_value;
        $taxRate = 11;
        $taxAmount = round($amount * $taxRate / 100, 2);
        $total = round($amount + $taxAmount, 2);

        return DB::transaction(function () use ($deal, $invoiceNumber, $invoiceDate, $dueDate, $amount, $taxAmount, $total) {
            $invoice = Invoice::create([
                'company_id' => $deal->company_id,
                'invoice_number' => $invoiceNumber,
                'invoice_type' => 'sales',
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'reference_entity' => 'deal',
                'reference_id' => $deal->id,
                'subtotal' => $amount,
                'discount_amount' => 0,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'paid_amount' => 0,
                'remaining_amount' => $total,
                'status' => 'draft',
                'notes' => "Invoice dari Deal: {$deal->title}",
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $deal->title,
                'quantity' => 1,
                'unit_price' => $amount,
                'tax_rate' => $taxRate,
                'amount' => $amount,
            ]);

            $deal->update(['invoice_id' => $invoice->id]);

            return $invoice->fresh(['invoiceItems']);
        });
    }

    // ─── PROCUREMENT: GRN → VENDOR INVOICE ───────────────────────

    public function createInvoiceFromGrn(GoodsReceipt $grn): Invoice
    {
        if ($grn->status !== 'posted') {
            throw new \RuntimeException('Penerimaan Barang harus berstatus "posted" untuk membuat invoice vendor.');
        }

        if ($grn->invoice_id) {
            $existing = Invoice::find($grn->invoice_id);
            if ($existing) {
                throw new \RuntimeException('Invoice vendor sudah dibuat untuk GRN ini: ' . $existing->invoice_number);
            }
        }

        $grn->load(['items.poItem', 'purchaseOrder.supplier']);

        $po = $grn->purchaseOrder;
        $supplier = $po->supplier;

        if (!$supplier) {
            throw new \RuntimeException('Purchase Order tidak memiliki supplier terkait.');
        }

        $invoiceNumber = $this->generateInvoiceNumber($grn->company_id, 'INV-P');
        $invoiceDate = Carbon::now()->format('Y-m-d');
        $dueDate = Carbon::now()->addDays(Carbon::parse($supplier->payment_terms ?? 'NET30')->diffInDays(Carbon::now(), false) ?: 30)->format('Y-m-d');

        // Parse payment_terms: NET30 → 30 days, NET60 → 60 days
        $dueDays = 30;
        $pt = strtoupper($supplier->payment_terms ?? 'NET30');
        if (preg_match('/NET(\d+)/', $pt, $m)) {
            $dueDays = (int) $m[1];
        }
        $dueDate = Carbon::now()->addDays($dueDays)->format('Y-m-d');

        return DB::transaction(function () use ($grn, $po, $invoiceNumber, $invoiceDate, $dueDate) {
            $subtotal = 0;
            $taxAmount = 0;
            $items = [];

            foreach ($grn->items as $grnItem) {
                $poItem = $grnItem->poItem;
                $qty = $grnItem->quantity_accepted;
                $price = $grnItem->unit_price;
                $itemAmount = round($qty * $price, 2);
                $subtotal += $itemAmount;

                $itemTaxRate = $poItem ? $poItem->tax_rate : 11;
                $itemTax = round($itemAmount * $itemTaxRate / 100, 2);
                $taxAmount += $itemTax;

                $items[] = [
                    'invoice_id' => null,
                    'description' => $grnItem->item_name . ' (PO: ' . ($poItem?->id ?? '-') . ')',
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'tax_rate' => $itemTaxRate,
                    'amount' => $itemAmount,
                ];
            }

            $total = round($subtotal + $taxAmount, 2);

            $invoice = Invoice::create([
                'company_id' => $grn->company_id,
                'invoice_number' => $invoiceNumber,
                'invoice_type' => 'purchase',
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'reference_entity' => 'goods_receipt',
                'reference_id' => $grn->id,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'paid_amount' => 0,
                'remaining_amount' => $total,
                'status' => 'draft',
                'notes' => "Invoice Vendor dari GRN {$grn->grn_number} (PO {$po->po_number}, Supplier: {$po->supplier->name})",
            ]);

            foreach ($items as &$item) {
                $item['invoice_id'] = $invoice->id;
            }
            InvoiceItem::insert($items);

            $grn->update(['invoice_id' => $invoice->id]);

            return $invoice->fresh(['invoiceItems']);
        });
    }

    // ─── PROCUREMENT: VENDOR INVOICE PAYMENT ─────────────────────

    public function payVendorInvoice(Invoice $invoice, float $amount, int $paymentMethodId): Payment
    {
        if ($invoice->invoice_type !== 'purchase') {
            throw new \RuntimeException('Hanya invoice purchase yang bisa dibayar melalui fungsi ini.');
        }

        if (!in_array($invoice->status, ['draft', 'sent', 'partial', 'overdue'])) {
            throw new \RuntimeException('Invoice tidak dalam status yang bisa dibayar. Status saat ini: ' . $invoice->status);
        }

        if ($amount <= 0) {
            throw new \RuntimeException('Jumlah pembayaran harus lebih dari nol.');
        }

        if ($amount > $invoice->remaining_amount) {
            throw new \RuntimeException('Jumlah pembayaran melebihi sisa tagihan. Sisa: Rp ' . number_format($invoice->remaining_amount, 2, ',', '.'));
        }

        $paymentNumber = $this->generatePaymentNumber($invoice->company_id, 'PAY');

        return DB::transaction(function () use ($invoice, $amount, $paymentMethodId, $paymentNumber) {
            $payment = Payment::create([
                'company_id' => $invoice->company_id,
                'payment_number' => $paymentNumber,
                'payment_date' => Carbon::now()->format('Y-m-d'),
                'payment_method_id' => $paymentMethodId,
                'amount' => $amount,
                'reference_number' => $invoice->invoice_number,
                'notes' => "Pembayaran vendor invoice {$invoice->invoice_number}",
                'status' => 'confirmed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);

            InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'amount' => $amount,
            ]);

            $newPaid = $invoice->paid_amount + $amount;
            $newRemaining = $invoice->total - $newPaid;

            $newStatus = 'partial';
            if ($newRemaining <= 0) {
                $newStatus = 'paid';
            } elseif ($invoice->status === 'overdue') {
                $newStatus = 'partial';
            }

            $invoice->update([
                'paid_amount' => $newPaid,
                'remaining_amount' => max(0, $newRemaining),
                'status' => $newStatus,
            ]);

            return $payment->fresh(['invoices']);
        });
    }

    // ─── 3-WAY MATCH ─────────────────────────────────────────────

    public function validateThreeWayMatch(PurchaseOrder $po): array
    {
        $po->load(['items', 'goodsReceipts.items', 'goodsReceipts.items.poItem']);

        $discrepancies = [];
        $allMatched = true;

        $grns = $po->goodsReceipts->where('status', 'posted');

        // Collect GRN totals per PO item
        $grnReceived = [];
        foreach ($grns as $grn) {
            foreach ($grn->items as $grnItem) {
                $poItemId = $grnItem->po_item_id;
                if (!isset($grnReceived[$poItemId])) {
                    $grnReceived[$poItemId] = [
                        'qty' => 0,
                        'item_name' => $grnItem->item_name,
                        'unit_price' => $grnItem->unit_price,
                    ];
                }
                $grnReceived[$poItemId]['qty'] += $grnItem->quantity_accepted;
            }
        }

        // Find invoices linked to GRNs
        $invoiceItems = [];
        foreach ($grns as $grn) {
            if ($grn->invoice_id) {
                $invoice = Invoice::with('invoiceItems')->find($grn->invoice_id);
                if ($invoice) {
                    foreach ($invoice->invoiceItems as $invItem) {
                        $invoiceItems[] = [
                            'description' => $invItem->description,
                            'qty' => $invItem->quantity,
                            'unit_price' => $invItem->unit_price,
                        ];
                    }
                }
            }
        }

        // Compare PO vs GRN
        foreach ($po->items as $poItem) {
            $poItemId = $poItem->id;
            $orderedQty = $poItem->quantity;
            $receivedQty = $grnReceived[$poItemId]['qty'] ?? 0;

            if ($receivedQty < $orderedQty && $po->status !== 'received') {
                $allMatched = false;
                $discrepancies[] = [
                    'type' => 'qty_short',
                    'item' => $poItem->item_name,
                    'po_qty' => $orderedQty,
                    'grn_qty' => $receivedQty,
                    'diff' => $orderedQty - $receivedQty,
                    'message' => "{$poItem->item_name}: PO={$orderedQty}, GRN={$receivedQty} (kurang " . ($orderedQty - $receivedQty) . ')',
                ];
            }

            if ($receivedQty > $orderedQty) {
                $allMatched = false;
                $discrepancies[] = [
                    'type' => 'qty_over',
                    'item' => $poItem->item_name,
                    'po_qty' => $orderedQty,
                    'grn_qty' => $receivedQty,
                    'diff' => $receivedQty - $orderedQty,
                    'message' => "{$poItem->item_name}: PO={$orderedQty}, GRN={$receivedQty} (lebih " . ($receivedQty - $orderedQty) . ')',
                ];
            }

            if (isset($grnReceived[$poItemId]) && $grnReceived[$poItemId]['unit_price'] != $poItem->unit_price) {
                $allMatched = false;
                $discrepancies[] = [
                    'type' => 'price_mismatch',
                    'item' => $poItem->item_name,
                    'po_price' => $poItem->unit_price,
                    'grn_price' => $grnReceived[$poItemId]['unit_price'],
                    'diff' => $grnReceived[$poItemId]['unit_price'] - $poItem->unit_price,
                    'message' => "{$poItem->item_name}: Harga PO={$poItem->unit_price}, GRN=" . $grnReceived[$poItemId]['unit_price'],
                ];
            }
        }

        // Compare Invoice vs GRN (simplified)
        $totalGrnValue = 0;
        foreach ($grnReceived as $item) {
            $totalGrnValue += round($item['qty'] * $item['unit_price'], 2);
        }

        $totalInvoiceValue = 0;
        foreach ($invoiceItems as $invItem) {
            $totalInvoiceValue += round($invItem['qty'] * $invItem['unit_price'], 2);
        }

        if ($totalGrnValue > 0 && $totalInvoiceValue > 0 && abs($totalGrnValue - $totalInvoiceValue) > 1) {
            $allMatched = false;
            $discrepancies[] = [
                'type' => 'invoice_grn_mismatch',
                'item' => 'Total',
                'grn_value' => $totalGrnValue,
                'invoice_value' => $totalInvoiceValue,
                'diff' => $totalInvoiceValue - $totalGrnValue,
                'message' => "Total GRN={$totalGrnValue}, Total Invoice={$totalInvoiceValue} (selisih " . ($totalInvoiceValue - $totalGrnValue) . ')',
            ];
        }

        return [
            'matched' => $allMatched && count($grnReceived) > 0,
            'po_id' => $po->id,
            'po_number' => $po->po_number,
            'po_status' => $po->status,
            'grn_count' => $grns->count(),
            'invoice_count' => $grns->filter(fn($g) => $g->invoice_id)->count(),
            'discrepancies' => $discrepancies,
            'discrepancy_count' => count($discrepancies),
        ];
    }

    // ─── HELPER METHODS ──────────────────────────────────────────

    private function findCoa(int $companyId, string $code): Coa
    {
        $coa = Coa::where('company_id', $companyId)
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$coa) {
            throw new \RuntimeException("COA dengan kode '{$code}' tidak ditemukan untuk company_id={$companyId}. Pastikan Chart of Account sudah disiapkan.");
        }

        return $coa;
    }

    private function generateJournalNumber(int $companyId, string $prefix): string
    {
        $date = Carbon::now()->format('Ym');
        $last = Journal::where('company_id', $companyId)
            ->where('journal_number', 'like', "JRN-{$prefix}-{$date}-%")
            ->orderBy('journal_number', 'desc')
            ->first();

        $seq = 1;
        if ($last && preg_match('/-(\d{5})$/', $last->journal_number, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf("JRN-{$prefix}-{$date}-%05d", $seq);
    }

    private function generateInvoiceNumber(int $companyId, string $prefix): string
    {
        $date = Carbon::now()->format('Ym');
        $last = Invoice::where('company_id', $companyId)
            ->where('invoice_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        $seq = 1;
        if ($last && preg_match('/-(\d{5})$/', $last->invoice_number, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf("{$prefix}-{$date}-%05d", $seq);
    }

    private function generatePaymentNumber(int $companyId, string $prefix): string
    {
        $date = Carbon::now()->format('Ym');
        $last = Payment::where('company_id', $companyId)
            ->where('payment_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('payment_number', 'desc')
            ->first();

        $seq = 1;
        if ($last && preg_match('/-(\d{5})$/', $last->payment_number, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf("{$prefix}-{$date}-%05d", $seq);
    }
}
