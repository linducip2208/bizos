<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'calendar_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'is_all_day',
        'location',
        'color',
        'external_id',
        'external_provider',
        'last_synced_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_all_day' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function calendar()
    {
        return $this->belongsTo(Calendar::class);
    }
}
