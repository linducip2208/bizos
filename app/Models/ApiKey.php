<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'key',
        'permissions',
        'rate_limit',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'permissions' => 'array',
        'rate_limit' => 'integer',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function hasPermission(string $permission): bool
    {
        if (empty($this->permissions)) {
            return false;
        }

        foreach ($this->permissions as $p) {
            $parts = explode('.', $p);
            $checkParts = explode('.', $permission);

            if (in_array('*', $parts)) {
                return true;
            }

            if ($this->matchPermission($parts, $checkParts)) {
                return true;
            }
        }

        return false;
    }

    protected function matchPermission(array $defined, array $checking): bool
    {
        if (count($defined) !== 2 || count($checking) !== 2) {
            return false;
        }

        $resourceMatch = $defined[0] === '*' || $defined[0] === $checking[0];
        $actionMatch = $defined[1] === '*' || $defined[1] === $checking[1];

        return $resourceMatch && $actionMatch;
    }
}
