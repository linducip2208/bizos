<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGateway extends Model
{
    protected $fillable = [
        'company_id', 'name', 'provider', 'api_key_encrypted',
        'api_secret_encrypted', 'sender_id', 'base_url',
        'is_active', 'extra_config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'extra_config' => 'array',
    ];

    protected $hidden = ['api_key_encrypted', 'api_secret_encrypted'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function logs()
    {
        return $this->hasMany(SmsLog::class, 'gateway_id');
    }
}
