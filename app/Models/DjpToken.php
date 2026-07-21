<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DjpToken extends Model
{
    protected $fillable = [
        'company_id', 'npwp', 'access_token_encrypted', 'refresh_token_encrypted',
        'certificate_path', 'certificate_password_encrypted', 'token_expires_at',
        'status', 'last_api_response', 'last_sync_at', 'notes',
    ];

    protected $casts = [
        'last_api_response' => 'array',
        'token_expires_at' => 'datetime',
        'last_sync_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
