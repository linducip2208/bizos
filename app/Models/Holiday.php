<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'date',
        'type',
        'is_recurring',
        'year',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'year' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where(function ($q) use ($year) {
            $q->where('year', $year)->orWhere('is_recurring', true);
        });
    }

    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
