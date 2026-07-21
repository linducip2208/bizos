<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    protected $fillable = [
        'budget_id',
        'coa_id',
        'description',
        'planned_amount',
        'actual_amount',
        'variance',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'planned_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'variance' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function coa()
    {
        return $this->belongsTo(Coa::class);
    }
}
