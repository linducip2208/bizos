<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffboardingChecklistItem extends Model
{
    protected $fillable = [
        'checklist_id',
        'name',
        'description',
        'assigned_role',
        'sort_order',
        'is_required',
    ];

    protected $casts = [
        'assigned_role' => 'string',
        'sort_order' => 'integer',
        'is_required' => 'boolean',
    ];

    public function checklist()
    {
        return $this->belongsTo(OffboardingChecklist::class, 'checklist_id');
    }

    public function progressItems()
    {
        return $this->hasMany(OffboardingProgressItem::class, 'checklist_item_id');
    }
}
