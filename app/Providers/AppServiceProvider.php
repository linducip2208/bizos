<?php

namespace App\Providers;

use App\Models\Asset;
use App\Models\Budget;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Product;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Reimbursement;
use App\Models\Task;
use App\Models\Ticket;
use App\Services\ActivityService;
use App\Services\NotificationTriggerService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected array $activityLoggableModels = [
        Employee::class,
        Department::class,
        Leave::class,
        Reimbursement::class,
        Overtime::class,
        PurchaseRequisition::class,
        PurchaseOrder::class,
        Invoice::class,
        Ticket::class,
        Task::class,
        Project::class,
        Budget::class,
        Asset::class,
        Product::class,
    ];

    public function register(): void
    {
        $this->app->singleton(\App\Services\LicenseClient::class);
        $this->app->singleton(NotificationTriggerService::class, fn () => new NotificationTriggerService());
        $this->app->singleton(ActivityService::class, fn () => new ActivityService());
    }

    public function boot(): void
    {
        Relation::morphMap([
            'leave' => \App\Models\Leave::class,
            'reimbursement' => \App\Models\Reimbursement::class,
            'overtime' => \App\Models\Overtime::class,
            'purchase_requisition' => \App\Models\PurchaseRequisition::class,
            'purchase_order' => \App\Models\PurchaseOrder::class,
            'budget' => \App\Models\Budget::class,
            'employee' => \App\Models\Employee::class,
            'department' => \App\Models\Department::class,
            'invoice' => \App\Models\Invoice::class,
            'ticket' => \App\Models\Ticket::class,
            'task' => \App\Models\Task::class,
            'project' => \App\Models\Project::class,
            'asset' => \App\Models\Asset::class,
            'product' => \App\Models\Product::class,
        ]);

        $this->registerActivityLogging();
        $this->registerModelEventListeners();
    }

    protected function registerActivityLogging(): void
    {
        foreach ($this->activityLoggableModels as $modelClass) {
            $modelClass::created(function (Model $model) {
                ActivityService::log('created', $model);
            });

            $modelClass::updated(function (Model $model) {
                $changes = $model->getChanges();
                $unset = ['updated_at'];
                foreach ($unset as $key) {
                    unset($changes[$key]);
                }
                if (!empty($changes)) {
                    if (isset($changes['status'])) {
                        ActivityService::log('status_changed', $model, null, [
                            'from' => $model->getOriginal('status') ?? null,
                            'to' => $changes['status'],
                        ]);
                    } else {
                        ActivityService::log('updated', $model);
                    }
                }
            });

            $modelClass::deleted(function (Model $model) {
                ActivityService::log('deleted', $model);
            });
        }
    }

    protected function registerModelEventListeners(): void
    {
        /** @var NotificationTriggerService $notifTrigger */
        $notifTrigger = app(NotificationTriggerService::class);

        Leave::created(function (Leave $leave) use ($notifTrigger) {
            $notifTrigger->onModelCreated($leave);
        });

        Leave::updated(function (Leave $leave) use ($notifTrigger) {
            $changes = $leave->getChanges();
            $unset = ['updated_at'];
            foreach ($unset as $key) {
                unset($changes[$key]);
            }
            if (!empty($changes)) {
                $notifTrigger->onModelUpdated($leave, $changes);
            }
        });

        Reimbursement::created(function (Reimbursement $r) use ($notifTrigger) {
            $notifTrigger->onModelCreated($r);
        });

        Reimbursement::updated(function (Reimbursement $r) use ($notifTrigger) {
            $changes = $r->getChanges();
            $unset = ['updated_at'];
            foreach ($unset as $key) {
                unset($changes[$key]);
            }
            if (!empty($changes)) {
                $notifTrigger->onModelUpdated($r, $changes);
            }
        });

        Overtime::created(function (Overtime $o) use ($notifTrigger) {
            $notifTrigger->onModelCreated($o);
        });

        Overtime::updated(function (Overtime $o) use ($notifTrigger) {
            $changes = $o->getChanges();
            $unset = ['updated_at'];
            foreach ($unset as $key) {
                unset($changes[$key]);
            }
            if (!empty($changes)) {
                $notifTrigger->onModelUpdated($o, $changes);
            }
        });

        PurchaseRequisition::created(function (PurchaseRequisition $pr) use ($notifTrigger) {
            $notifTrigger->onModelCreated($pr);
        });

        PurchaseRequisition::updated(function (PurchaseRequisition $pr) use ($notifTrigger) {
            $changes = $pr->getChanges();
            $unset = ['updated_at'];
            foreach ($unset as $key) {
                unset($changes[$key]);
            }
            if (!empty($changes)) {
                $notifTrigger->onModelUpdated($pr, $changes);
            }
        });

        PurchaseOrder::created(function (PurchaseOrder $po) use ($notifTrigger) {
            $notifTrigger->onModelCreated($po);
        });

        PurchaseOrder::updated(function (PurchaseOrder $po) use ($notifTrigger) {
            $changes = $po->getChanges();
            $unset = ['updated_at'];
            foreach ($unset as $key) {
                unset($changes[$key]);
            }
            if (!empty($changes)) {
                $notifTrigger->onModelUpdated($po, $changes);
            }
        });

        Invoice::updated(function (Invoice $invoice) use ($notifTrigger) {
            if ($invoice->status === 'overdue' || ($invoice->due_date && $invoice->due_date->isPast() && $invoice->status !== 'paid')) {
                $notifTrigger->onInvoiceOverdue($invoice);
            }
        });

        Ticket::created(function (Ticket $ticket) use ($notifTrigger) {
            $notifTrigger->onModelCreated($ticket);
        });

        Ticket::updated(function (Ticket $ticket) use ($notifTrigger) {
            $changes = $ticket->getChanges();
            $unset = ['updated_at'];
            foreach ($unset as $key) {
                unset($changes[$key]);
            }
            if (!empty($changes)) {
                $notifTrigger->onModelUpdated($ticket, $changes);
            }
            if (isset($changes['assigned_to']) && $changes['assigned_to'] !== $ticket->getOriginal('assigned_to')) {
                $notifTrigger->onTicketAssigned($ticket);
            }
        });
    }
}
