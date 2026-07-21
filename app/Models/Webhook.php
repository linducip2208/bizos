<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'event',
        'url',
        'secret',
        'headers',
        'is_active',
        'retry_count',
        'max_retries',
        'created_by',
    ];

    protected $casts = [
        'headers' => 'array',
        'is_active' => 'boolean',
        'retry_count' => 'integer',
        'max_retries' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class)->orderBy('created_at', 'desc');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function successfulDeliveries()
    {
        return $this->hasMany(WebhookDelivery::class)->where('status', 'success');
    }

    public function failedDeliveries()
    {
        return $this->hasMany(WebhookDelivery::class)->where('status', 'failed');
    }
}
