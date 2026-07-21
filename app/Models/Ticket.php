<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Concerns\HasBranchScope;

class Ticket extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'company_id',
        'ticket_number',
        'category_id',
        'sla_policy_id',
        'client_id',
        'contact_id',
        'assigned_to',
        'subject',
        'description',
        'priority',
        'status',
        'source',
        'due_date',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'created_by',
        'parent_id',
        'satisfaction_rating',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'satisfaction_rating' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function slaPolicy()
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_policy_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contact()
    {
        return $this->belongsTo(ClientContact::class, 'contact_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function parent()
    {
        return $this->belongsTo(Ticket::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Ticket::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function activities()
    {
        return $this->hasMany(TicketActivity::class);
    }

    public function tags()
    {
        return $this->belongsToMany(TicketTag::class, 'ticket_tag_relation', 'ticket_id', 'tag_id');
    }
}
