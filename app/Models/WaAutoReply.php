<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaAutoReply extends Model
{
    protected $fillable = [
        'company_id',
        'keyword',
        'match_type',
        'reply_text',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
