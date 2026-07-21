<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'is_enabled',
        'enabled_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'enabled_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public static function isEnabled(string $name, int $companyId): bool
    {
        return static::where('company_id', $companyId)
            ->where('name', $name)
            ->where('is_enabled', true)
            ->exists();
    }
}
