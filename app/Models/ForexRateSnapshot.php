<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForexRateSnapshot extends Model
{
    protected $fillable = [
        'company_id',
        'currency_id',
        'snapshot_date',
        'buy_rate',
        'sell_rate',
        'mid_rate',
        'source',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'buy_rate' => 'decimal:6',
        'sell_rate' => 'decimal:6',
        'mid_rate' => 'decimal:6',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeLatestForCurrency($query, int $currencyId)
    {
        return $query->where('currency_id', $currencyId)->orderByDesc('snapshot_date');
    }
}
