<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $status = $request->get('status');
        $leaves = Leave::where('employee_id', $employee->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['leaveType', 'leaveApprovals.approver'])
            ->latest()
            ->paginate(15);

        $data = $leaves->through(function ($leave) {
            return [
                'id' => $leave->id,
                'leave_type' => $leave->leaveType?->name,
                'start_date' => $leave->start_date->format('Y-m-d'),
                'end_date' => $leave->end_date->format('Y-m-d'),
                'total_days' => $leave->total_days,
                'reason' => $leave->reason,
                'status' => $leave->status,
                'attachment_url' => $leave->attachment ? asset('storage/' . $leave->attachment) : null,
                'approvals' => $leave->leaveApprovals->map(fn($a) => [
                    'level' => $a->level,
                    'status' => $a->status,
                    'approver' => $a->approver?->first_name . ' ' . $a->approver?->last_name,
                    'notes' => $a->notes,
                    'approved_at' => $a->approved_at?->format('Y-m-d H:i:s'),
                ]),
                'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment_base64' => ['nullable', 'string'],
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $totalDays = $startDate->diffInDaysFiltered(fn(Carbon $date) => $date->dayOfWeek !== Carbon::SUNDAY, $endDate) + 1;

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', now()->year)
            ->first();

        if ($balance && $balance->remaining_days < $totalDays) {
            return response()->json(['message' => 'Sisa cuti tidak mencukupi.'], 422);
        }

        if ($leaveType->max_days && $totalDays > $leaveType->max_days) {
            return response()->json(['message' => 'Maksimal pengajuan cuti adalah ' . $leaveType->max_days . ' hari.'], 422);
        }

        $attachmentPath = null;
        if ($request->filled('attachment_base64')) {
            $data = base64_decode(preg_replace('/^data:.*base64,/', '', $request->attachment_base64));
            $filename = 'leaves/' . $employee->id . '/attachment_' . time() . '.pdf';
            Storage::disk('public')->put($filename, $data);
            $attachmentPath = $filename;
        }

        $leave = Leave::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
            'status' => $leaveType->require_approval ? 'pending' : 'approved',
        ]);

        if ($leave->status === 'approved' && $balance) {
            $balance->increment('used_days', $totalDays);
            $balance->decrement('remaining_days', $totalDays);
        }

        return response()->json([
            'message' => 'Pengajuan cuti berhasil dibuat.',
            'data' => ['id' => $leave->id, 'status' => $leave->status],
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $leave = Leave::where('employee_id', $employee->id)
            ->with(['leaveType', 'leaveApprovals.approver'])
            ->findOrFail($id);

        return response()->json([
            'id' => $leave->id,
            'leave_type' => $leave->leaveType?->name,
            'start_date' => $leave->start_date->format('Y-m-d'),
            'end_date' => $leave->end_date->format('Y-m-d'),
            'total_days' => $leave->total_days,
            'reason' => $leave->reason,
            'status' => $leave->status,
            'rejection_reason' => $leave->rejection_reason,
            'attachment_url' => $leave->attachment ? asset('storage/' . $leave->attachment) : null,
            'approvals' => $leave->leaveApprovals->map(fn($a) => [
                'level' => $a->level,
                'status' => $a->status,
                'approver' => $a->approver?->first_name . ' ' . $a->approver?->last_name,
                'notes' => $a->notes,
                'approved_at' => $a->approved_at?->format('Y-m-d H:i:s'),
            ]),
            'created_at' => $leave->created_at->format('Y-m-d H:i:s'),
        ]);
    }

    public function balances(Request $request)
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            return response()->json(['message' => 'Data karyawan tidak ditemukan.'], 404);
        }

        $balances = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->with('leaveType')
            ->get();

        $data = $balances->map(fn($b) => [
            'leave_type' => $b->leaveType?->name,
            'total_days' => $b->total_days,
            'used_days' => $b->used_days,
            'remaining_days' => $b->remaining_days,
            'is_annual' => $b->leaveType?->is_annual,
        ]);

        return response()->json(['data' => $data]);
    }

    public function types(Request $request)
    {
        $user = $request->user();

        $types = LeaveType::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'code' => $t->code,
                'name' => $t->name,
                'default_days' => $t->default_days,
                'max_days' => $t->max_days,
                'is_paid' => $t->is_paid,
                'require_attachment' => $t->require_attachment,
                'require_approval' => $t->require_approval,
            ]);

        return response()->json(['data' => $types]);
    }
}
