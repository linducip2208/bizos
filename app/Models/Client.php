<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'customer_group_id',
        'notes',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function getEffectiveDiscountPercent(): float
    {
        return (float) ($this->customerGroup?->discount_percent ?? 0);
    }

    public function getEffectivePriceListId(): ?int
    {
        return $this->customerGroup?->price_list_id;
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

    public function scopeHasDuplicates(Builder $query): Builder
    {
        return $query->whereIn('id', function ($sub) {
            $sub->selectRaw('c1.id')
                ->from('clients as c1')
                ->join('clients as c2', function ($join) {
                    $join->on('c1.company_id', '=', 'c2.company_id')
                        ->whereColumn('c1.id', '<', 'c2.id')
                        ->where(function ($q) {
                            $q->whereRaw('SOUNDEX(c1.name) = SOUNDEX(c2.name)')
                                ->orWhere(function ($q2) {
                                    $q2->whereNotNull('c1.email')
                                        ->whereNotNull('c2.email')
                                        ->whereColumn('c1.email', '=', 'c2.email');
                                })
                                ->orWhere(function ($q2) {
                                    $q2->whereNotNull('c1.phone')
                                        ->whereNotNull('c2.phone')
                                        ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(c1.phone, ' ', ''), '-', ''), '(', ''), ')', '') = REPLACE(REPLACE(REPLACE(REPLACE(c2.phone, ' ', ''), '-', ''), '(', ''), ')', '')");
                                });
                        });
                });
        });
    }
}
