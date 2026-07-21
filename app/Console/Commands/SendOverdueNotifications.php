<?php

namespace App\Console\Commands;

use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Services\NotificationService;
use App\Services\NotificationTriggerService;
use Illuminate\Console\Command;

class SendOverdueNotifications extends Command
{
    protected $signature = 'bizos:notify-overdue';
    protected $description = 'Kirim notifikasi untuk semua item yang overdue';

    public function handle(): int
    {
        $this->info('Memulai pengecekan overdue...');

        $this->checkOverdueInvoices();
        $this->checkPendingApprovals();
        $this->checkSlaBreachedTickets();

        $this->info('Selesai.');
        return self::SUCCESS;
    }

    protected function checkOverdueInvoices(): void
    {
        $overdueInvoices = Invoice::where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'draft')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->get();

        $count = $overdueInvoices->count();
        $this->info("  Invoice overdue: {$count} ditemukan.");

        /** @var NotificationTriggerService $notifTrigger */
        $notifTrigger = app(NotificationTriggerService::class);

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = (int) now()->startOfDay()->diffInDays($invoice->due_date->startOfDay());

            $notifTrigger->onInvoiceOverdue($invoice);

            if ($daysOverdue > 0 && $daysOverdue % 7 === 0) {
                $this->sendFinanceReminder($invoice, $daysOverdue);
            }

            $this->line("    - Invoice #{$invoice->invoice_number}: {$daysOverdue} hari overdue.");
        }
    }

    protected function checkPendingApprovals(): void
    {
        $threeDaysAgo = now()->subDays(3);

        $pendingApprovals = ApprovalRequest::with(['workflow', 'requester'])
            ->where('status', 'pending')
            ->where('submitted_at', '<', $threeDaysAgo)
            ->get();

        $count = $pendingApprovals->count();
        $this->info("  Approval pending >3 hari: {$count} ditemukan.");

        foreach ($pendingApprovals as $request) {
            $approverIds = [];
            $currentLevel = $request->currentApprovalLevel;
            if ($currentLevel) {
                $approverIds = $currentLevel->getApproverEmployeeIds();
            }

            foreach ($approverIds as $employeeId) {
                $employee = \App\Models\Employee::find($employeeId);
                if ($employee && $employee->user) {
                    $daysAgo = (int) $request->submitted_at->diffInDays(now());
                    NotificationService::send(
                        userId: $employee->user->id,
                        type: 'approval_overdue',
                        title: 'Pengingat: Approval Tertunda',
                        body: "Permintaan approval \"{$request->title}\" sudah menunggu {$daysAgo} hari. Mohon segera ditindaklanjuti.",
                        channel: 'in_app',
                        data: [
                            'approval_request_id' => $request->id,
                            'module' => $request->module,
                            'module_id' => $request->module_id,
                        ]
                    );
                }
            }

            $this->line("    - {$request->title}: {$request->submitted_at->diffInDays(now())} hari.");
        }
    }

    protected function checkSlaBreachedTickets(): void
    {
        $breachedTickets = Ticket::whereNotIn('status', ['closed', 'resolved', 'cancelled'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->get();

        $count = $breachedTickets->count();
        $this->info("  Tiket SLA breached: {$count} ditemukan.");

        foreach ($breachedTickets as $ticket) {
            if ($ticket->assigned_to) {
                $employee = \App\Models\Employee::find($ticket->assigned_to);
                if ($employee && $employee->user) {
                    $hoursBreached = (int) $ticket->due_date->diffInHours(now());
                    NotificationService::send(
                        userId: $employee->user->id,
                        type: 'ticket_sla_breach',
                        title: 'SLA Tiket Terlampaui',
                        body: "Tiket #{$ticket->ticket_number}: {$ticket->subject} telah melampaui SLA ({$hoursBreached} jam lalu). Prioritas: {$ticket->priority}.",
                        channel: 'in_app',
                        data: [
                            'ticket_id' => $ticket->id,
                            'sla_hours' => $hoursBreached,
                        ]
                    );
                }
            }

            $this->line("    - Tiket #{$ticket->ticket_number}: {$ticket->subject}");
        }
    }

    protected function sendFinanceReminder(Invoice $invoice, int $daysOverdue): void
    {
        $financeEmployees = \App\Models\Employee::whereHas('department', function ($q) {
            $q->where('code', 'FA');
        })->get();

        foreach ($financeEmployees as $employee) {
            if ($employee->user) {
                NotificationService::send(
                    userId: $employee->user->id,
                    type: 'invoice_weekly_reminder',
                    title: 'Pengingat Mingguan: Invoice Overdue',
                    body: "Invoice #{$invoice->invoice_number} sudah overdue {$daysOverdue} hari. Total: Rp " . number_format($invoice->total, 0, ',', '.') . ". Mohon segera ditindaklanjuti.",
                    channel: 'in_app',
                    data: [
                        'invoice_id' => $invoice->id,
                        'days_overdue' => $daysOverdue,
                    ]
                );
            }
        }
    }
}
