<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'yearly_price',
        'max_users',
        'max_companies',
        'max_branches',
        'features',
        'module_access',
        'white_label',
        'tier',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'max_users' => 'integer',
        'max_companies' => 'integer',
        'max_branches' => 'integer',
        'features' => 'json',
        'module_access' => 'json',
        'white_label' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function scopeTier($query, string $tier)
    {
        return $query->where('tier', $tier);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFeatureList(): array
    {
        return $this->features ?? [];
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->getFeatureList());
    }

    public function getModuleAccess(): array
    {
        return $this->module_access ?? [];
    }

    public function hasModuleAccess(string $module): bool
    {
        return in_array($module, $this->getModuleAccess());
    }
}
