<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadScore extends Model
{
    protected $fillable = [
        'company_id',
        'lead_id',
        'score',
        'criteria',
        'calculated_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'criteria' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
