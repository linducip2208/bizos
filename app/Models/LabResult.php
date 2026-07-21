<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabResult extends Model
{
    protected $fillable = [
        'lab_order_id',
        'test_name',
        'result_value',
        'unit',
        'normal_range',
        'is_abnormal',
        'notes',
        'performed_by',
        'performed_at',
    ];

    protected $casts = [
        'is_abnormal' => 'boolean',
        'performed_at' => 'datetime',
    ];

    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class);
    }
}
