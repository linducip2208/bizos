<?php

namespace App\Events;

use App\Models\Attendance;
use Illuminate\Foundation\Events\Dispatchable;

class AttendanceClockedIn
{
    use Dispatchable;

    public function __construct(public Attendance $attendance) {}
}
