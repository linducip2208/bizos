<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sprint extends Model
{
    protected $fillable = [
        'project_id', 'name', 'goal', 'start_date', 'end_date', 'status', 'sort_order',
    ];

    protected $casts = [
        'start_date' => 'date', 'end_date' => 'date', 'sort_order' => 'integer',
    ];

    public function project() { return $this->belongsTo(Project::class); }
    public function sprintTasks() { return $this->hasMany(SprintTask::class); }
}
