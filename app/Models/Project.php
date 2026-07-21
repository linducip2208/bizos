<?php

namespace App\Models;

use App\Concerns\HasBranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasBranchScope, SoftDeletes;

    protected $fillable = [
        'company_id',
        'department_id',
        'client_id',
        'deal_id',
        'manager_id',
        'code',
        'name',
        'description',
        'start_date',
        'end_date',
        'budget',
        'actual_cost',
        'status',
        'priority',
        'progress_percent',
        'color',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'progress_percent' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    public function projectPhases()
    {
        return $this->hasMany(ProjectPhase::class);
    }

    public function projectMembers()
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
}
