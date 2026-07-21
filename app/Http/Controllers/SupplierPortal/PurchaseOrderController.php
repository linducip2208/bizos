<?php

namespace App\Http\Controllers\SupplierPortal;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $supplierUser = Auth::guard('supplier')->user();
        $supplierId = $supplierUser->supplier_id;

        $status = $request->get('status');
        $pos = PurchaseOrder::where('supplier_id', $supplierId)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['items', 'goodsReceipts'])
            ->latest('order_date')
            ->paginate(15);

        return view('supplier-portal.po-index', compact('supplierUser', 'pos', 'status'));
    }

    public function show($id)
    {
        $supplierUser = Auth::guard('supplier')->user();
        $supplierId = $supplierUser->supplier_id;

        $po = PurchaseOrder::where('supplier_id', $supplierId)
            ->with([
                'items.product',
                'goodsReceipts.items',
                'purchaseRequisition',
                'warehouse',
                'creator',
            ])
            ->findOrFail($id);

        return view('supplier-portal.po-show', compact('supplierUser', 'po'));
    }

    public function updateStatus(Request $request, $id)
    {
        $supplierUser = Auth::guard('supplier')->user();
        $supplierId = $supplierUser->supplier_id;

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,shipped,delivered'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $po = PurchaseOrder::where('supplier_id', $supplierId)->findOrFail($id);

        $allowedTransitions = [
            'sent' => ['confirmed'],
            'confirmed' => ['shipped'],
            'shipped' => ['delivered'],
        ];

        $currentStatus = $po->status;
        if (!isset($allowedTransitions[$currentStatus]) || !in_array($validated['status'], $allowedTransitions[$currentStatus])) {
            return back()->with('error', 'Status tidak dapat diubah dari "' . $currentStatus . '" ke "' . $validated['status'] . '".');
        }

        $po->update([
            'status' => $validated['status'],
            'notes' => $po->notes . "\n[" . now()->format('d M Y H:i') . "] Supplier: " . ($validated['notes'] ?? 'Status diubah menjadi ' . $validated['status']),
        ]);

        return back()->with('success', 'Status PO berhasil diperbarui menjadi "' . $validated['status'] . '".');
    }

    public function uploadInvoice(Request $request, $id)
    {
        $supplierUser = Auth::guard('supplier')->user();
        $supplierId = $supplierUser->supplier_id;

        $request->validate([
            'invoice_file' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
            'invoice_number' => ['required', 'string', 'max:100'],
            'invoice_amount' => ['nullable', 'numeric'],
        ]);

        $po = PurchaseOrder::where('supplier_id', $supplierId)->findOrFail($id);

        $path = $request->file('invoice_file')->store('purchase-orders/' . $po->id . '/invoices', 'public');

        $po->update([
            'notes' => $po->notes . "\n[" . now()->format('d M Y H:i') . "] Invoice diupload: {$request->invoice_number}"
                . ($request->invoice_amount ? " (Rp " . number_format($request->invoice_amount, 0, ',', '.') . ")" : '')
                . " | File: " . $path,
        ]);

        return back()->with('success', 'Invoice berhasil diupload.');
    }
}
