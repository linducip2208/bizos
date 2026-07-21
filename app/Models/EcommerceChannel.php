<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EcommerceChannel extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'channel_name',
        'api_key_encrypted',
        'api_secret_encrypted',
        'shop_id',
        'webhook_secret',
        'is_active',
        'last_sync_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_sync_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function orders()
    {
        return $this->hasMany(EcommerceOrder::class, 'channel_id');
    }

    public function inventoryLogs()
    {
        return $this->hasMany(EcommerceInventoryLog::class, 'channel_id');
    }

    public function setApiKeyAttribute($value)
    {
        if ($value) {
            $this->attributes['api_key_encrypted'] = Crypt::encryptString($value);
        }
    }

    public function getApiKeyAttribute()
    {
        if (!empty($this->attributes['api_key_encrypted'])) {
            try {
                return Crypt::decryptString($this->attributes['api_key_encrypted']);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function setApiSecretAttribute($value)
    {
        if ($value) {
            $this->attributes['api_secret_encrypted'] = Crypt::encryptString($value);
        }
    }

    public function getApiSecretAttribute()
    {
        if (!empty($this->attributes['api_secret_encrypted'])) {
            try {
                return Crypt::decryptString($this->attributes['api_secret_encrypted']);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }
}
