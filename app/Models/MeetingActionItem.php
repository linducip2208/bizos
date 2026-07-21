<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingActionItem extends Model
{
    protected $table = 'meeting_action_items';

    protected $fillable = [
        'meeting_id',
        'assigned_to',
        'title',
        'description',
        'due_date',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function assignee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }
}
