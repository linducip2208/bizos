<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'description',
        'target_date',
        'completed_date',
        'status',
        'invoice_id',
        'sort_order',
    ];

    protected $casts = [
        'target_date' => 'date',
        'completed_date' => 'date',
        'sort_order' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
