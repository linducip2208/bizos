<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'email',
        'name',
        'provider',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'password_encrypted',
        'signature',
        'is_active',
        'auto_fetch',
        'fetch_interval_minutes',
        'last_fetched_at',
        'last_error',
    ];

    protected $casts = [
        'imap_port' => 'integer',
        'smtp_port' => 'integer',
        'is_active' => 'boolean',
        'auto_fetch' => 'boolean',
        'fetch_interval_minutes' => 'integer',
        'last_fetched_at' => 'datetime',
    ];

    protected $hidden = ['password_encrypted'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(EmailMessage::class);
    }
}
