<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarketplaceReview extends Model
{
    protected $fillable = [
        'marketplace_app_id',
        'company_id',
        'user_id',
        'rating',
        'review',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    protected $table = 'marketplace_reviews';

    public function app()
    {
        return $this->belongsTo(MarketplaceApp::class, 'marketplace_app_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
