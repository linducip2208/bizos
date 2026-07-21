<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IsoRisk extends Model
{
    protected $fillable = [
        'company_id',
        'asset_name',
        'asset_type',
        'asset_description',
        'threat',
        'vulnerability',
        'likelihood',
        'impact',
        'risk_level',
        'risk_score',
        'existing_controls',
        'treatment',
        'treatment_plan',
        'applied_controls',
        'iso_control_ref',
        'status',
        'owner_id',
        'review_due',
    ];

    protected $casts = [
        'review_due' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
