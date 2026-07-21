<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WikiCategory extends Model
{
    protected $fillable = ['company_id', 'name', 'slug', 'parent_id', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function parent()
    {
        return $this->belongsTo(WikiCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(WikiCategory::class, 'parent_id')->orderBy('sort_order');
    }

    public function pages()
    {
        return $this->hasMany(WikiPage::class, 'category_id');
    }
}
