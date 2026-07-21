<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'company_id',
        'organized_by',
        'title',
        'description',
        'start_time',
        'end_time',
        'location',
        'meeting_link',
        'meeting_type',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function organizer()
    {
        return $this->belongsTo(Employee::class, 'organized_by');
    }

    public function attendees()
    {
        return $this->hasMany(MeetingAttendee::class);
    }

    public function minutes()
    {
        return $this->hasMany(MeetingMinute::class);
    }

    public function recaps()
    {
        return $this->hasMany(MeetingRecap::class);
    }

    public function actionItems()
    {
        return $this->hasMany(MeetingActionItem::class);
    }
}
