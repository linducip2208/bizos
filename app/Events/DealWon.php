<?php

namespace App\Events;

use App\Models\Deal;
use Illuminate\Foundation\Events\Dispatchable;

class DealWon
{
    use Dispatchable;

    public function __construct(public Deal $deal) {}
}
