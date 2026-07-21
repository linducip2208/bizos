<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OkrKeyResult extends Model
{
    protected $fillable = [
        'okr_id',
        'description',
        'target_value',
        'current_value',
        'unit',
        'weight',
        'type',
        'status',
    ];

    protected $casts = [
        'target_value' => 'decimal:4',
        'current_value' => 'decimal:4',
        'weight' => 'decimal:2',
        'type' => 'string',
        'status' => 'string',
    ];

    public function okr()
    {
        return $this->belongsTo(Okr::class);
    }
}
