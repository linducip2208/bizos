<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCompetency extends Model
{
    protected $fillable = [
        'employee_id', 'competency_id', 'current_level',
        'assessed_by', 'assessed_at', 'notes',
    ];

    protected $casts = [
        'current_level' => 'integer',
        'assessed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function competency()
    {
        return $this->belongsTo(Competency::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
