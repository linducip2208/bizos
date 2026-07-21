<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'quotation_number',
        'client_id',
        'contact_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'valid_until',
        'status',
        'notes',
        'terms',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'valid_until' => 'date',
        'approved_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function contact()
    {
        return $this->belongsTo(ClientContact::class, 'contact_id');
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function salesOrder()
    {
        return $this->hasOne(SalesOrder::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Quotation $model) {
            if (!$model->quotation_number) {
                $prefix = 'QTO-' . now()->format('ym');
                $last = static::where('quotation_number', 'like', $prefix . '%')
                    ->orderByDesc('quotation_number')
                    ->first();
                $num = $last ? (int) substr($last->quotation_number, 8) + 1 : 1;
                $model->quotation_number = $prefix . str_pad($num, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
