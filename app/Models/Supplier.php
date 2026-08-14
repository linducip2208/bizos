<?php

namespace App\Models;

use App\Services\VendorScorecardService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'tax_number',
        'payment_terms',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function goodsReceipts()
    {
        return $this->hasManyThrough(GoodsReceipt::class, PurchaseOrder::class, 'supplier_id', 'purchase_order_id', 'id', 'id');
    }

    /**
     * Hasil inspeksi kualitas (GoodsReceiptInspection) milik vendor ini,
     * dirangkai melalui goods_receipts -> purchase_orders.
     */
    public function qualityChecks()
    {
        return GoodsReceiptInspection::whereHas('goodsReceipt.purchaseOrder', function ($query) {
            $query->where('supplier_id', $this->getKey());
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getScorecardGrade(): string
    {
        return app(VendorScorecardService::class)->calculateScorecard($this)['grade'];
    }

    public function rfqSuppliers()
    {
        return $this->hasMany(RfqSupplier::class);
    }

    public function rfqs()
    {
        return $this->belongsToMany(Rfq::class, 'rfq_suppliers')
            ->withPivot('invited_at', 'responded_at', 'status')
            ->withTimestamps();
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function scopeHasDuplicates(Builder $query): Builder
    {
        return $query->whereIn('id', function ($sub) {
            $sub->selectRaw('s1.id')
                ->from('suppliers as s1')
                ->join('suppliers as s2', function ($join) {
                    $join->on('s1.company_id', '=', 's2.company_id')
                        ->whereColumn('s1.id', '<', 's2.id')
                        ->where(function ($q) {
                            $q->whereRaw('SOUNDEX(s1.name) = SOUNDEX(s2.name)')
                                ->orWhere(function ($q2) {
                                    $q2->whereNotNull('s1.tax_number')
                                        ->whereNotNull('s2.tax_number')
                                        ->whereColumn('s1.tax_number', '=', 's2.tax_number');
                                })
                                ->orWhere(function ($q2) {
                                    $q2->whereNotNull('s1.email')
                                        ->whereNotNull('s2.email')
                                        ->whereColumn('s1.email', '=', 's2.email');
                                });
                        });
                });
        });
    }
}
