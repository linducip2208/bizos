<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SprintTask extends Model
{
    protected $fillable = ['sprint_id', 'task_id', 'status', 'sort_order'];
    protected $casts = ['sort_order' => 'integer'];

    public function sprint() { return $this->belongsTo(Sprint::class); }
    public function task() { return $this->belongsTo(Task::class); }
}
