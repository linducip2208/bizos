<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkCalendar extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'year',
        'config',
        'is_default',
    ];

    protected $casts = [
        'year' => 'integer',
        'config' => 'json',
        'is_default' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getWorkingDays(): array
    {
        return $this->config['working_days'] ?? ['senin', 'selasa', 'rabu', 'kamis', 'jumat'];
    }

    public function getWorkingHours(): array
    {
        return $this->config['working_hours'] ?? ['start' => '08:00', 'end' => '17:00'];
    }

    public static function booted(): void
    {
        static::saving(function (WorkCalendar $calendar) {
            if ($calendar->is_default) {
                static::where('company_id', $calendar->company_id)
                    ->where('id', '!=', $calendar->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
