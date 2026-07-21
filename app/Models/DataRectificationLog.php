<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataRectificationLog extends Model
{
    protected $fillable = [
        'company_id',
        'entity_type',
        'entity_id',
        'requested_by_name',
        'requested_by_email',
        'request_channel',
        'corrections',
        'reason',
        'requested_at',
        'processed_at',
        'processed_by',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'corrections' => 'array',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
