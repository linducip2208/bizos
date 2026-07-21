<?php

namespace App\Listeners;

use App\Models\Lead;
use App\Models\Deal;
use App\Models\Invoice;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Ticket;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\StockBalance;
use App\Services\WebhookService;
use App\Services\WorkflowAutomationService;

class FireWebhooks
{
    public function __construct(
        protected WebhookService $webhookService,
        protected WorkflowAutomationService $workflowService,
    ) {}

    public function handle($event): void
    {
        $eventName = $this->mapEventName($event);
        if (! $eventName) {
            return;
        }

        $payload = $this->buildPayload($event);
        $context = $this->buildContext($event);

        $this->webhookService->fire($eventName, $payload);
        $this->workflowService->evaluate($eventName, $context);
    }

    protected function mapEventName($event): ?string
    {
        return match (get_class($event)) {
            \App\Events\EmployeeCreated::class => 'employee.created',
            \App\Events\InvoicePaid::class => 'invoice.paid',
            \App\Events\LeaveSubmitted::class => 'leave.submitted',
            \App\Events\LeaveApproved::class => 'leave.approved',
            \App\Events\TicketCreated::class => 'ticket.created',
            \App\Events\TicketClosed::class => 'ticket.closed',
            \App\Events\DealWon::class => 'deal.won',
            \App\Events\DealLost::class => 'deal.lost',
            \App\Events\LeadCreated::class => 'lead.created',
            \App\Events\LeadConverted::class => 'lead.converted',
            \App\Events\AttendanceClockedIn::class => 'attendance.clock_in',
            \App\Events\AttendanceLate::class => 'attendance.late',
            \App\Events\InvoiceOverdue::class => 'invoice.overdue',
            \App\Events\StockLow::class => 'stock.low_stock',
            \App\Events\PayrollProcessed::class => 'payroll.processed',
            default => null,
        };
    }

    protected function buildPayload($event): array
    {
        $model = $this->getModel($event);
        if (! $model) {
            return ['event' => class_basename($event), 'timestamp' => now()->toIso8601String()];
        }

        return [
            'event' => $this->mapEventName($event),
            'timestamp' => now()->toIso8601String(),
            'data' => $model->toArray(),
        ];
    }

    protected function buildContext($event): array
    {
        $model = $this->getModel($event);
        if (! $model) {
            return [];
        }

        $context = $model->toArray();
        $context['event'] = $this->mapEventName($event);
        $context['timestamp'] = now()->toIso8601String();

        if (isset($context['company_id'])) {
            $context['company_id'] = (int) $context['company_id'];
        }

        if ($model instanceof Employee) {
            $context['employee_id'] = $model->id;
        }

        if ($model instanceof Attendance) {
            $context['employee_id'] = $model->employee_id;
        }

        if ($model instanceof Leave) {
            $context['employee_id'] = $model->employee_id;
        }

        return $context;
    }

    protected function getModel($event): mixed
    {
        return match (true) {
            $event instanceof \App\Events\EmployeeCreated => $event->employee,
            $event instanceof \App\Events\InvoicePaid => $event->invoice,
            $event instanceof \App\Events\InvoiceOverdue => $event->invoice,
            $event instanceof \App\Events\LeaveSubmitted => $event->leave,
            $event instanceof \App\Events\LeaveApproved => $event->leave,
            $event instanceof \App\Events\TicketCreated => $event->ticket,
            $event instanceof \App\Events\TicketClosed => $event->ticket,
            $event instanceof \App\Events\DealWon => $event->deal,
            $event instanceof \App\Events\DealLost => $event->deal,
            $event instanceof \App\Events\LeadCreated => $event->lead,
            $event instanceof \App\Events\LeadConverted => $event->lead,
            $event instanceof \App\Events\AttendanceClockedIn => $event->attendance,
            $event instanceof \App\Events\AttendanceLate => $event->attendance,
            $event instanceof \App\Events\StockLow => $event->stock,
            $event instanceof \App\Events\PayrollProcessed => $event->payroll,
            default => null,
        };
    }
}
