<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\DeliveryOrder;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\Overtime;
use App\Models\Reimbursement;
use App\Models\Task;
use App\Models\Visit;
use App\Services\VoiceNoteService;
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

    public function home(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;
        $today = Carbon::today();
        $now = Carbon::now();

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Data karyawan tidak ditemukan.',
                'data' => null,
            ], 404);
        }

        // Today's attendance
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today->toDateString())
            ->with(['shift'])
            ->first();

        // Pending tasks count
        $pendingTasks = Task::whereHas('assignees', fn($q) => $q->where('employee_id', $employee->id))
            ->whereIn('status', ['todo', 'in_progress'])
            ->count();

        $overdueTasks = Task::whereHas('assignees', fn($q) => $q->where('employee_id', $employee->id))
            ->whereIn('status', ['todo', 'in_progress'])
            ->where('due_date', '<', $today->toDateString())
            ->count();

        // Unread notifications + voice notes
        $unreadNotifications = $user->notifications()->where('is_read', false)->count();
        $voiceNoteService = app(VoiceNoteService::class);
        $unreadVoiceNotes = $voiceNoteService->getUnplayedCount($user->id);

        // Today's schedule (visits + appointments + deliveries)
        $todayVisits = Visit::where('employee_id', $employee->id)
            ->where('date', $today->toDateString())
            ->orderBy('start_time')
            ->get()
            ->map(fn($v) => [
                'type' => 'visit',
                'id' => $v->id,
                'visit_type' => $v->visit_type,
                'location' => $v->location,
                'purpose' => $v->purpose,
                'start_time' => $v->start_time?->format('H:i'),
                'end_time' => $v->end_time?->format('H:i'),
                'status' => $v->status,
            ]);

        $todayDeliveries = DeliveryOrder::where('driver_id', $employee->id)
            ->whereDate('delivery_date', $today->toDateString())
            ->whereIn('status', ['pending', 'in_transit', 'out_for_delivery'])
            ->orderBy('estimated_arrival')
            ->get()
            ->map(fn($d) => [
                'type' => 'delivery',
                'id' => $d->id,
                'do_number' => $d->do_number,
                'customer_name' => $d->customer_name,
                'delivery_address' => $d->delivery_address,
                'status' => $d->status,
                'estimated_arrival' => $d->estimated_arrival?->format('H:i'),
            ]);

        $todayAppointments = \App\Models\Appointment::where(function ($q) use ($employee) {
            $q->where('doctor_id', $employee->id)
                ->orWhereHas('patient', fn($sub) => $sub->where('id',
                    \App\Models\Patient::where('employee_id', $employee->id)->value('id')
                ));
        })
            ->whereDate('appointment_date', $today->toDateString())
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->with(['patient', 'doctor'])
            ->orderBy('start_time')
            ->get()
            ->map(fn($a) => [
                'type' => 'appointment',
                'id' => $a->id,
                'patient_name' => $a->patient?->name,
                'doctor_name' => $a->doctor?->first_name . ' ' . $a->doctor?->last_name,
                'appointment_type' => $a->appointment_type,
                'start_time' => $a->start_time?->format('H:i'),
                'end_time' => $a->end_time?->format('H:i'),
                'status' => $a->status,
                'queue_number' => $a->queue_number,
            ]);

        $todaySchedule = collect()
            ->concat($todayVisits)
            ->concat($todayDeliveries)
            ->concat($todayAppointments)
            ->sortBy('start_time')
            ->values();

        // Pending approvals
        $pendingApprovals = 0;
        $pendingLeaves = Leave::where('employee_id', $employee->id)->where('status', 'pending')->count();
        $pendingOvertimes = Overtime::where('employee_id', $employee->id)->where('status', 'pending')->count();
        $pendingReimbursements = Reimbursement::where('employee_id', $employee->id)->where('status', 'pending')->count();
        $pendingApprovals = $pendingLeaves + $pendingOvertimes + $pendingReimbursements;

        // Quick actions menu config
        $quickActions = $this->getQuickActions($user, $employee);

        // Offline sync status
        $offlinePendingCount = \App\Models\OfflineAction::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Data home mobile.',
            'data' => [
                'greeting' => $this->getGreeting($now) . ', ' . $employee->first_name,
                'attendance' => $todayAttendance ? [
                    'clocked_in' => (bool) $todayAttendance->clock_in,
                    'clocked_out' => (bool) $todayAttendance->clock_out,
                    'clock_in_time' => $todayAttendance->clock_in?->format('H:i:s'),
                    'clock_out_time' => $todayAttendance->clock_out?->format('H:i:s'),
                    'status' => $todayAttendance->status,
                    'work_type' => $todayAttendance->work_type,
                    'shift' => $todayAttendance->shift?->name,
                    'can_clock_in' => !$todayAttendance || !$todayAttendance->clock_in,
                    'can_clock_out' => $todayAttendance && $todayAttendance->clock_in && !$todayAttendance->clock_out,
                ] : [
                    'clocked_in' => false,
                    'clocked_out' => false,
                    'status' => 'absent',
                    'can_clock_in' => true,
                    'can_clock_out' => false,
                ],
                'tasks' => [
                    'pending' => $pendingTasks,
                    'overdue' => $overdueTasks,
                    'total' => $pendingTasks + $overdueTasks,
                ],
                'notifications' => [
                    'unread' => $unreadNotifications,
                    'unread_voice_notes' => $unreadVoiceNotes,
                    'total_unread' => $unreadNotifications + $unreadVoiceNotes,
                ],
                'pending_approvals' => $pendingApprovals,
                'today_schedule' => $todaySchedule,
                'schedule_count' => $todaySchedule->count(),
                'quick_actions' => $quickActions,
                'offline_sync' => [
                    'pending_actions' => $offlinePendingCount,
                    'needs_sync' => $offlinePendingCount > 0,
                ],
                'timestamp' => $now->toIso8601String(),
            ],
        ]);
    }

    protected function getGreeting(Carbon $now): string
    {
        $hour = $now->hour;
        if ($hour >= 3 && $hour < 11) return 'Selamat pagi';
        if ($hour >= 11 && $hour < 15) return 'Selamat siang';
        if ($hour >= 15 && $hour < 19) return 'Selamat sore';
        return 'Selamat malam';
    }

    protected function getQuickActions($user, $employee): array
    {
        $actions = [];

        $actions[] = [
            'id' => 'clock_in',
            'label' => 'Clock In',
            'icon' => 'login',
            'route' => '/attendance/clock-in',
            'color' => 'emerald',
            'visible' => true,
        ];

        $actions[] = [
            'id' => 'clock_out',
            'label' => 'Clock Out',
            'icon' => 'logout',
            'route' => '/attendance/clock-out',
            'color' => 'red',
            'visible' => true,
        ];

        $actions[] = [
            'id' => 'visit',
            'label' => 'Kunjungan',
            'icon' => 'map-pin',
            'route' => '/visits',
            'color' => 'blue',
            'visible' => true,
        ];

        $actions[] = [
            'id' => 'scan',
            'label' => 'Scan Barcode',
            'icon' => 'scan',
            'route' => '/scan',
            'color' => 'amber',
            'visible' => true,
        ];

        $actions[] = [
            'id' => 'tasks',
            'label' => 'Tugas Saya',
            'icon' => 'clipboard-list',
            'route' => '/tasks',
            'color' => 'indigo',
            'visible' => true,
        ];

        $actions[] = [
            'id' => 'delivery',
            'label' => 'Pengiriman',
            'icon' => 'truck',
            'route' => '/deliveries',
            'color' => 'orange',
            'visible' => true,
        ];

        $actions[] = [
            'id' => 'ptt',
            'label' => 'PTT',
            'icon' => 'radio',
            'route' => '/ptt',
            'color' => 'violet',
            'visible' => true,
        ];

        $actions[] = [
            'id' => 'voice_note',
            'label' => 'Voice Note',
            'icon' => 'microphone',
            'route' => '/voice-notes',
            'color' => 'pink',
            'visible' => true,
        ];

        return $actions;
    }
}
