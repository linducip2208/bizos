<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceConfig;
use App\Models\AttendanceLog;
use App\Models\ShiftEmployee;
use App\Models\WifiAccessPoint;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    protected PushNotificationService $push;

    public function __construct(PushNotificationService $push)
    {
        $this->push = $push;
    }

    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $month = request('month', now()->format('Y-m'));
        $startDate = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['shift'])
            ->orderBy('date', 'desc')
            ->get();

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', now()->toDateString())
            ->first();

        return view('portal.attendance-index', compact('employee', 'attendances', 'todayAttendance', 'month'));
    }

    public function clockIn(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        $today = Carbon::today();
        $now = Carbon::now();

        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today->toDateString())
            ->first();

        if ($existingAttendance && $existingAttendance->clock_in) {
            return back()->with('error', 'Anda sudah clock-in hari ini.');
        }

        $request->validate([
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'wifi_bssid' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $config = AttendanceConfig::where('company_id', $user->company_id)->first();

        $workType = 'office';
        $lateMinutes = 0;
        $overtimeMinutes = 0;

        if ($config) {
            if (in_array('gps', (array) $config->method) && $config->gps_latitude && $config->gps_longitude) {
                if ($request->filled('latitude') && $request->filled('longitude')) {
                    $distance = $this->haversineDistance(
                        $config->gps_latitude, $config->gps_longitude,
                        $request->latitude, $request->longitude
                    );

                    $radius = $config->gps_radius_meters ?? 100;
                    if ($distance > $radius) {
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

        $shiftStart = null;
        if ($shift && $shift->shift) {
            $shiftTime = $shift->shift->start_time;
            if ($shiftTime) {
                $shiftStart = Carbon::parse($shiftTime)->format('H:i:s');
                $gracePeriod = $shift->shift->grace_period_minutes ?? 0;

                $expectedClockIn = Carbon::today()->setTimeFromTimeString($shiftStart);
                $graceDeadline = $expectedClockIn->copy()->addMinutes($gracePeriod);

                if ($now->gt($graceDeadline)) {
                    $lateMinutes = $expectedClockIn->diffInMinutes($now);
                }
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance/' . $employee->id . '/' . $today->format('Y/m'), 'public');
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

        return back()->with('success', 'Clock-in berhasil pada ' . $now->format('H:i') . '. '
            . ($lateMinutes > 0 ? "Terlambat {$lateMinutes} menit." : 'Tepat waktu.')
            . ' Tipe: ' . strtoupper($workType));
    }

    public function clockOut(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        $today = Carbon::today();
        $now = Carbon::now();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today->toDateString())
            ->whereNotNull('clock_in')
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Anda belum clock-in hari ini.');
        }

        if ($attendance->clock_out) {
            return back()->with('error', 'Anda sudah clock-out hari ini.');
        }

        $request->validate([
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'wifi_bssid' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $overtimeMinutes = 0;
        $earlyDepartureMinutes = 0;

        $shift = $attendance->shift;
        if ($shift && $shift->end_time) {
            $shiftEnd = Carbon::today()->setTimeFromTimeString(Carbon::parse($shift->end_time)->format('H:i:s'));
            if ($now->lt($shiftEnd)) {
                $earlyDepartureMinutes = $now->diffInMinutes($shiftEnd);
            } elseif ($now->gt($shiftEnd)) {
                $overtimeMinutes = $shiftEnd->diffInMinutes($now);
            }
        } else {
            $clockIn = Carbon::parse($attendance->clock_in);
            $standardHours = 9 * 60;
            $workedMinutes = $clockIn->diffInMinutes($now);
            if ($workedMinutes > $standardHours) {
                $overtimeMinutes = $workedMinutes - $standardHours;
            } elseif ($workedMinutes < $standardHours) {
                $earlyDepartureMinutes = $standardHours - $workedMinutes;
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('attendance/' . $employee->id . '/' . $today->format('Y/m'), 'public');
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

        $clockInTime = Carbon::parse($attendance->clock_in);
        $hours = floor($clockInTime->diffInMinutes($now) / 60);
        $minutes = $clockInTime->diffInMinutes($now) % 60;
        $duration = $hours . 'j ' . $minutes . 'm';

        return back()->with('success', "Clock-out berhasil pada {$now->format('H:i')}. "
            . "Durasi: {$duration}. "
            . ($overtimeMinutes > 0 ? "Lembur {$overtimeMinutes} menit." : ''));
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard');
        }

        $month = $request->get('month', now()->format('Y-m'));

        return redirect()->route('portal.attendance.index', ['month' => $month]);
    }

    public function todayStatus()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['error' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        return response()->json([
            'clocked_in' => $attendance && $attendance->clock_in ? true : false,
            'clocked_out' => $attendance && $attendance->clock_out ? true : false,
            'clock_in_time' => $attendance?->clock_in?->format('H:i:s'),
            'clock_out_time' => $attendance?->clock_out?->format('H:i:s'),
            'status' => $attendance?->status,
            'work_type' => $attendance?->work_type,
            'late_minutes' => $attendance?->late_minutes,
            'overtime_minutes' => $attendance?->overtime_minutes,
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
