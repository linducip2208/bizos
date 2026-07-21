<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductBlockchainEvent extends Model
{
    protected $table = 'product_blockchain_events';

    protected $fillable = [
        'company_id', 'product_id', 'transaction_id', 'event_type',
        'event_data', 'location', 'actor_name', 'document_hash',
        'block_number', 'recorded_at',
    ];

    protected $casts = [
        'event_data' => 'json',
        'block_number' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function transaction()
    {
        return $this->belongsTo(BlockchainTransaction::class, 'transaction_id');
    }
}
