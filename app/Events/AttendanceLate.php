<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Foundation\Events\Dispatchable;

class AttendanceLate
{
    use Dispatchable;

    public function __construct(public Attendance $attendance) {}
}
