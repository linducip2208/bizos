<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'widget_type',
        'title',
        'config',
        'position',
        'is_pinned',
        'sort_order',
    ];

    protected $casts = [
        'config' => 'array',
        'position' => 'array',
        'is_pinned' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
