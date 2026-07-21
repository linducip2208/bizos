<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceConfig;
use App\Models\AttendanceLog;
use App\Models\ShiftEmployee;
use App\Models\WifiAccessPoint;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function clockIn(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $today = Carbon::today();
        $now = Carbon::now();

        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today->toDateString())
            ->whereNotNull('clock_in')
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Anda sudah clock-in hari ini.'], 422);
        }

        $request->validate([
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'photo_base64' => ['nullable', 'string'],
            'wifi_bssid' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $config = AttendanceConfig::where('company_id', $user->company_id)->first();
        $workType = 'office';
        $lateMinutes = 0;

        if ($config) {
            if (in_array('gps', (array) $config->method) && $config->gps_latitude && $config->gps_longitude) {
                if ($request->filled('latitude') && $request->filled('longitude')) {
                    $distance = $this->haversineDistance(
                        $config->gps_latitude, $config->gps_longitude,
                        $request->latitude, $request->longitude
                    );
                    if ($distance > ($config->gps_radius_meters ?? 100)) {
                        $workType = 'wfh';
                    }
                }
            }

            if (in_array('wifi', (array) $config->method) && $request->filled('wifi_bssid')) {
                $wifiValid = WifiAccessPoint::where('company_id', $user->company_id)
                    ->where('bssid', $request->wifi_bssid)
                    ->where('is_active', true)
                    ->exists();
                if (!$wifiValid && $workType !== 'wfh') {
                    $workType = 'wfh';
                }
            }
        }

        $shift = ShiftEmployee::where('employee_id', $employee->id)
            ->where('effective_date', '<=', $today->toDateString())
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today->toDateString());
            })
            ->with('shift')
            ->first();

        if ($shift && $shift->shift && $shift->shift->start_time) {
            $shiftStart = Carbon::parse($shift->shift->start_time)->format('H:i:s');
            $expectedClockIn = Carbon::today()->setTimeFromTimeString($shiftStart);
            $graceDeadline = $expectedClockIn->copy()->addMinutes($shift->shift->grace_period_minutes ?? 0);
            if ($now->gt($graceDeadline)) {
                $lateMinutes = $expectedClockIn->diffInMinutes($now);
            }
        }

        $photoPath = null;
        if ($request->filled('photo_base64')) {
            $photoData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $request->photo_base64));
            $filename = 'attendance/' . $employee->id . '/' . $today->format('Y/m') . '/clockin_' . time() . '.jpg';
            Storage::disk('public')->put($filename, $photoData);
            $photoPath = $filename;
        }

        $attendance = Attendance::create([
            'employee_id' => $employee->id,
            'shift_id' => $shift?->shift_id,
            'date' => $today->toDateString(),
            'clock_in' => $now,
            'clock_in_lat' => $request->latitude,
            'clock_in_lng' => $request->longitude,
            'clock_in_photo' => $photoPath,
            'clock_in_wifi_bssid' => $request->wifi_bssid,
            'status' => $lateMinutes > 0 ? 'late' : 'present',
            'late_minutes' => $lateMinutes,
            'work_type' => $workType,
            'notes' => $request->notes,
        ]);

        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'type' => 'clock_in',
            'timestamp' => $now,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'wifi_bssid' => $request->wifi_bssid,
            'work_type' => $workType,
        ]);

        return response()->json([
            'message' => 'Clock-in berhasil.',
            'data' => [
                'clock_in_time' => $attendance->clock_in->format('H:i:s'),
                'status' => $attendance->status,
                'late_minutes' => $lateMinutes,
                'work_type' => $workType,
            ],
        ]);
    }

    public function clockOut(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $today = Carbon::today();
        $now = Carbon::now();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today->toDateString())
            ->whereNotNull('clock_in')
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Anda belum clock-in hari ini.'], 422);
        }

        if ($attendance->clock_out) {
            return response()->json(['message' => 'Anda sudah clock-out hari ini.'], 422);
        }

        $request->validate([
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'photo_base64' => ['nullable', 'string'],
            'wifi_bssid' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $overtimeMinutes = 0;
        $earlyDepartureMinutes = 0;

        if ($attendance->shift && $attendance->shift->end_time) {
            $shiftEnd = Carbon::today()->setTimeFromTimeString(Carbon::parse($attendance->shift->end_time)->format('H:i:s'));
            if ($now->lt($shiftEnd)) {
                $earlyDepartureMinutes = $now->diffInMinutes($shiftEnd);
            } elseif ($now->gt($shiftEnd)) {
                $overtimeMinutes = $shiftEnd->diffInMinutes($now);
            }
        } else {
            $standardHours = 9 * 60;
            $workedMinutes = Carbon::parse($attendance->clock_in)->diffInMinutes($now);
            if ($workedMinutes > $standardHours) {
                $overtimeMinutes = $workedMinutes - $standardHours;
            } elseif ($workedMinutes < $standardHours) {
                $earlyDepartureMinutes = $standardHours - $workedMinutes;
            }
        }

        $photoPath = null;
        if ($request->filled('photo_base64')) {
            $photoData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $request->photo_base64));
            $filename = 'attendance/' . $employee->id . '/' . $today->format('Y/m') . '/clockout_' . time() . '.jpg';
            Storage::disk('public')->put($filename, $photoData);
            $photoPath = $filename;
        }

        $attendance->update([
            'clock_out' => $now,
            'clock_out_lat' => $request->latitude,
            'clock_out_lng' => $request->longitude,
            'clock_out_photo' => $photoPath,
            'clock_out_wifi_bssid' => $request->wifi_bssid,
            'overtime_minutes' => $overtimeMinutes,
            'early_departure_minutes' => $earlyDepartureMinutes,
            'notes' => trim($attendance->notes . "\n" . $request->notes),
        ]);

        AttendanceLog::create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'type' => 'clock_out',
            'timestamp' => $now,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'wifi_bssid' => $request->wifi_bssid,
            'work_type' => $attendance->work_type,
        ]);

        $hours = floor(Carbon::parse($attendance->clock_in)->diffInMinutes($now) / 60);
        $minutes = Carbon::parse($attendance->clock_in)->diffInMinutes($now) % 60;

        return response()->json([
            'message' => 'Clock-out berhasil.',
            'data' => [
                'clock_out_time' => $attendance->clock_out->format('H:i:s'),
                'duration' => "{$hours}j {$minutes}m",
                'overtime_minutes' => $overtimeMinutes,
            ],
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $month = $request->get('month', now()->format('Y-m'));
        $startDate = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['shift'])
            ->orderBy('date', 'desc')
            ->paginate(31);

        $data = $attendances->through(function ($att) {
            return [
                'id' => $att->id,
                'date' => $att->date->format('Y-m-d'),
                'clock_in' => $att->clock_in?->format('H:i:s'),
                'clock_out' => $att->clock_out?->format('H:i:s'),
                'status' => $att->status,
                'work_type' => $att->work_type,
                'late_minutes' => $att->late_minutes,
                'overtime_minutes' => $att->overtime_minutes,
                'shift' => $att->shift?->name,
            ];
        });

        return response()->json($data);
    }

    public function today(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->with(['shift'])
            ->first();

        if (!$attendance) {
            return response()->json([
                'clocked_in' => false,
                'clocked_out' => false,
                'clock_in_time' => null,
                'clock_out_time' => null,
                'status' => 'absent',
                'work_type' => null,
            ]);
        }

        return response()->json([
            'clocked_in' => (bool) $attendance->clock_in,
            'clocked_out' => (bool) $attendance->clock_out,
            'clock_in_time' => $attendance->clock_in?->format('H:i:s'),
            'clock_out_time' => $attendance->clock_out?->format('H:i:s'),
            'status' => $attendance->status,
            'work_type' => $attendance->work_type,
            'late_minutes' => $attendance->late_minutes,
            'overtime_minutes' => $attendance->overtime_minutes,
            'shift_name' => $attendance->shift?->name,
        ]);
    }

    protected function haversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
