<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceReport extends Model
{
    protected $fillable = [
        'work_order_id',
        'technician_id',
        'report_date',
        'findings',
        'work_performed',
        'recommendations',
        'customer_signature',
        'customer_feedback',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function technician()
    {
        return $this->belongsTo(Employee::class, 'technician_id');
    }
}
