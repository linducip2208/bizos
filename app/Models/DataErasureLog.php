<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataErasureLog extends Model
{
    protected $fillable = [
        'company_id',
        'subject_type',
        'subject_id',
        'requested_by_name',
        'requested_by_email',
        'request_channel',
        'requested_at',
        'action',
        'reason',
        'erased_fields',
        'retained_fields',
        'retention_justification',
        'processed_at',
        'processed_by',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'erased_fields' => 'array',
        'retained_fields' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function subject()
    {
        return $this->morphTo(null, 'subject_type', 'subject_id');
    }
}
