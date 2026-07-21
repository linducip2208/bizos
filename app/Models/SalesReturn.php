<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'return_number',
        'sales_invoice_id',
        'client_id',
        'reason',
        'status',
        'total',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    protected static function booted(): void
    {
        static::creating(function (SalesReturn $model) {
            if (!$model->return_number) {
                $prefix = 'RET-' . now()->format('ym');
                $last = static::where('return_number', 'like', $prefix . '%')
                    ->orderByDesc('return_number')
                    ->first();
                $num = $last ? (int) substr($last->return_number, 8) + 1 : 1;
                $model->return_number = $prefix . str_pad($num, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
