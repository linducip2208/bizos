<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAssignee extends Model
{
    protected $table = 'task_assignees';

    protected $fillable = [
        'task_id',
        'employee_id',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
