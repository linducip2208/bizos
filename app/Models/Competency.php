<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Competency extends Model
{
    protected $fillable = ['company_id', 'name', 'category', 'description', 'proficiency_levels'];

    protected $casts = [
        'proficiency_levels' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function positionCompetencies()
    {
        return $this->hasMany(PositionCompetency::class);
    }

    public function employeeCompetencies()
    {
        return $this->hasMany(EmployeeCompetency::class);
    }
}
