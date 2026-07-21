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
        'provider',
        'provider_meeting_id',
        'meeting_url',
        'passcode',
        'dial_in',
        'recording_path',
        'transcript_text',
        'ai_summary',
        'is_recurring',
        'recurrence_frequency',
        'recurrence_until',
        'linked_project_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'ai_summary' => 'json',
        'is_recurring' => 'boolean',
        'recurrence_until' => 'datetime',
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

    public function recordings()
    {
        return $this->hasMany(MeetingRecording::class);
    }

    public function transcripts()
    {
        return $this->hasMany(MeetingTranscript::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(MeetingAttendanceLog::class);
    }

    public function linkedProject()
    {
        return $this->belongsTo(Project::class, 'linked_project_id');
    }
}
