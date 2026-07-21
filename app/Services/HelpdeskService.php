<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\TicketReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HelpdeskService
{
    public function createTicket(array $data): Ticket
    {
        $ticket = DB::transaction(function () use ($data) {
            $ticket = Ticket::create([
                'company_id' => $data['company_id'] ?? auth()->user()?->company_id,
                'ticket_number' => $this->generateTicketNumber(),
                'category_id' => $data['category_id'] ?? null,
                'client_id' => $data['client_id'] ?? null,
                'contact_id' => $data['contact_id'] ?? null,
                'subject' => $data['subject'],
                'description' => $data['description'],
                'priority' => $data['priority'] ?? 'medium',
                'status' => 'open',
                'source' => $data['source'] ?? 'portal',
                'created_by' => $data['created_by'] ?? null,
            ]);

            $this->applySlaPolicy($ticket);

            $ticket->activities()->create([
                'user_id' => auth()->id() ?? $data['user_id'] ?? null,
                'employee_id' => $data['created_by'] ?? null,
                'activity_type' => 'created',
                'new_value' => $ticket->status,
                'created_at' => now(),
            ]);

            if (!empty($data['tag_ids'])) {
                $ticket->tags()->sync($data['tag_ids']);
            }

            return $ticket;
        });

        return $ticket;
    }

    public function autoAssign(Ticket $ticket): void
    {
        $lastEmployee = Ticket::whereNotNull('assigned_to')
            ->where('company_id', $ticket->company_id)
            ->orderBy('created_at', 'desc')
            ->value('assigned_to');

        $nextEmployee = Employee::where('company_id', $ticket->company_id)
            ->where('status', 'active')
            ->when($lastEmployee, function ($query) use ($lastEmployee) {
                return $query->where('id', '>', $lastEmployee);
            })
            ->orderBy('id')
            ->first();

        if (!$nextEmployee) {
            $nextEmployee = Employee::where('company_id', $ticket->company_id)
                ->where('status', 'active')
                ->orderBy('id')
                ->first();
        }

        if ($nextEmployee) {
            $oldAssignee = $ticket->assigned_to;
            $ticket->update(['assigned_to' => $nextEmployee->id]);

            $ticket->activities()->create([
                'employee_id' => auth()->user()?->employee_id ?? null,
                'activity_type' => 'assigned',
                'old_value' => (string) $oldAssignee,
                'new_value' => (string) $nextEmployee->id,
                'created_at' => now(),
            ]);
        }
    }

    public function calculateSla(Ticket $ticket): array
    {
        if (!$ticket->slaPolicy) {
            return [
                'has_sla' => false,
                'breached' => false,
            ];
        }

        $responseLeft = $ticket->slaPolicy->responseTimeLeft($ticket);
        $resolutionLeft = $ticket->slaPolicy->resolutionTimeLeft($ticket);
        $breached = $ticket->slaPolicy->isBreached($ticket);

        return [
            'has_sla' => true,
            'sla_name' => $ticket->slaPolicy->name,
            'response_time_hours' => $ticket->slaPolicy->response_time_hours,
            'resolution_time_hours' => $ticket->slaPolicy->resolution_time_hours,
            'response_time_left' => max(0, round($responseLeft, 1)),
            'resolution_time_left' => max(0, round($resolutionLeft, 1)),
            'breached' => $breached,
        ];
    }

    public function addReply(Ticket $ticket, array $data): TicketReply
    {
        $reply = DB::transaction(function () use ($ticket, $data) {
            $reply = $ticket->replies()->create([
                'user_id' => $data['user_id'] ?? auth()->id(),
                'employee_id' => $data['employee_id'] ?? auth()->user()?->employee_id,
                'message' => $data['message'],
                'is_internal' => $data['is_internal'] ?? false,
                'attachments' => $data['attachments'] ?? null,
            ]);

            if ($ticket->first_response_at === null && !($data['is_internal'] ?? false)) {
                $ticket->update(['first_response_at' => now()]);
            }

            $ticket->activities()->create([
                'user_id' => $data['user_id'] ?? auth()->id(),
                'employee_id' => $data['employee_id'] ?? auth()->user()?->employee_id,
                'activity_type' => ($data['is_internal'] ?? false) ? 'note_added' : 'replied',
                'created_at' => now(),
            ]);

            return $reply;
        });

        return $reply;
    }

    public function changeStatus(Ticket $ticket, string $status, ?string $note = null): void
    {
        $oldStatus = $ticket->status;

        $ticket->update(['status' => $status]);

        if ($status === 'in_progress' && $ticket->first_response_at === null) {
            $ticket->update(['first_response_at' => now()]);
        }

        if ($status === 'resolved') {
            $ticket->update(['resolved_at' => now()]);
        }

        if ($status === 'closed') {
            $ticket->update(['closed_at' => now()]);
        }

        $ticket->activities()->create([
            'user_id' => auth()->id(),
            'employee_id' => auth()->user()?->employee_id,
            'activity_type' => 'status_changed',
            'old_value' => $oldStatus,
            'new_value' => $status,
            'created_at' => now(),
        ]);

        if ($note) {
            $ticket->replies()->create([
                'employee_id' => auth()->user()?->employee_id,
                'message' => $note,
                'is_internal' => true,
            ]);
        }
    }

    public function escalate(Ticket $ticket): void
    {
        $priorityOrder = ['low' => 0, 'medium' => 1, 'high' => 2, 'urgent' => 3];
        $currentLevel = $priorityOrder[$ticket->priority] ?? 1;

        if ($currentLevel < 3) {
            $newPriority = array_search($currentLevel + 1, $priorityOrder);
            $oldPriority = $ticket->priority;

            $ticket->update([
                'priority' => $newPriority,
            ]);

            $escalatedSla = SlaPolicy::where('priority', $newPriority)
                ->where('company_id', $ticket->company_id)
                ->when($ticket->category_id, function ($q) use ($ticket) {
                    return $q->where('category_id', $ticket->category_id);
                })
                ->where('is_active', true)
                ->first();

            if ($escalatedSla) {
                $ticket->update(['sla_policy_id' => $escalatedSla->id]);
            }

            $ticket->activities()->create([
                'employee_id' => auth()->user()?->employee_id,
                'activity_type' => 'priority_changed',
                'old_value' => $oldPriority,
                'new_value' => $newPriority,
                'created_at' => now(),
            ]);
        }
    }

    public function mergeTickets(Ticket $source, Ticket $target): void
    {
        DB::transaction(function () use ($source, $target) {
            $source->replies()->update(['ticket_id' => $target->id]);
            $source->attachments()->update(['ticket_id' => $target->id]);
            $source->activities()->update(['ticket_id' => $target->id]);

            $sourceTagIds = $source->tags()->pluck('ticket_tags.id')->toArray();
            $target->tags()->syncWithoutDetaching($sourceTagIds);

            $target->activities()->create([
                'employee_id' => auth()->user()?->employee_id,
                'activity_type' => 'note_added',
                'new_value' => "Tiket #{$source->ticket_number} digabungkan ke tiket ini.",
                'created_at' => now(),
            ]);

            $source->update(['status' => 'closed', 'closed_at' => now(), 'parent_id' => $target->id]);
        });
    }

    public function getQueueStats(?int $companyId = null): array
    {
        $companyId = $companyId ?? auth()->user()?->company_id;

        $query = Ticket::where('company_id', $companyId);

        $clone = clone $query;
        $overdue = (clone $clone)->whereNotIn('status', ['resolved', 'closed'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();

        return [
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'waiting_on_customer' => (clone $query)->where('status', 'waiting_on_customer')->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
            'overdue' => $overdue,
            'unassigned' => (clone $query)->whereNull('assigned_to')
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count(),
            'total' => (clone $query)->count(),
        ];
    }

    public function generateTicketNumber(): string
    {
        $date = now()->format('Ymd');
        $count = Ticket::whereDate('created_at', now()->toDateString())->count() + 1;

        return 'TKT-' . $date . '-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }

    public function applySlaPolicy(Ticket $ticket): void
    {
        $sla = SlaPolicy::where('priority', $ticket->priority)
            ->where('company_id', $ticket->company_id)
            ->when($ticket->category_id, function ($query) use ($ticket) {
                return $query->where(function ($q) use ($ticket) {
                    $q->where('category_id', $ticket->category_id)
                        ->orWhereNull('category_id');
                });
            })
            ->where('is_active', true)
            ->orderByRaw('category_id IS NOT NULL DESC')
            ->first();

        if ($sla) {
            $ticket->update([
                'sla_policy_id' => $sla->id,
                'due_date' => now()->addHours($sla->resolution_time_hours),
            ]);
        }
    }
}
