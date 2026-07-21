<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnboardingChecklistItem extends Model
{
    protected $fillable = [
        'checklist_id',
        'name',
        'description',
        'assigned_role',
        'sort_order',
        'is_required',
        'days_before_join',
    ];

    protected $casts = [
        'assigned_role' => 'string',
        'sort_order' => 'integer',
        'is_required' => 'boolean',
        'days_before_join' => 'integer',
    ];

    public function checklist()
    {
        return $this->belongsTo(OnboardingChecklist::class, 'checklist_id');
    }

    public function progressItems()
    {
        return $this->hasMany(OnboardingProgressItem::class, 'checklist_item_id');
    }
}
