<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRateLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'currency_id',
        'rate_date',
        'rate',
        'created_at',
    ];

    protected $casts = [
        'rate_date' => 'date',
        'rate' => 'decimal:6',
        'created_at' => 'datetime',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
