<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffboardingProgressItem extends Model
{
    protected $fillable = [
        'progress_id',
        'checklist_item_id',
        'completed_by',
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
        return $this->belongsTo(OffboardingProgress::class, 'progress_id');
    }

    public function checklistItem()
    {
        return $this->belongsTo(OffboardingChecklistItem::class, 'checklist_item_id');
    }

    public function completedBy()
    {
        return $this->belongsTo(Employee::class, 'completed_by');
    }
}
