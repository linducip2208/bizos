<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Models\LeaveApproval;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $status = $request->get('status');
        $leaves = Leave::where('employee_id', $employee->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['leaveType', 'leaveApprovals.approver'])
            ->latest()
            ->paginate(15);

        return view('portal.leave-index', compact('employee', 'leaves', 'status'));
    }

    public function create()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $leaveTypes = LeaveType::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->get();

        $balances = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', now()->year)
            ->with('leaveType')
            ->get();

        return view('portal.leave-create', compact('employee', 'leaveTypes', 'balances'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $totalDays = $startDate->diffInDaysFiltered(function (Carbon $date) {
            $dayOfWeek = $date->dayOfWeek;
            return $dayOfWeek !== Carbon::SUNDAY;
        }, $endDate) + 1;

        $leaveType = LeaveType::findOrFail($validated['leave_type_id']);

        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', now()->year)
            ->first();

        if ($balance && $balance->remaining_days < $totalDays) {
            return back()->with('error', 'Sisa cuti tidak mencukupi. Tersedia ' . $balance->remaining_days . ' hari.');
        }

        if ($leaveType->max_days && $totalDays > $leaveType->max_days) {
            return back()->with('error', 'Maksimal pengajuan cuti adalah ' . $leaveType->max_days . ' hari.');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leaves/' . $employee->id, 'public');
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

        if ($leaveType->require_approval) {
            $this->createApprovalChain($leave);
        } else if ($balance) {
            $balance->increment('used_days', $totalDays);
            $balance->decrement('remaining_days', $totalDays);
        }

        return redirect()->route('portal.leave.show', $leave->id)
            ->with('success', 'Pengajuan cuti berhasil dibuat.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard');
        }

        $leave = Leave::where('employee_id', $employee->id)
            ->with(['leaveType', 'leaveApprovals.approver'])
            ->findOrFail($id);

        return view('portal.leave-show', compact('employee', 'leave'));
    }

    protected function createApprovalChain(Leave $leave): void
    {
        $employee = $leave->employee;
        $departmentId = $employee->department_id;

        if ($departmentId) {
            LeaveApproval::create([
                'leave_id' => $leave->id,
                'approver_id' => null,
                'level' => 1,
                'status' => 'pending',
            ]);
        }
    }
}
