<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoiceChannelMember extends Model
{
    protected $fillable = [
        'voice_channel_id',
        'user_id',
        'last_active_at',
    ];

    protected $casts = [
        'last_active_at' => 'datetime',
    ];

    public function voiceChannel()
    {
        return $this->belongsTo(VoiceChannel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
