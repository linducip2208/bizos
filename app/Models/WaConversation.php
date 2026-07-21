<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaConversation extends Model
{
    protected $fillable = [
        'company_id',
        'contact_phone',
        'contact_name',
        'last_message',
        'last_message_at',
        'unread_count',
        'assigned_to',
        'status',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'unread_count' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }
}
