<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceInstall extends Model
{
    protected $fillable = [
        'marketplace_app_id',
        'company_id',
        'installed_version',
        'status',
        'subscription_start',
        'subscription_end',
        'last_checked_at',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
        'subscription_start' => 'date',
        'subscription_end' => 'date',
        'last_checked_at' => 'datetime',
    ];

    protected $table = 'marketplace_installs';

    public function app()
    {
        return $this->belongsTo(MarketplaceApp::class, 'marketplace_app_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function needsUpdate(): bool
    {
        return $this->app && version_compare($this->app->version, $this->installed_version, '>');
    }
}
