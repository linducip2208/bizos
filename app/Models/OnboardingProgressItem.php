<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingProgressItem extends Model
{
    protected $fillable = [
        'progress_id',
        'checklist_item_id',
        'assigned_to',
        'completed_at',
        'notes',
        'status',
    ];

    protected $casts = [
        'completed_at' => 'date',
        'status' => 'string',
    ];

    public function progress()
    {
        return $this->belongsTo(OnboardingProgress::class, 'progress_id');
    }

    public function checklistItem()
    {
        return $this->belongsTo(OnboardingChecklistItem::class, 'checklist_item_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }
}
