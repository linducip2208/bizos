<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    protected $fillable = [
        'job_posting_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'photo',
        'resume_path',
        'portfolio_url',
        'linkedin_url',
        'source',
        'expected_salary',
        'available_date',
        'pipeline_stage',
        'notes',
        'rejection_reason',
        'hired_employee_id',
    ];

    protected $casts = [
        'expected_salary' => 'decimal:2',
        'available_date' => 'date',
        'pipeline_stage' => 'string',
        'hired_employee_id' => 'integer',
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class);
    }

    public function interviews()
    {
        return $this->hasMany(Interview::class);
    }

    public function hiredEmployee()
    {
        return $this->belongsTo(Employee::class, 'hired_employee_id');
    }
}
