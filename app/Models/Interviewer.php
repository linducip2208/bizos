<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interviewer extends Model
{
    protected $fillable = [
        'interview_id',
        'employee_id',
        'role',
    ];

    protected $casts = [
        'role' => 'string',
    ];

    public function interview()
    {
        return $this->belongsTo(Interview::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function interviewResults()
    {
        return $this->hasMany(InterviewResult::class);
    }
}
