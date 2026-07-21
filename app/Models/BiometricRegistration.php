<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricRegistration extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'public_key',
        'device_name',
        'platform',
        'registered_at',
        'last_used_at',
        'is_active',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }
}
