<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\WorkOrderPart;
use App\Models\TechnicianVan;
use App\Models\VanInventory;
use App\Services\FieldServiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FieldServiceController extends Controller
{
    protected FieldServiceService $service;

    public function __construct(FieldServiceService $service)
    {
        $this->service = $service;
    }

    public function checkin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wo_id' => 'required|exists:work_orders,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'photo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $wo = WorkOrder::findOrFail($request->wo_id);

        if ($wo->technician_id !== auth()->user()?->employee?->id) {
            return response()->json(['success' => false, 'message' => 'Bukan work order Anda'], 403);
        }

        $this->service->checkIn($wo, (float) $request->lat, (float) $request->lng, $request->photo);

        return response()->json([
            'success' => true,
            'message' => 'Check-in berhasil',
            'data' => ['status' => $wo->status, 'checkin_time' => $wo->actual_start],
        ]);
    }

    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wo_id' => 'required|exists:work_orders,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'photo' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $wo = WorkOrder::findOrFail($request->wo_id);

        if ($wo->technician_id !== auth()->user()?->employee?->id) {
            return response()->json(['success' => false, 'message' => 'Bukan work order Anda'], 403);
        }

        $this->service->checkOut($wo, (float) $request->lat, (float) $request->lng, $request->photo);

        return response()->json([
            'success' => true,
            'message' => 'Check-out berhasil, WO selesai',
            'data' => [
                'status' => $wo->status,
                'checkout_time' => $wo->actual_end,
                'travel_distance_km' => $wo->travel_distance_km,
                'labor_hours' => $wo->labor_hours,
            ],
        ]);
    }

    public function complete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wo_id' => 'required|exists:work_orders,id',
            'resolution' => 'required|string|min:10',
            'signature' => 'required|string',
            'photo' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $wo = WorkOrder::findOrFail($request->wo_id);

        if ($wo->technician_id !== auth()->user()?->employee?->id) {
            return response()->json(['success' => false, 'message' => 'Bukan work order Anda'], 403);
        }

        $this->service->complete($wo, $request->resolution, $request->signature, $request->photo);

        return response()->json([
            'success' => true,
            'message' => 'Work Order selesai dengan tanda tangan pelanggan',
            'data' => ['status' => $wo->status, 'signature_path' => $wo->customer_signature_path],
        ]);
    }

    public function myOrders(Request $request)
    {
        $employeeId = auth()->user()?->employee?->id;
        if (!$employeeId) {
            return response()->json(['success' => false, 'message' => 'Akun tidak terhubung ke data karyawan'], 400);
        }

        $orders = WorkOrder::with(['client', 'equipment', 'parts.product'])
            ->where('technician_id', $employeeId)
            ->whereDate('scheduled_start', now())
            ->orderBy('priority')
            ->get();

        return response()->json([
            'success' => true,
            'count' => $orders->count(),
            'data' => $orders->map(fn ($wo) => [
                'id' => $wo->id,
                'wo_number' => $wo->wo_number,
                'client_name' => $wo->client?->name,
                'client_address' => $wo->client?->address,
                'equipment' => $wo->equipment?->equipment_name,
                'service_type' => $wo->service_type,
                'priority' => $wo->priority,
                'status' => $wo->status,
                'description' => $wo->description,
                'scheduled_start' => $wo->scheduled_start?->toISOString(),
                'scheduled_end' => $wo->scheduled_end?->toISOString(),
                'service_charge' => $wo->service_charge,
                'parts' => $wo->parts->map(fn ($p) => [
                    'product_name' => $p->product?->name,
                    'quantity' => $p->quantity,
                    'unit_price' => $p->unit_price,
                    'subtotal' => $p->subtotal,
                    'from_van_stock' => $p->from_van_stock,
                ]),
            ]),
        ]);
    }

    public function vanStock(Request $request)
    {
        $employeeId = auth()->user()?->employee?->id;
        if (!$employeeId) {
            return response()->json(['success' => false, 'message' => 'Akun tidak terhubung ke data karyawan'], 400);
        }

        $van = TechnicianVan::where('technician_id', $employeeId)
            ->where('is_active', true)
            ->first();

        if (!$van) {
            return response()->json(['success' => false, 'message' => 'Tidak ada van aktif'], 404);
        }

        $inventory = VanInventory::with('product')
            ->where('van_id', $van->id)
            ->get()
            ->map(fn ($inv) => [
                'product_id' => $inv->product_id,
                'product_name' => $inv->product?->name,
                'quantity' => $inv->quantity,
                'min_quantity' => $inv->min_quantity,
                'reorder_point' => $inv->reorder_point,
                'low_stock' => $inv->quantity <= $inv->reorder_point,
            ]);

        return response()->json([
            'success' => true,
            'van' => [
                'license_plate' => $van->license_plate,
                'last_location_update' => $van->last_location_update?->toISOString(),
            ],
            'inventory' => $inventory,
        ]);
    }

    public function usePart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wo_id' => 'required|exists:work_orders,id',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|numeric|min:0.001',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $wo = WorkOrder::findOrFail($request->wo_id);

        if ($wo->technician_id !== auth()->user()?->employee?->id) {
            return response()->json(['success' => false, 'message' => 'Bukan work order Anda'], 403);
        }

        $product = \App\Models\Product::findOrFail($request->product_id);
        $unitPrice = $product->selling_price ?? $product->purchase_price ?? 0;
        $subtotal = $request->qty * $unitPrice;

        $part = WorkOrderPart::create([
            'work_order_id' => $wo->id,
            'product_id' => $request->product_id,
            'quantity' => $request->qty,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'from_van_stock' => true,
        ]);

        $this->service->deductVanStock($part);

        return response()->json([
            'success' => true,
            'message' => 'Part berhasil digunakan',
            'data' => [
                'product_name' => $product->name,
                'quantity' => $request->qty,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ],
        ]);
    }

    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $employeeId = auth()->user()?->employee?->id;
        if (!$employeeId) {
            return response()->json(['success' => false, 'message' => 'Akun tidak terhubung ke data karyawan'], 400);
        }

        TechnicianVan::where('technician_id', $employeeId)
            ->where('is_active', true)
            ->update([
                'current_location_lat' => (float) $request->lat,
                'current_location_lng' => (float) $request->lng,
                'last_location_update' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Lokasi diperbarui',
        ]);
    }
}
