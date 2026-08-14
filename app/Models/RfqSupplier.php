<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqSupplier extends Model
{
    protected $fillable = [
        'rfq_id',
        'supplier_id',
        'invited_at',
        'responded_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'invited_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
