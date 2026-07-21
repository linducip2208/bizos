<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderChecklistItem extends Model
{
    protected $table = 'work_order_checklist_items';

    protected $fillable = [
        'work_order_id',
        'checklist_item_id',
        'is_checked',
        'checked_at',
        'notes',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function checklistItem()
    {
        return $this->belongsTo(ServiceChecklistItem::class, 'checklist_item_id');
    }
}
