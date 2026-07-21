<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'so_number',
        'client_id',
        'quotation_id',
        'subtotal',
        'tax',
        'discount',
        'shipping_cost',
        'total',
        'order_date',
        'expected_delivery',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'order_date' => 'date',
        'expected_delivery' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items()
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function salesInvoice()
    {
        return $this->hasOne(SalesInvoice::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    protected static function booted(): void
    {
        static::creating(function (SalesOrder $model) {
            if (!$model->so_number) {
                $prefix = 'SO-' . now()->format('ym');
                $last = static::where('so_number', 'like', $prefix . '%')
                    ->orderByDesc('so_number')
                    ->first();
                $num = $last ? (int) substr($last->so_number, 7) + 1 : 1;
                $model->so_number = $prefix . str_pad($num, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
