<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Deal;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $clientUser = Auth::guard('client')->user();
        $clientId = $clientUser->client_id;

        $invoices = collect();
        $totalInvoices = 0;
        $totalPaid = 0;
        $totalOutstanding = 0;
        $overdueInvoices = 0;
        $deals = collect();
        $tickets = collect();

        if ($clientId) {
            $invoices = Invoice::whereIn('reference_entity', ['Client', 'App\Models\Client'])
                ->where('reference_id', $clientId)
                ->with(['invoicePayments', 'invoiceItems'])
                ->latest('invoice_date')
                ->get();

            $totalInvoices = $invoices->count();
            $totalPaid = $invoices->sum('paid_amount');
            $totalOutstanding = $invoices->whereIn('status', ['sent', 'partial', 'overdue'])->sum('remaining_amount');
            $overdueInvoices = $invoices->where('status', 'overdue')->count();

            $deals = Deal::where('client_id', $clientId)
                ->with(['stage'])
                ->latest()
                ->limit(5)
                ->get();

            $tickets = Ticket::where('client_id', $clientId)
                ->with(['category', 'assignedTo'])
                ->latest('updated_at')
                ->limit(5)
                ->get();
        }

        return view('client-portal.dashboard', compact(
            'clientUser', 'invoices', 'totalInvoices', 'totalPaid',
            'totalOutstanding', 'overdueInvoices', 'deals', 'tickets'
        ));
    }

    public function invoices(Request $request)
    {
        $clientUser = Auth::guard('client')->user();
        $clientId = $clientUser->client_id;

        $invoices = collect();

        if ($clientId) {
            $invoices = Invoice::whereIn('reference_entity', ['Client', 'App\Models\Client'])
                ->where('reference_id', $clientId)
                ->with(['invoicePayments', 'invoiceItems'])
                ->latest('invoice_date')
                ->get();
        }

        return view('client-portal.invoices', compact('clientUser', 'invoices'));
    }

    public function invoiceDetail($id)
    {
        $clientUser = Auth::guard('client')->user();
        $clientId = $clientUser->client_id;

        $invoice = Invoice::whereIn('reference_entity', ['Client', 'App\Models\Client'])
            ->where('reference_id', $clientId)
            ->with(['invoiceItems', 'invoicePayments.payment', 'invoicePayments.payment.paymentMethod'])
            ->findOrFail($id);

        return view('client-portal.invoice-detail', compact('clientUser', 'invoice'));
    }

    public function profile(Request $request)
    {
        $clientUser = Auth::guard('client')->user();

        if ($request->isMethod('post')) {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:20'],
                'password' => ['nullable', 'min:8', 'confirmed'],
            ]);

            $clientUser->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? $clientUser->phone,
            ]);

            if (!empty($validated['password'])) {
                $clientUser->update(['password' => \Illuminate\Support\Facades\Hash::make($validated['password'])]);
            }

            return back()->with('success', 'Profil berhasil diperbarui.');
        }

        return view('client-portal.profile', compact('clientUser'));
    }
}
