<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GamificationPoint extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'branch_id',
        'department_id',
        'action_id',
        'action_key',
        'points',
        'context',
        'period_date',
    ];

    protected $casts = [
        'points' => 'integer',
        'context' => 'array',
        'period_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function action()
    {
        return $this->belongsTo(GamificationAction::class, 'action_id');
    }
}
