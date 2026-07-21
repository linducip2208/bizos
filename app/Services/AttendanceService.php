<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Shift;
use Carbon\Carbon;

class AttendanceService
{
    public function calculateLateMinutes(Attendance $attendance): int
    {
        if (! $attendance->clock_in) {
            return 0;
        }

        $shift = $attendance->shift;

        if (! $shift || ! $shift->start_time) {
            return 0;
        }

        $attendanceDate = Carbon::parse($attendance->date)->format('Y-m-d');
        $shiftStart = Carbon::parse($attendanceDate . ' ' . Carbon::parse($shift->start_time)->format('H:i:s'));
        $graceMinutes = (int) ($shift->grace_period_minutes ?? 0);
        $allowedUntil = $shiftStart->copy()->addMinutes($graceMinutes);
        $clockIn = Carbon::parse($attendance->clock_in);

        if ($clockIn->lte($allowedUntil)) {
            return 0;
        }

        return (int) abs($clockIn->diffInMinutes($shiftStart));
    }

    public function calculateEarlyDepartureMinutes(Attendance $attendance): int
    {
        if (! $attendance->clock_out) {
            return 0;
        }

        $shift = $attendance->shift;

        if (! $shift || ! $shift->end_time) {
            return 0;
        }

        $attendanceDate = Carbon::parse($attendance->date)->format('Y-m-d');
        $shiftEnd = Carbon::parse($attendanceDate . ' ' . Carbon::parse($shift->end_time)->format('H:i:s'));

        if ($shift->is_overnight) {
            $shiftEnd->addDay();
        }

        $clockOut = Carbon::parse($attendance->clock_out);

        if ($clockOut->gte($shiftEnd)) {
            return 0;
        }

        return (int) abs($shiftEnd->diffInMinutes($clockOut));
    }

    public function calculateOvertimeMinutes(Attendance $attendance): int
    {
        if (! $attendance->clock_out) {
            return 0;
        }

        $shift = $attendance->shift;

        if (! $shift || ! $shift->end_time) {
            return 0;
        }

        $attendanceDate = Carbon::parse($attendance->date)->format('Y-m-d');
        $shiftEnd = Carbon::parse($attendanceDate . ' ' . Carbon::parse($shift->end_time)->format('H:i:s'));

        if ($shift->is_overnight) {
            $shiftEnd->addDay();
        }

        $clockOut = Carbon::parse($attendance->clock_out);

        if ($clockOut->lte($shiftEnd)) {
            return 0;
        }

        return (int) abs($clockOut->diffInMinutes($shiftEnd));
    }
}
