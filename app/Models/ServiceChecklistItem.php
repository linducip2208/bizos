<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceChecklistItem extends Model
{
    protected $table = 'service_checklist_items';

    protected $fillable = [
        'checklist_id',
        'description',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function checklist()
    {
        return $this->belongsTo(ServiceChecklist::class, 'checklist_id');
    }

    public function workOrderChecklistItems()
    {
        return $this->hasMany(WorkOrderChecklistItem::class);
    }
}
