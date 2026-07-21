<?php

namespace App\Http\Controllers\SupplierPortal;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $supplierUser = Auth::guard('supplier')->user();
        $supplierId = $supplierUser->supplier_id;

        $activePOs = PurchaseOrder::where('supplier_id', $supplierId)
            ->whereIn('status', ['draft', 'sent', 'confirmed', 'partial'])
            ->count();

        $pendingDeliveries = PurchaseOrder::where('supplier_id', $supplierId)
            ->whereIn('status', ['sent', 'confirmed'])
            ->count();

        $paidPOs = PurchaseOrder::where('supplier_id', $supplierId)
            ->where('status', 'paid')
            ->count();

        $totalValue = PurchaseOrder::where('supplier_id', $supplierId)
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        $recentPOs = PurchaseOrder::where('supplier_id', $supplierId)
            ->with(['goodsReceipts', 'items'])
            ->latest('order_date')
            ->limit(10)
            ->get();

        return view('supplier-portal.dashboard', compact(
            'supplierUser', 'activePOs', 'pendingDeliveries',
            'paidPOs', 'totalValue', 'recentPOs'
        ));
    }
}
