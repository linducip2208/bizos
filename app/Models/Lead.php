<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'company_id',
        'source_id',
        'assigned_to',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company_name',
        'industry',
        'address',
        'score',
        'status',
        'lost_reason',
        'converted_client_id',
        'notes',
        'next_follow_up',
    ];

    protected $casts = [
        'score' => 'integer',
        'next_follow_up' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function source()
    {
        return $this->belongsTo(LeadSource::class, 'source_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function convertedClient()
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }

    public function leadActivities()
    {
        return $this->hasMany(LeadActivity::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }
}
