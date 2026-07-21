<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimesheetEntry extends Model
{
    protected $fillable = [
        'timesheet_id',
        'task_id',
        'start_time',
        'end_time',
        'hours',
        'description',
        'is_billable',
        'invoice_id',
        'is_billed',
    ];

    protected $casts = [
        'hours' => 'decimal:2',
        'is_billable' => 'boolean',
        'is_billed' => 'boolean',
    ];

    public function timesheet()
    {
        return $this->belongsTo(Timesheet::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
