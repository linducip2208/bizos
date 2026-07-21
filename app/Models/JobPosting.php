<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    protected $fillable = [
        'company_id',
        'department_id',
        'position_id',
        'title',
        'description',
        'requirements',
        'responsibilities',
        'employee_type',
        'min_salary',
        'max_salary',
        'location',
        'is_remote',
        'quota',
        'status',
        'published_at',
        'closed_at',
    ];

    protected $casts = [
        'employee_type' => 'string',
        'min_salary' => 'decimal:2',
        'max_salary' => 'decimal:2',
        'is_remote' => 'boolean',
        'quota' => 'integer',
        'status' => 'string',
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
