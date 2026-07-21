<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientSegment extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'color',
        'criteria_json',
    ];

    protected $casts = [
        'criteria_json' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_segment_members', 'segment_id', 'client_id')
            ->withPivot('added_at')
            ->withTimestamps();
    }

    public function waBlastCampaigns()
    {
        return $this->hasMany(WaBlastCampaign::class, 'target_segment_id');
    }
}
