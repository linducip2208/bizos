<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainTransaction extends Model
{
    protected $table = 'blockchain_transactions';

    protected $fillable = [
        'block_id', 'transaction_hash', 'type', 'reference_type', 'reference_id',
        'document_hash', 'file_name', 'metadata', 'timestamped_at',
    ];

    protected $casts = [
        'metadata' => 'json',
        'timestamped_at' => 'datetime',
    ];

    public function block()
    {
        return $this->belongsTo(BlockchainBlock::class, 'block_id');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    public function productEvents()
    {
        return $this->hasMany(ProductBlockchainEvent::class, 'transaction_id');
    }
}
