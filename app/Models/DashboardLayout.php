<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardLayout extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'layout_config',
        'is_default',
    ];

    protected $casts = [
        'layout_config' => 'array',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
