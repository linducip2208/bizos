<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $fillable = [
        'company_id',
        'license_key_encrypted',
        'module',
        'seats',
        'started_at',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'seats' => 'integer',
        'started_at' => 'date',
        'expires_at' => 'date',
    ];

    protected $hidden = [
        'license_key_encrypted',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
