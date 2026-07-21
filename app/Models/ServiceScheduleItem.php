<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceScheduleItem extends Model
{
    protected $fillable = [
        'service_schedule_id',
        'work_order_id',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function serviceSchedule()
    {
        return $this->belongsTo(ServiceSchedule::class);
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
