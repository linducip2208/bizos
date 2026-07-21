<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Overtime;
use App\Models\Reimbursement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;

        $clientIds = ClientContact::where('email', $user->email)
            ->pluck('client_id')
            ->toArray();

        $invoices = Invoice::whereIn('reference_entity', ['Client', 'App\Models\Client'])
            ->whereIn('reference_id', $clientIds)
            ->latest('invoice_date')
            ->limit(10)
            ->get();

        $overdueInvoices = Invoice::whereIn('reference_entity', ['Client', 'App\Models\Client'])
            ->whereIn('reference_id', $clientIds)
            ->where('status', 'overdue')
            ->count();

        $totalPaid = InvoicePayment::whereIn('invoice_id', $invoices->pluck('id'))
            ->sum('amount');

        $totalOutstanding = Invoice::whereIn('reference_entity', ['Client', 'App\Models\Client'])
            ->whereIn('reference_id', $clientIds)
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->sum('remaining_amount');

        $dashboardInvoices = Invoice::whereIn('reference_entity', ['Client', 'App\Models\Client'])
            ->whereIn('reference_id', $clientIds)
            ->with(['invoicePayments', 'invoiceItems'])
            ->latest('invoice_date')
            ->get();

        $todayAttendance = null;
        $leaveBalances = collect();
        $pendingApprovals = 0;
        $upcomingBirthdays = collect();
        $monthlyAttendance = collect();
        $pendingLeaves = 0;
        $pendingOvertimes = 0;
        $pendingReimbursements = 0;
        $announcements = collect();
        $recentLeaves = collect();
        $recentOvertimes = collect();

        if ($employee) {
            $today = Carbon::today();
            $monthStart = $today->copy()->startOfMonth();

            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $today->toDateString())
                ->with(['shift'])
                ->first();

            $leaveBalances = LeaveBalance::where('employee_id', $employee->id)
                ->where('year', $today->year)
                ->with('leaveType')
                ->get();

            $pendingLeaves = Leave::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->count();

            $pendingOvertimes = Overtime::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->count();

            $pendingReimbursements = Reimbursement::where('employee_id', $employee->id)
                ->where('status', 'pending')
                ->count();

            $pendingApprovals = $pendingLeaves + $pendingOvertimes + $pendingReimbursements;

            $upcomingBirthdays = Employee::where('company_id', $user->company_id)
                ->where('status', 'active')
                ->whereRaw("DATE_FORMAT(birth_date, '%m-%d') >= ?", [$today->format('m-d')])
                ->whereRaw("DATE_FORMAT(birth_date, '%m-%d') <= ?", [$today->copy()->addDays(7)->format('m-d')])
                ->orderByRaw("DATE_FORMAT(birth_date, '%m-%d')")
                ->limit(5)
                ->get(['id', 'first_name', 'last_name', 'birth_date', 'photo', 'department_id'])
                ->load('department');

            $announcements = Announcement::where('company_id', $user->company_id)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->latest('published_at')
                ->limit(5)
                ->get();

            $recentLeaves = Leave::where('employee_id', $employee->id)
                ->with(['leaveType', 'leaveApprovals.approver'])
                ->latest()
                ->limit(5)
                ->get();

            $recentOvertimes = Overtime::where('employee_id', $employee->id)
                ->latest()
                ->limit(5)
                ->get();

            $monthlyAttendance = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$monthStart->toDateString(), $today->toDateString()])
                ->orderBy('date', 'desc')
                ->get();
        }

        return view('portal.dashboard', compact(
            'invoices', 'overdueInvoices', 'totalPaid', 'totalOutstanding',
            'clientIds', 'dashboardInvoices', 'employee', 'todayAttendance',
            'leaveBalances', 'pendingApprovals', 'upcomingBirthdays',
            'announcements', 'recentLeaves', 'recentOvertimes',
            'monthlyAttendance', 'pendingLeaves', 'pendingOvertimes', 'pendingReimbursements',
        ));
    }

    public function invoices()
    {
        $user = Auth::user();
        $employee = $user->employee;

        $clientIds = ClientContact::where('email', $user->email)
            ->pluck('client_id')
            ->toArray();

        $invoices = Invoice::whereIn('reference_entity', ['Client', 'App\Models\Client'])
            ->whereIn('reference_id', $clientIds)
            ->with(['invoicePayments', 'invoiceItems'])
            ->latest('invoice_date')
            ->get();

        return view('portal.invoice-list', compact('invoices', 'clientIds', 'employee'));
    }

    public function invoiceDetail($id)
    {
        $user = Auth::user();
        $employee = $user->employee;

        $clientIds = ClientContact::where('email', $user->email)
            ->pluck('client_id')
            ->toArray();

        $invoice = Invoice::whereIn('reference_entity', ['Client', 'App\Models\Client'])
            ->whereIn('reference_id', $clientIds)
            ->with(['invoiceItems', 'invoicePayments.payment', 'invoicePayments.payment.paymentMethod'])
            ->findOrFail($id);

        return view('portal.invoice-detail', compact('invoice', 'employee'));
    }
}
