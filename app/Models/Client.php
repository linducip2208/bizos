<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'client_code',
        'name',
        'client_type',
        'industry',
        'tax_id',
        'website',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'logo',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function clientContacts()
    {
        return $this->hasMany(ClientContact::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'converted_client_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function segments()
    {
        return $this->belongsToMany(ClientSegment::class, 'client_segment_members', 'client_id', 'segment_id')
            ->withPivot('added_at')
            ->withTimestamps();
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function salesOrders()
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_client_id');
    }
}
