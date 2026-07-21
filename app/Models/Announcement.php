<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'content',
        'priority',
        'target_type',
        'target_department_ids',
        'target_position_ids',
        'expires_at',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'priority' => 'string',
        'target_type' => 'string',
        'target_department_ids' => 'array',
        'target_position_ids' => 'array',
        'expires_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function publishedBy()
    {
        return $this->belongsTo(Employee::class, 'published_by');
    }

    public function announcementReads()
    {
        return $this->hasMany(AnnouncementRead::class);
    }
}
