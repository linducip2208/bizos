<?php

namespace App\Models;

use App\Concerns\HasBranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasBranchScope, SoftDeletes;

    protected $fillable = [
        'project_id',
        'phase_id',
        'parent_id',
        'milestone_id',
        'title',
        'description',
        'status',
        'priority',
        'type',
        'estimated_hours',
        'actual_hours',
        'start_date',
        'due_date',
        'completed_at',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function phase()
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id');
    }

    public function parent()
    {
        return $this->belongsTo(Task::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Task::class, 'parent_id');
    }

    public function milestone()
    {
        return $this->belongsTo(Milestone::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function labels()
    {
        return $this->belongsToMany(TaskLabel::class, 'task_label_task', 'task_id', 'label_id')
            ->withTimestamps();
    }

    public function assignees()
    {
        return $this->belongsToMany(Employee::class, 'task_assignees', 'task_id', 'employee_id')
            ->withTimestamps();
    }

    public function dependencies()
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function dependentOn()
    {
        return $this->hasMany(TaskDependency::class, 'depends_on_task_id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function activities()
    {
        return $this->hasMany(TaskActivity::class);
    }

    public function timesheetEntries()
    {
        return $this->hasMany(TimesheetEntry::class);
    }
}
