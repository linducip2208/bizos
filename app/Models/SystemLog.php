<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'company_id',
        'level',
        'channel',
        'message',
        'context',
    ];

    public $timestamps = false;

    protected $casts = [
        'context' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    public function scopeError($query)
    {
        return $query->where('level', 'error');
    }

    public function scopeWarning($query)
    {
        return $query->where('level', 'warning');
    }
}
