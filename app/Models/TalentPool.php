<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TalentPool extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function candidates()
    {
        return $this->hasMany(TalentPoolCandidate::class);
    }
}
