<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalDashboard extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'is_default',
        'layout',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'layout' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function widgets()
    {
        return $this->hasMany(PersonalDashboardWidget::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public static function booted(): void
    {
        static::saving(function (PersonalDashboard $dashboard) {
            if ($dashboard->is_default) {
                static::where('user_id', $dashboard->user_id)
                    ->where('id', '!=', $dashboard->id)
                    ->update(['is_default' => false]);
            }
        });
    }
}
