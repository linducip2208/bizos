<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingRecording extends Model
{
    protected $fillable = [
        'meeting_id', 'provider', 'provider_recording_id', 'file_name',
        'file_path', 'file_size_bytes', 'duration_seconds', 'file_type',
        'status', 'recorded_at',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
        'duration_seconds' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function transcripts()
    {
        return $this->hasMany(MeetingTranscript::class, 'recording_id');
    }
}
