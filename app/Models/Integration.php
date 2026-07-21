<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'integration_type',
        'api_format',
        'base_url',
        'api_key_encrypted',
        'extra_config',
        'is_active',
    ];

    protected $casts = [
        'extra_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
