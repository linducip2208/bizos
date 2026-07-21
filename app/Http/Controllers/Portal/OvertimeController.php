<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Overtime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $status = $request->get('status');
        $overtimes = Overtime::where('employee_id', $employee->id)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['approvedBy'])
            ->latest()
            ->paginate(15);

        return view('portal.overtime-index', compact('employee', 'overtimes', 'status'));
    }

    public function create()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.overtime-create', compact('employee'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('error', 'Data karyawan tidak ditemukan.');
        }

        $validated = $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'reason' => ['required', 'string', 'max:1000'],
            'rate_multiplier' => ['nullable', 'numeric', 'min:1', 'max:3'],
        ]);

        $startTime = Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $endTime = Carbon::parse($validated['date'] . ' ' . $validated['end_time']);

        if ($endTime->lte($startTime)) {
            $endTime->addDay();
        }

        $durationMinutes = $startTime->diffInMinutes($endTime);

        $overtime = Overtime::create([
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $durationMinutes,
            'rate_multiplier' => $validated['rate_multiplier'] ?? ($employee->overtime_rate ?? 1.5),
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('portal.overtime.show', $overtime->id)
            ->with('success', 'Pengajuan lembur berhasil dibuat.');
    }

    public function show($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard');
        }

        $overtime = Overtime::where('employee_id', $employee->id)
            ->with(['approvedBy'])
            ->findOrFail($id);

        return view('portal.overtime-show', compact('employee', 'overtime'));
    }
}
