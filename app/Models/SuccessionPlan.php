<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuccessionPlan extends Model
{
    protected $fillable = [
        'company_id', 'position_id', 'current_incumbent_id',
        'successor_employee_id', 'readiness', 'risk_level',
        'notes', 'development_plan', 'created_by',
    ];

    protected $casts = [
        'development_plan' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function currentIncumbent()
    {
        return $this->belongsTo(Employee::class, 'current_incumbent_id');
    }

    public function successor()
    {
        return $this->belongsTo(Employee::class, 'successor_employee_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
