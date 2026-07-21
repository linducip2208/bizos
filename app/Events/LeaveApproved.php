<?php

namespace App\Events;

use App\Models\Leave;
use Illuminate\Foundation\Events\Dispatchable;

class LeaveApproved
{
    use Dispatchable;

    public function __construct(public Leave $leave) {}
}
