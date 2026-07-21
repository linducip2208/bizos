<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealCommission extends Model
{
    protected $fillable = ['deal_id', 'employee_id', 'commission_amount', 'rate_percent', 'status', 'split_percent', 'paid_at'];
    protected $casts = ['commission_amount' => 'decimal:2', 'rate_percent' => 'decimal:2', 'split_percent' => 'decimal:2', 'paid_at' => 'datetime'];

    public function deal() { return $this->belongsTo(Deal::class); }
    public function employee() { return $this->belongsTo(Employee::class); }
}
