<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoiceNoteRecipient extends Model
{
    protected $fillable = [
        'voice_note_id',
        'user_id',
        'is_played',
        'played_at',
    ];

    protected $casts = [
        'is_played' => 'boolean',
        'played_at' => 'datetime',
    ];

    public function voiceNote()
    {
        return $this->belongsTo(VoiceNote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markPlayed(): void
    {
        $this->update([
            'is_played' => true,
            'played_at' => now(),
        ]);
    }
}
