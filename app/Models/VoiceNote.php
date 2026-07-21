<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoiceNote extends Model
{
    protected $fillable = [
        'sender_id',
        'audio_path',
        'duration_seconds',
        'transcript',
        'title',
        'context_type',
        'context_id',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients()
    {
        return $this->hasMany(VoiceNoteRecipient::class);
    }

    public function recipientUsers()
    {
        return $this->belongsToMany(User::class, 'voice_note_recipients', 'voice_note_id', 'user_id')
            ->withPivot(['is_played', 'played_at'])
            ->withTimestamps();
    }

    public function context()
    {
        return $this->morphTo();
    }
}
