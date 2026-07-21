<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingTranscript extends Model
{
    protected $fillable = [
        'meeting_id', 'recording_id', 'language', 'full_text', 'segments', 'ai_model',
    ];

    protected $casts = [
        'segments' => 'json',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    public function recording()
    {
        return $this->belongsTo(MeetingRecording::class, 'recording_id');
    }
}
