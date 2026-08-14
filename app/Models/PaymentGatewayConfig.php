<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PaymentGatewayConfig extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'gateway_type',
        'api_key',
        'api_secret',
        'server_key',
        'client_key',
        'api_key_encrypted',
        'api_secret_encrypted',
        'server_key_encrypted',
        'client_key_encrypted',
        'base_url',
        'extra_config',
        'is_active',
    ];

    protected $casts = [
        'extra_config' => 'array',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_key_encrypted',
        'api_secret_encrypted',
        'server_key_encrypted',
        'client_key_encrypted',
    ];

    protected const ENCRYPTED_FIELDS = [
        'api_key' => 'api_key_encrypted',
        'api_secret' => 'api_secret_encrypted',
        'server_key' => 'server_key_encrypted',
        'client_key' => 'client_key_encrypted',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class, 'gateway_config_id');
    }

    /**
     * Ambil kunci yang sudah didekripsi dari kolom *_encrypted.
     * Jika nilai bukan ciphertext (mis. diisi manual tanpa enkripsi), kembalikan apa adanya.
     */
    public function getDecryptedKey(string $field): ?string
    {
        $column = self::ENCRYPTED_FIELDS[$field] ?? $field;
        $encrypted = $this->getAttribute($column);

        if (!$encrypted) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable $e) {
            return $encrypted;
        }
    }

    public function setApiKeyAttribute($value): void
    {
        $this->attributes['api_key_encrypted'] = $value ? Crypt::encryptString($value) : '';
    }

    public function getApiKeyAttribute(): ?string
    {
        return $this->getDecryptedKey('api_key');
    }

    public function setApiSecretAttribute($value): void
    {
        $this->attributes['api_secret_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getApiSecretAttribute(): ?string
    {
        return $this->getDecryptedKey('api_secret');
    }

    public function setServerKeyAttribute($value): void
    {
        $this->attributes['server_key_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getServerKeyAttribute(): ?string
    {
        return $this->getDecryptedKey('server_key');
    }

    public function setClientKeyAttribute($value): void
    {
        $this->attributes['client_key_encrypted'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getClientKeyAttribute(): ?string
    {
        return $this->getDecryptedKey('client_key');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
