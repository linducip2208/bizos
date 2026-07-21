<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoiceChannel extends Model
{
    protected $fillable = [
        'name',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members()
    {
        return $this->hasMany(VoiceChannelMember::class);
    }

    public function memberUsers()
    {
        return $this->belongsToMany(User::class, 'voice_channel_members', 'voice_channel_id', 'user_id')
            ->withPivot(['last_active_at'])
            ->withTimestamps();
    }

    public function voiceNotes()
    {
        return $this->hasMany(VoiceNote::class, 'context_id')
            ->where('context_type', 'voice_channel');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
