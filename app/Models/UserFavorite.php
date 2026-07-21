<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavorite extends Model
{
    protected $table = 'user_favorites';

    protected $fillable = [
        'user_id',
        'resource_type',
        'resource_label',
        'resource_url',
        'resource_icon',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
