<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentLinkController extends Controller
{
    protected function findInvoice(string $token): Invoice
    {
        $invoice = Invoice::with(['invoiceItems', 'company', 'currency', 'payments'])
            ->where('payment_token', $token)
            ->firstOrFail();

        if ($invoice->isPaymentLinkExpired()) {
            abort(410, 'Link pembayaran telah kedaluwarsa.');
        }

        return $invoice;
    }

    protected function clientName(Invoice $invoice): string
    {
        if (in_array($invoice->reference_entity, ['client', 'Client', 'App\Models\Client'], true)) {
            return Client::find($invoice->reference_id)?->name ?? '—';
        }

        return '—';
    }

    protected function generatePaymentNumber(int $companyId): string
    {
        $prefix = 'PAY';
        $date = Carbon::now()->format('Ym');

        $last = Payment::where('company_id', $companyId)
            ->where('payment_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('payment_number', 'desc')
            ->first();

        $seq = 1;
        if ($last && preg_match('/-(\d{5})$/', $last->payment_number, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf("{$prefix}-{$date}-%05d", $seq);
    }

    public function show(string $token)
    {
        $invoice = $this->findInvoice($token);

        return view('payment-link.show', [
            'invoice' => $invoice,
            'clientName' => $this->clientName($invoice),
        ]);
    }

    public function pay(string $token)
    {
        $invoice = $this->findInvoice($token);

        if ($invoice->status === 'paid' || $invoice->remaining_amount <= 0) {
            return redirect()->route('pay.show', $token);
        }

        $paymentMethods = PaymentMethod::where('company_id', $invoice->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $bankAccounts = BankAccount::where('company_id', $invoice->company_id)
            ->where('is_active', true)
            ->get();

        return view('payment-link.pay', [
            'invoice' => $invoice,
            'clientName' => $this->clientName($invoice),
            'paymentMethods' => $paymentMethods,
            'bankAccounts' => $bankAccounts,
        ]);
    }

    public function submitPayment(Request $request, string $token)
    {
        $invoice = $this->findInvoice($token);

        if ($invoice->status === 'paid' || $invoice->remaining_amount <= 0) {
            return redirect()->route('pay.thank-you', $token);
        }

        $validated = $request->validate([
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:'.$invoice->remaining_amount],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
            'proof' => ['nullable', 'file', 'image', 'max:5120'],
        ]);

        $method = PaymentMethod::find($validated['payment_method_id']);
        $isTransfer = $method && str_contains(strtolower($method->code ?? ''), 'transfer');

        if ($isTransfer && ! $request->hasFile('proof')) {
            return back()
                ->withErrors(['proof' => 'Bukti transfer wajib diunggah untuk pembayaran via transfer bank.'])
                ->withInput();
        }

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = $request->file('proof')->store('payment-proofs', 'public');
        }

        $amount = (float) $validated['amount'];
        $paymentNumber = $this->generatePaymentNumber($invoice->company_id);

        $payment = DB::transaction(function () use ($invoice, $validated, $amount, $paymentNumber, $proofPath, $isTransfer) {
            $payment = Payment::create([
                'company_id' => $invoice->company_id,
                'branch_id' => $invoice->branch_id,
                'payment_number' => $paymentNumber,
                'payment_date' => now()->format('Y-m-d'),
                'payment_method_id' => $validated['payment_method_id'],
                'amount' => $amount,
                'reference_number' => $validated['reference_number'] ?? $paymentNumber,
                'proof_path' => $proofPath,
                'notes' => $validated['notes'] ?? "Pembayaran online faktur {$invoice->invoice_number}",
                'status' => $isTransfer ? 'pending' : 'confirmed',
                'confirmed_at' => $isTransfer ? null : now(),
                'currency_id' => $invoice->currency_id,
                'exchange_rate' => $invoice->exchange_rate,
            ]);

            InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'amount' => $amount,
            ]);

            $newPaid = $invoice->paid_amount + $amount;
            $newRemaining = $invoice->total - $newPaid;

            $newStatus = 'partial';
            if ($newRemaining <= 0) {
                $newStatus = 'paid';
            }

            $invoice->update([
                'paid_amount' => $newPaid,
                'remaining_amount' => max(0, $newRemaining),
                'status' => $newStatus,
            ]);

            return $payment;
        });

        return redirect()->route('pay.thank-you', ['token' => $token, 'payment' => $payment->payment_number]);
    }

    public function uploadProof(Request $request, string $token)
    {
        $invoice = $this->findInvoice($token);

        $validated = $request->validate([
            'proof' => ['required', 'file', 'image', 'max:5120'],
        ]);

        $path = $request->file('proof')->store('payment-proofs', 'public');

        return response()->json([
            'path' => $path,
            'url' => Storage::disk('public')->url($path),
        ]);
    }

    public function thankYou(string $token)
    {
        $invoice = $this->findInvoice($token);

        return view('payment-link.thank-you', [
            'invoice' => $invoice,
            'paymentNumber' => request('payment'),
        ]);
    }
}
