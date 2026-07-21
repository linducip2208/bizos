<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamTarget extends Model
{
    protected $fillable = ['company_id', 'department_id', 'target_amount', 'bonus_amount', 'period_start', 'period_end', 'is_active'];
    protected $casts = ['target_amount' => 'decimal:2', 'bonus_amount' => 'decimal:2', 'period_start' => 'date', 'period_end' => 'date', 'is_active' => 'boolean'];

    public function company() { return $this->belongsTo(Company::class); }
    public function department() { return $this->belongsTo(Department::class); }
}
