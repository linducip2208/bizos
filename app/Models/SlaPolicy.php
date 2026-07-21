<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'category_id',
        'priority',
        'response_time_hours',
        'resolution_time_hours',
        'business_hours_only',
        'is_active',
    ];

    protected $casts = [
        'business_hours_only' => 'boolean',
        'is_active' => 'boolean',
        'response_time_hours' => 'integer',
        'resolution_time_hours' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'sla_policy_id');
    }

    public function isBreached(Ticket $ticket): bool
    {
        if ($ticket->status === 'resolved' || $ticket->status === 'closed') {
            return false;
        }

        $responseTimeLeft = $this->responseTimeLeft($ticket);
        $resolutionTimeLeft = $this->resolutionTimeLeft($ticket);

        if ($responseTimeLeft <= 0) {
            return true;
        }

        if ($ticket->status !== 'open' && $resolutionTimeLeft <= 0) {
            return true;
        }

        return false;
    }

    public function responseTimeLeft(Ticket $ticket): float
    {
        if ($ticket->first_response_at) {
            $actualHours = $ticket->created_at->diffInHours($ticket->first_response_at);
            return $this->response_time_hours - $actualHours;
        }

        $elapsed = $ticket->created_at->diffInHours(now());
        return $this->response_time_hours - $elapsed;
    }

    public function resolutionTimeLeft(Ticket $ticket): float
    {
        if ($ticket->resolved_at) {
            $actualHours = $ticket->created_at->diffInHours($ticket->resolved_at);
            return $this->resolution_time_hours - $actualHours;
        }

        $elapsed = $ticket->created_at->diffInHours(now());
        return $this->resolution_time_hours - $elapsed;
    }
}
