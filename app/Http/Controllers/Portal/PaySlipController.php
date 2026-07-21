<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollPeriod;
use App\Models\PaySlip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaySlipController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $payrolls = Payroll::where('employee_id', $employee->id)
            ->with(['period', 'paySlip'])
            ->latest()
            ->paginate(12);

        return view('portal.payslip-index', compact('employee', 'payrolls'));
    }

    public function download($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('portal.dashboard')->with('error', 'Data karyawan tidak ditemukan.');
        }

        $paySlip = PaySlip::whereHas('payroll', function ($q) use ($employee) {
            $q->where('employee_id', $employee->id);
        })->findOrFail($id);

        if ($paySlip->file_path && file_exists(storage_path('app/public/' . $paySlip->file_path))) {
            $paySlip->update(['viewed_at' => now()]);
            return response()->download(storage_path('app/public/' . $paySlip->file_path));
        }

        $payroll = $paySlip->payroll()->with(['employee', 'period', 'payrollItems.salaryComponent'])->first();

        if (!$payroll) {
            abort(404, 'Data payroll tidak ditemukan.');
        }

        $paySlip->update(['viewed_at' => now()]);

        return view('portal.payslip-pdf', compact('payroll', 'employee'));
    }
}
