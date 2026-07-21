<?php

namespace App\Events;

use App\Models\StockBalance;
use Illuminate\Foundation\Events\Dispatchable;

class StockLow
{
    use Dispatchable;

    public function __construct(public StockBalance $stock) {}
}
