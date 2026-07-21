<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsentRecord extends Model
{
    protected $fillable = [
        'company_id',
        'person_type',
        'person_id',
        'purpose',
        'method',
        'consented_at',
        'expires_at',
        'withdrawn_at',
        'withdrawal_reason',
        'scope_description',
        'status',
        'metadata',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
        'expires_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->morphTo(null, 'person_type', 'person_id')->where('person_type', 'employee');
    }

    public function client()
    {
        return $this->morphTo(null, 'person_type', 'person_id')->where('person_type', 'client');
    }
}
