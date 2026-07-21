<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketTag extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'color',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function tickets()
    {
        return $this->belongsToMany(Ticket::class, 'ticket_tag_relation', 'tag_id', 'ticket_id');
    }
}
