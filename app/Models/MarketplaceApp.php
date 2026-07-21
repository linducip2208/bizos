<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceApp extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'developer',
        'version',
        'price_type',
        'price',
        'category',
        'icon',
        'screenshots',
        'features',
        'requirements',
        'package_path',
        'migration_class',
        'seeder_class',
        'permissions_required',
        'is_published',
        'is_featured',
        'total_installs',
        'rating',
    ];

    protected $casts = [
        'screenshots' => 'array',
        'features' => 'array',
        'requirements' => 'array',
        'permissions_required' => 'array',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'rating' => 'decimal:2',
        'total_installs' => 'integer',
    ];

    public function installs()
    {
        return $this->hasMany(MarketplaceInstall::class);
    }

    public function reviews()
    {
        return $this->hasMany(MarketplaceReview::class);
    }

    public function priceLabel(): string
    {
        return match ($this->price_type) {
            'free' => 'Gratis',
            'paid' => 'Rp ' . number_format($this->price, 0, ',', '.'),
            'monthly' => 'Rp ' . number_format($this->price, 0, ',', '.') . ' / bulan',
            default => 'Gratis',
        };
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_published', true);
    }
}
