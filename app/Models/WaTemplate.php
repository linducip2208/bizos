<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'content',
        'category',
        'language',
        'status',
    ];

    protected $casts = [
        'language' => 'string',
        'status' => 'string',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function waBlastCampaigns()
    {
        return $this->hasMany(WaBlastCampaign::class, 'template_id');
    }
}
