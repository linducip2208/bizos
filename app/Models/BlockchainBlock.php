<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainBlock extends Model
{
    protected $table = 'blockchain_ledger';

    protected $fillable = [
        'block_number', 'previous_hash', 'block_hash', 'data', 'nonce', 'mined_at',
    ];

    protected $casts = [
        'block_number' => 'integer',
        'data' => 'json',
        'nonce' => 'integer',
        'mined_at' => 'datetime',
    ];

    public function transactions()
    {
        return $this->hasMany(BlockchainTransaction::class, 'block_id');
    }
}
