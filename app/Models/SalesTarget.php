<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'employee_id',
        'year',
        'month',
        'target_amount',
        'achieved_amount',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'target_amount' => 'decimal:2',
        'achieved_amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function getAchievementPercentAttribute(): float
    {
        if ($this->target_amount <= 0) {
            return 0;
        }
        return round(($this->achieved_amount / $this->target_amount) * 100, 2);
    }
}
