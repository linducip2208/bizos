<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeyResultCheckIn extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key_result_id',
        'value',
        'notes',
        'checked_by',
        'confidence_level',
        'is_on_track',
        'created_at',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'confidence_level' => 'integer',
        'is_on_track' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $checkIn) {
            if (!$checkIn->created_at) {
                $checkIn->created_at = now();
            }
        });

        static::created(function (self $checkIn) {
            $checkIn->keyResult?->syncFromLatestCheckIn();
            $checkIn->keyResult?->objective?->recalculateProgress();
        });
    }

    public function keyResult(): BelongsTo
    {
        return $this->belongsTo(KeyResult::class, 'key_result_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
