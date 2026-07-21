<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id',
        'product_id',
        'dosage',
        'frequency',
        'duration_days',
        'quantity',
        'instructions',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'duration_days' => 'integer',
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
