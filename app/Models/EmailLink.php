<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLink extends Model
{
    protected $fillable = [
        'message_id',
        'linkable_type',
        'linkable_id',
        'link_reason',
        'linked_by',
    ];

    protected $casts = [
        'linkable_id' => 'integer',
    ];

    public function linkable()
    {
        return $this->morphTo();
    }

    public function linkedBy()
    {
        return $this->belongsTo(User::class, 'linked_by');
    }
}
