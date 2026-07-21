<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\Overtime;
use App\Models\Reimbursement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json([
                'message' => 'Data karyawan tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        $today = Carbon::today();
        $monthStart = $today->copy()->startOfMonth();

        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today->toDateString())
            ->with(['shift'])
            ->first();

        $leaveBalances = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', $today->year)
            ->with('leaveType')
            ->get()
            ->map(fn($b) => [
                'leave_type' => $b->leaveType?->name,
                'remaining_days' => $b->remaining_days,
                'total_days' => $b->total_days,
                'used_days' => $b->used_days,
            ]);

        $pendingLeaves = Leave::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->count();

        $pendingOvertimes = Overtime::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->count();

        $pendingReimbursements = Reimbursement::where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->count();

        $monthlyAttendance = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$monthStart->toDateString(), $today->toDateString()])
            ->orderBy('date')
            ->get();

        $presentDays = $monthlyAttendance->whereIn('status', ['present', 'late'])->count();
        $lateDays = $monthlyAttendance->where('status', 'late')->count();
        $absentDays = $monthlyAttendance->filter(fn($a) => !$a->clock_in)->count();
        $totalOvertimeMinutes = $monthlyAttendance->sum('overtime_minutes');

        $unreadNotifications = $user->notifications()
            ->where('is_read', false)
            ->count();

        return response()->json([
            'data' => [
                'today_attendance' => $todayAttendance ? [
                    'clocked_in' => (bool) $todayAttendance->clock_in,
                    'clocked_out' => (bool) $todayAttendance->clock_out,
                    'clock_in_time' => $todayAttendance->clock_in?->format('H:i:s'),
                    'clock_out_time' => $todayAttendance->clock_out?->format('H:i:s'),
                    'status' => $todayAttendance->status,
                    'work_type' => $todayAttendance->work_type,
                    'late_minutes' => $todayAttendance->late_minutes,
                ] : null,
                'leave_balances' => $leaveBalances,
                'pending' => [
                    'leaves' => $pendingLeaves,
                    'overtimes' => $pendingOvertimes,
                    'reimbursements' => $pendingReimbursements,
                    'total' => $pendingLeaves + $pendingOvertimes + $pendingReimbursements,
                ],
                'monthly_summary' => [
                    'present_days' => $presentDays,
                    'late_days' => $lateDays,
                    'absent_days' => $absentDays,
                    'total_overtime_hours' => round($totalOvertimeMinutes / 60, 1),
                    'working_days_elapsed' => $monthlyAttendance->count(),
                ],
                'unread_notifications' => $unreadNotifications,
            ],
        ]);
    }
}
