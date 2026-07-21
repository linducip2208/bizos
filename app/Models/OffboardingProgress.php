<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffboardingProgress extends Model
{
    protected $fillable = [
        'employee_id',
        'checklist_id',
        'resignation_date',
        'last_working_date',
        'final_settlement_amount',
        'clearance_status',
        'exit_interview_notes',
    ];

    protected $casts = [
        'resignation_date' => 'date',
        'last_working_date' => 'date',
        'final_settlement_amount' => 'decimal:2',
        'clearance_status' => 'string',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function checklist()
    {
        return $this->belongsTo(OffboardingChecklist::class, 'checklist_id');
    }

    public function items()
    {
        return $this->hasMany(OffboardingProgressItem::class, 'progress_id');
    }
}
