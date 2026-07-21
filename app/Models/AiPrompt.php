<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPrompt extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'category',
        'content',
        'variables',
        'is_template',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_template' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTemplate($query)
    {
        return $query->where('is_template', true);
    }

    public function render(array $data = []): string
    {
        $content = $this->content;
        foreach ($data as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }
        return $content;
    }
}
