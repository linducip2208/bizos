<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingRecap extends Model
{
    protected $table = 'meeting_recaps';

    protected $fillable = [
        'meeting_id',
        'summary',
        'key_points',
        'sentiment',
        'transcript_path',
        'status',
        'ai_provider',
    ];

    protected $casts = [
        'key_points' => 'array',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }
}
