<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WikiPage extends Model
{
    protected $fillable = [
        'company_id', 'category_id', 'title', 'slug', 'content',
        'author_id', 'status', 'view_count', 'published_at',
        'last_edited_by', 'last_edited_at',
    ];

    protected $casts = [
        'view_count' => 'integer',
        'published_at' => 'datetime',
        'last_edited_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(WikiCategory::class, 'category_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function lastEditor()
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopePopular($query, int $limit = 10)
    {
        return $query->published()->orderByDesc('view_count')->limit($limit);
    }
}
