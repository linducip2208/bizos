<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeBoardPost extends Model
{
    protected $fillable = [
        'company_id', 'title', 'content', 'category', 'priority',
        'posted_by', 'expires_at', 'is_pinned', 'view_count',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_pinned' => 'boolean',
        'view_count' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function reads()
    {
        return $this->hasMany(NoticeBoardRead::class, 'post_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
}
