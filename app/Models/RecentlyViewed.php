<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecentlyViewed extends Model
{
    protected $table = 'recently_viewed';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'resource_type',
        'resource_label',
        'resource_url',
        'resource_icon',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
