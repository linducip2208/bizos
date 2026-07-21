<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingAttendanceLog extends Model
{
    protected $fillable = [
        'meeting_id', 'participant_name', 'participant_email',
        'join_time', 'leave_time', 'duration_minutes',
    ];

    protected $casts = [
        'join_time' => 'datetime',
        'leave_time' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
