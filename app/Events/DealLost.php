<?php

namespace App\Events;

use App\Models\Deal;
use Illuminate\Foundation\Events\Dispatchable;

class DealLost
{
    use Dispatchable;

    public function __construct(public Deal $deal) {}
}
