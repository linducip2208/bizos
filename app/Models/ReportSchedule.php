<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSchedule extends Model
{
    protected $fillable = [
        'report_template_id',
        'name',
        'recipients',
        'frequency',
        'time_of_day',
        'day_of_week',
        'day_of_month',
        'format',
        'is_active',
        'last_sent_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
        'day_of_week' => 'integer',
        'day_of_month' => 'integer',
    ];

    public function reportTemplate()
    {
        return $this->belongsTo(ReportTemplate::class);
    }

    public function isDue(): bool
    {
        $now = now();
        $timeOfDay = $this->time_of_day;

        return match ($this->frequency) {
            'daily' => true,
            'weekly' => $now->dayOfWeek === $this->day_of_week,
            'monthly' => $now->day === $this->day_of_month,
            default => false,
        };
    }
}
