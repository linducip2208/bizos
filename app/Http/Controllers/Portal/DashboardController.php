<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

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

        return view('portal.dashboard', compact(
            'invoices',
            'overdueInvoices',
            'totalPaid',
            'totalOutstanding',
            'clientIds',
            'dashboardInvoices',
        ));
    }

    public function invoices()
    {
        $user = Auth::user();

        $clientIds = ClientContact::where('email', $user->email)
            ->pluck('client_id')
            ->toArray();

        $invoices = Invoice::whereIn('reference_entity', ['Client', 'App\Models\Client'])
            ->whereIn('reference_id', $clientIds)
            ->with(['invoicePayments', 'invoiceItems'])
            ->latest('invoice_date')
            ->get();

        return view('portal.dashboard', compact('invoices', 'clientIds'));
    }

    public function invoiceDetail($id)
    {
        $user = Auth::user();

        $clientIds = ClientContact::where('email', $user->email)
            ->pluck('client_id')
            ->toArray();

        $invoice = Invoice::whereIn('reference_entity', ['Client', 'App\Models\Client'])
            ->whereIn('reference_id', $clientIds)
            ->with(['invoiceItems', 'invoicePayments.payment', 'invoicePayments.payment.paymentMethod'])
            ->findOrFail($id);

        return view('portal.invoice-detail', compact('invoice'));
    }
}
