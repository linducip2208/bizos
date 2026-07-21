<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonalDashboardWidget extends Model
{
    protected $fillable = [
        'personal_dashboard_id',
        'widget_type',
        'config',
        'position',
    ];

    protected $casts = [
        'config' => 'json',
        'position' => 'json',
    ];

    public function dashboard()
    {
        return $this->belongsTo(PersonalDashboard::class);
    }
}
