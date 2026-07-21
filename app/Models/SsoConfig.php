<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SsoConfig extends Model
{
    protected $fillable = [
        'company_id',
        'provider',
        'metadata_url',
        'entity_id',
        'certificate',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
