<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'status',
        'published_at',
        'form_id',
        'created_by',
    ];

    protected $casts = [
        'content' => 'array',
        'published_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
