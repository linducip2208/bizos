<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class CheckSlaBreaches extends Command
{
    protected $signature = 'helpdesk:check-sla';

    protected $description = 'Check all open tickets for SLA breaches and send alert notifications';

    public function handle(): int
    {
        $this->info('Checking SLA breaches...');

        $tickets = Ticket::with(['slaPolicy', 'assignedTo', 'assignedTo.user'])
            ->whereNotIn('status', ['resolved', 'closed'])
            ->whereNotNull('sla_policy_id')
            ->get();

        $breachCount = 0;

        foreach ($tickets as $ticket) {
            if (!$ticket->slaPolicy) {
                continue;
            }

            if ($ticket->slaPolicy->isBreached($ticket)) {
                $breachCount++;

                $this->warn("SLA breached: #{$ticket->ticket_number} ({$ticket->subject})");

                if ($ticket->assignedTo && $ticket->assignedTo->user) {
                    \App\Services\NotificationService::send(
                        $ticket->assignedTo->user->id,
                        'sla_breach',
                        'SLA Terlampaui',
                        "Tiket #{$ticket->ticket_number} \"{$ticket->subject}\" telah melampaui SLA.",
                        'in_app',
                        ['ticket_id' => $ticket->id, 'ticket_number' => $ticket->ticket_number]
                    );
                }
            }
        }

        $this->info("SLA check completed. {$breachCount} ticket(s) breached.");

        return self::SUCCESS;
    }
}
