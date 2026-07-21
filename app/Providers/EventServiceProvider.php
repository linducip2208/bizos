<?php

namespace App\Providers;

use App\Events\AttendanceClockedIn;
use App\Events\AttendanceLate;
use App\Events\DealLost;
use App\Events\DealWon;
use App\Events\EmployeeCreated;
use App\Events\InvoiceOverdue;
use App\Events\InvoicePaid;
use App\Events\LeadConverted;
use App\Events\LeadCreated;
use App\Events\LeaveApproved;
use App\Events\LeaveSubmitted;
use App\Events\PayrollProcessed;
use App\Events\StockLow;
use App\Events\TicketClosed;
use App\Events\TicketCreated;
use App\Listeners\FireWebhooks;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        EmployeeCreated::class => [
            FireWebhooks::class,
        ],
        InvoicePaid::class => [
            FireWebhooks::class,
        ],
        InvoiceOverdue::class => [
            FireWebhooks::class,
        ],
        LeaveSubmitted::class => [
            FireWebhooks::class,
        ],
        LeaveApproved::class => [
            FireWebhooks::class,
        ],
        TicketCreated::class => [
            FireWebhooks::class,
        ],
        TicketClosed::class => [
            FireWebhooks::class,
        ],
        DealWon::class => [
            FireWebhooks::class,
        ],
        DealLost::class => [
            FireWebhooks::class,
        ],
        LeadCreated::class => [
            FireWebhooks::class,
        ],
        LeadConverted::class => [
            FireWebhooks::class,
        ],
        AttendanceClockedIn::class => [
            FireWebhooks::class,
        ],
        AttendanceLate::class => [
            FireWebhooks::class,
        ],
        StockLow::class => [
            FireWebhooks::class,
        ],
        PayrollProcessed::class => [
            FireWebhooks::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}
