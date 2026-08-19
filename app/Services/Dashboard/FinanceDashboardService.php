<?php

namespace App\Services\Dashboard;

use App\Models\Invoice;
use App\Models\Journal;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\User;

final class FinanceDashboardService extends AbstractDashboardService
{
    protected function build(DashboardFilter $filter, User $user): array
    {
        $invoices = $this->tenant(Invoice::query(), $filter);
        $journals = $this->tenant(Journal::query(), $filter);

        return [
            'cards' => [
                ['label' => 'Invoice Dibayar', 'value' => (float) (clone $invoices)->where('status', 'paid')->whereBetween('invoice_date', [$filter->dateFrom, $filter->dateTo])->sum('total'), 'format' => 'currency'],
                ['label' => 'Piutang Berjalan', 'value' => (float) (clone $invoices)->whereIn('status', ['sent', 'overdue'])->sum('remaining_amount'), 'format' => 'currency'],
                ['label' => 'Pembayaran Dikonfirmasi', 'value' => (float) $this->tenant(Payment::query(), $filter)->where('status', 'confirmed')->whereBetween('payment_date', [$filter->dateFrom, $filter->dateTo])->sum('amount'), 'format' => 'currency'],
                ['label' => 'Komitmen Pembelian', 'value' => (float) $this->tenant(PurchaseOrder::query(), $filter)->whereIn('status', ['approved', 'partially_received'])->sum('total'), 'format' => 'currency'],
            ],
            'trend' => (clone $journals)->where('status', 'posted')->whereBetween('journal_date', [$filter->dateFrom, $filter->dateTo])->selectRaw('DATE(journal_date) as period, SUM(total_debit) as debit, SUM(total_credit) as credit')->groupBy('period')->orderBy('period')->get()->toArray(),
        ];
    }
}
