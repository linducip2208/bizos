<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class SalesInvoice extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'invoice_number',
        'sales_order_id',
        'client_id',
        'due_date',
        'subtotal',
        'tax',
        'total',
        'paid_amount',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function salesReturns()
    {
        return $this->hasMany(SalesReturn::class);
    }

    protected static function booted(): void
    {
        static::creating(function (SalesInvoice $model) {
            if (!$model->invoice_number) {
                $prefix = 'INV-S-' . now()->format('ym');
                $last = static::where('invoice_number', 'like', $prefix . '%')
                    ->orderByDesc('invoice_number')
                    ->first();
                $num = $last ? (int) substr($last->invoice_number, 10) + 1 : 1;
                $model->invoice_number = $prefix . str_pad($num, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
