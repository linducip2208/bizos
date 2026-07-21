<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RfidTag extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'warehouse_id',
        'rfid_code',
        'epc',
        'product_id',
        'batch_number',
        'expiry_date',
        'status',
        'last_scanned_at',
        'last_scanned_by',
        'last_known_location',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'last_scanned_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function lastScannedBy()
    {
        return $this->belongsTo(User::class, 'last_scanned_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInStock($query)
    {
        return $query->where('status', 'in_stock');
    }
}
