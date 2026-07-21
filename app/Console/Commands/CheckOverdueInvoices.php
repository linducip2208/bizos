<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Notification;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'invoices:check-overdue';

    protected $description = 'Check for overdue invoices and update their status + notify.';

    public function handle(): int
    {
        $overdue = Invoice::where('due_date', '<', now())
            ->whereNotIn('status', ['paid', 'overdue', 'cancelled', 'void'])
            ->get();

        $count = 0;

        foreach ($overdue as $invoice) {
            $invoice->update(['status' => 'overdue']);
            $count++;

            $companyId = $invoice->company_id;

            $admins = \App\Models\User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'super_admin', 'owner']);
            })->pluck('id')->toArray();

            foreach ($admins as $userId) {
                Notification::create([
                    'user_id' => $userId,
                    'notification_type' => 'invoice_overdue',
                    'title' => 'Invoice Jatuh Tempo',
                    'body' => "Invoice #{$invoice->invoice_number} (Rp " . number_format($invoice->total, 0, ',', '.') . ") telah melewati jatuh tempo.",
                    'data' => json_encode([
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'due_date' => $invoice->due_date->format('Y-m-d'),
                    ]),
                    'channel' => 'database',
                    'is_read' => false,
                    'read_at' => null,
                    'sent_at' => null,
                ]);
            }
        }

        $this->info("{$count} invoice(s) marked as overdue.");

        return self::SUCCESS;
    }
}
