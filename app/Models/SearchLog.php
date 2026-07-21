<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'query',
        'filters',
        'results_count',
        'clicked_result_type',
        'clicked_result_id',
        'clicked_result_model',
        'search_time_ms',
    ];

    protected $casts = [
        'filters' => 'array',
        'results_count' => 'integer',
        'search_time_ms' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
