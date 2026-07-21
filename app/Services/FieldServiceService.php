<?php

namespace App\Services;

use App\Models\ServiceContract;
use App\Models\ContractedEquipment;
use App\Models\WorkOrder;
use App\Models\WorkOrderPart;
use App\Models\TechnicianVan;
use App\Models\VanInventory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Employee;
use App\Models\ServiceChecklist;
use App\Models\ServiceChecklistItem;
use App\Models\WorkOrderChecklistItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FieldServiceService
{
    public function generateScheduledWorkOrders(): array
    {
        $results = ['created' => 0, 'skipped' => 0, 'errors' => 0];

        $contracts = ServiceContract::where('status', 'active')
            ->where('service_frequency', '!=', 'quarterly')
            ->with('contractedEquipment')
            ->get();

        foreach ($contracts as $contract) {
            if ($contract->contractedEquipment->isEmpty()) {
                $results['skipped']++;
                continue;
            }

            foreach ($contract->contractedEquipment as $equipment) {
                if (!$equipment->next_service_date || $equipment->next_service_date->gt(now()->toDateString())) {
                    $results['skipped']++;
                    continue;
                }

                if ($equipment->status !== 'active') {
                    $results['skipped']++;
                    continue;
                }

                try {
                    DB::transaction(function () use ($contract, $equipment) {
                        $wo = WorkOrder::create([
                            'company_id' => $contract->company_id,
                            'service_contract_id' => $contract->id,
                            'client_id' => $contract->client_id,
                            'equipment_id' => $equipment->id,
                            'wo_number' => $this->generateWoNumber($contract->company_id),
                            'service_type' => 'preventive',
                            'priority' => 'medium',
                            'description' => "Preventive maintenance: {$equipment->equipment_name} ({$equipment->brand} {$equipment->model}) — Kontrak #{$contract->contract_number}",
                            'reported_by' => 'Sistem (Auto)',
                            'scheduled_start' => now()->addDay()->setTime(8, 0),
                            'scheduled_end' => now()->addDay()->setTime(17, 0),
                            'status' => 'open',
                        ]);

                        $this->attachChecklistToWorkOrder($wo, $contract->company_id);

                        $nextDate = $this->calculateNextServiceDate($equipment->next_service_date ?? now(), $contract->service_frequency);
                        $equipment->update([
                            'last_service_date' => now(),
                            'next_service_date' => $nextDate,
                        ]);
                    });

                    $results['created']++;
                } catch (\Throwable $e) {
                    Log::error("Gagal generate WO untuk equipment {$equipment->id}: {$e->getMessage()}");
                    $results['errors']++;
                }
            }
        }

        return $results;
    }

    public function dispatchWorkOrder(WorkOrder $wo): array
    {
        $client = $wo->client;
        $clientLat = $this->resolveClientLocation($client);

        $technicians = Employee::where('status', 'aktif')
            ->where(function ($q) {
                $q->where('employee_type', 'technician')
                    ->orWhere('specialization', 'like', '%teknisi%')
                    ->orWhere('specialization', 'like', '%field%')
                    ->orWhereHas('designation', fn($d) => $d->where('name', 'like', '%teknisi%'));
            })
            ->whereDoesntHave('workOrders', function ($q) {
                $q->whereIn('status', ['in_progress', 'en_route', 'assigned']);
            })
            ->with('workOrders')
            ->get();

        if ($technicians->isEmpty()) {
            return ['error' => 'Tidak ada teknisi tersedia'];
        }

        $scored = [];
        foreach ($technicians as $tech) {
            $van = TechnicianVan::where('technician_id', $tech->id)
                ->where('is_active', true)
                ->first();

            $distanceKm = 999;
            if ($van && $van->current_location_lat && $van->current_location_lng && $clientLat['lat']) {
                $distanceKm = $this->haversine(
                    $van->current_location_lat, $van->current_location_lng,
                    $clientLat['lat'], $clientLat['lng']
                );
            }

            $completedCount = WorkOrder::where('technician_id', $tech->id)
                ->whereIn('status', ['completed', 'verified'])
                ->count();

            $avgRating = WorkOrder::where('technician_id', $tech->id)
                ->whereNotNull('customer_rating')
                ->avg('customer_rating') ?? 3;

            $matchScore = $this->calculateMatchScore($distanceKm, $completedCount, (float) $avgRating);

            $scored[] = [
                'technician_id' => $tech->id,
                'name' => $tech->first_name . ' ' . $tech->last_name,
                'distance_km' => round($distanceKm, 2),
                'travel_time_minutes' => round(($distanceKm / 30) * 60),
                'completed_count' => $completedCount,
                'avg_rating' => round((float) $avgRating, 1),
                'match_score' => round($matchScore, 2),
            ];
        }

        usort($scored, fn($a, $b) => $b['match_score'] <=> $a['match_score']);
        return $scored;
    }

    public function checkIn(WorkOrder $wo, float $lat, float $lng, ?string $photoBase64 = null): void
    {
        $wo->update([
            'gps_checkin_lat' => $lat,
            'gps_checkin_lng' => $lng,
            'actual_start' => now(),
            'status' => 'in_progress',
        ]);

        if ($photoBase64) {
            $path = $this->saveBase64Photo($photoBase64, 'work-orders/before', $wo->id . '-before');
            $wo->update(['photo_before_path' => $path]);
        }

        if ($wo->technician_id) {
            TechnicianVan::where('technician_id', $wo->technician_id)
                ->where('is_active', true)
                ->update([
                    'current_location_lat' => $lat,
                    'current_location_lng' => $lng,
                    'last_location_update' => now(),
                ]);
        }
    }

    public function checkOut(WorkOrder $wo, float $lat, float $lng, ?string $photoBase64 = null): void
    {
        $distanceKm = 0;
        if ($wo->gps_checkin_lat && $wo->gps_checkin_lng) {
            $distanceKm = $this->haversine(
                $wo->gps_checkin_lat, $wo->gps_checkin_lng, $lat, $lng
            );
        }

        $laborHours = 0;
        if ($wo->actual_start) {
            $laborHours = round(now()->diffInMinutes($wo->actual_start) / 60, 2);
        }

        $wo->update([
            'gps_checkout_lat' => $lat,
            'gps_checkout_lng' => $lng,
            'actual_end' => now(),
            'travel_distance_km' => $distanceKm,
            'labor_hours' => $laborHours,
            'status' => 'completed',
        ]);

        if ($photoBase64) {
            $path = $this->saveBase64Photo($photoBase64, 'work-orders/after', $wo->id . '-after');
            $wo->update(['photo_after_path' => $path]);
        }
    }

    public function complete(WorkOrder $wo, string $resolution, string $signatureBase64, string $photoBase64): void
    {
        $signaturePath = $this->saveBase64Photo($signatureBase64, 'work-orders/signatures', $wo->id . '-signature');
        $photoPath = $this->saveBase64Photo($photoBase64, 'work-orders/after', $wo->id . '-final');

        $partsCost = WorkOrderPart::where('work_order_id', $wo->id)->sum('subtotal');
        $totalCost = $partsCost + ($wo->service_charge ?? 0);

        $wo->update([
            'resolution' => $resolution,
            'customer_signature_path' => $signaturePath,
            'photo_after_path' => $photoPath,
            'actual_end' => now(),
            'parts_cost' => $partsCost,
            'total_cost' => $totalCost,
            'status' => 'completed',
        ]);
    }

    public function generateInvoice(WorkOrder $wo): ?Invoice
    {
        if ($wo->invoice_id) {
            return Invoice::find($wo->invoice_id);
        }

        if (!$wo->parts_cost && !$wo->service_charge) {
            return null;
        }

        $invoice = DB::transaction(function () use ($wo) {
            $invoiceNumber = 'INV-FS-' . date('Ymd') . '-' . strtoupper(Str::random(4));

            $invoice = Invoice::create([
                'company_id' => $wo->company_id,
                'invoice_number' => $invoiceNumber,
                'invoice_type' => 'sales',
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'reference_entity' => 'work_order',
                'reference_id' => $wo->id,
                'subtotal' => $wo->parts_cost + $wo->service_charge,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $wo->parts_cost + $wo->service_charge,
                'paid_amount' => 0,
                'remaining_amount' => $wo->parts_cost + $wo->service_charge,
                'status' => 'sent',
                'notes' => "Field Service: {$wo->wo_number} — {$wo->description}",
            ]);

            if ($wo->service_charge > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => "Biaya Jasa — {$wo->wo_number}",
                    'quantity' => 1,
                    'unit_price' => $wo->service_charge,
                    'amount' => $wo->service_charge,
                ]);
            }

            $parts = WorkOrderPart::where('work_order_id', $wo->id)->get();
            foreach ($parts as $part) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => "Suku Cadang: {$part->product->name}",
                    'quantity' => $part->quantity,
                    'unit_price' => $part->unit_price,
                    'amount' => $part->subtotal,
                ]);
            }

            $wo->update(['invoice_id' => $invoice->id]);
            return $invoice;
        });

        return $invoice;
    }

    public function checkVanStock(): array
    {
        $results = ['alerts' => [], 'ok' => 0];

        $lowStock = VanInventory::whereColumn('quantity', '<=', 'reorder_point')
            ->with(['van.technician', 'product'])
            ->get();

        foreach ($lowStock as $inv) {
            $results['alerts'][] = [
                'van_id' => $inv->van_id,
                'license_plate' => $inv->van->license_plate ?? 'N/A',
                'technician' => $inv->van->technician->first_name ?? 'Unknown',
                'product' => $inv->product->name,
                'current_qty' => $inv->quantity,
                'reorder_point' => $inv->reorder_point,
            ];
        }

        $results['ok'] = VanInventory::whereColumn('quantity', '>', 'reorder_point')->count();

        return $results;
    }

    public function restockVan(TechnicianVan $van, array $items): void
    {
        DB::transaction(function () use ($van, $items) {
            foreach ($items as $item) {
                $inv = VanInventory::firstOrNew([
                    'van_id' => $van->id,
                    'product_id' => $item['product_id'],
                ]);
                $inv->quantity = ($inv->quantity ?? 0) + $item['quantity'];
                $inv->last_restock_date = now();
                $inv->save();
            }
        });
    }

    public function deductVanStock(WorkOrderPart $part): void
    {
        if (!$part->from_van_stock) {
            return;
        }

        $wo = $part->workOrder;
        if (!$wo || !$wo->technician_id) {
            return;
        }

        $van = TechnicianVan::where('technician_id', $wo->technician_id)
            ->where('is_active', true)
            ->first();

        if (!$van) {
            return;
        }

        $inv = VanInventory::where('van_id', $van->id)
            ->where('product_id', $part->product_id)
            ->first();

        if ($inv && $inv->quantity >= $part->quantity) {
            $inv->quantity -= $part->quantity;
            $inv->save();
        } else {
            Log::warning("Van stock tidak cukup: van={$van->id}, product={$part->product_id}, need={$part->quantity}, have=" . ($inv->quantity ?? 0));
        }
    }

    public function getFirstTimeFixRate(int $technicianId, string $period): array
    {
        $query = WorkOrder::where('technician_id', $technicianId)
            ->whereIn('status', ['completed', 'verified']);

        $query = $this->applyPeriodFilter($query, $period);
        $total = $query->count();

        $fixedFirst = $query->clone()->whereDoesntHave('parts')->count();

        $previousQuery = WorkOrder::where('technician_id', $technicianId)
            ->whereIn('status', ['completed', 'verified']);
        $previousQuery = $this->applyPeriodFilter($previousQuery, $period, true);
        $prevTotal = $previousQuery->count();
        $prevFixed = $previousQuery->clone()->whereDoesntHave('parts')->count();

        $rate = $total > 0 ? round(($fixedFirst / $total) * 100, 1) : 0;
        $prevRate = $prevTotal > 0 ? round(($prevFixed / $prevTotal) * 100, 1) : 0;
        $trend = $rate - $prevRate;

        return [
            'total_work_orders' => $total,
            'fixed_first_visit' => $fixedFirst,
            'rate_percent' => $rate,
            'trend' => round($trend, 1),
        ];
    }

    public function getTechnicianKpi(int $technicianId, string $period): array
    {
        $query = WorkOrder::where('technician_id', $technicianId)
            ->whereIn('status', ['completed', 'verified']);

        $query = $this->applyPeriodFilter($query, $period);

        $completed = $query->count();
        $avgTravel = $query->avg('travel_distance_km') ?? 0;
        $avgService = $query->avg('labor_hours') ?? 0;
        $avgRating = $query->whereNotNull('customer_rating')->avg('customer_rating') ?? 0;
        $revenue = $query->sum('total_cost');

        $ftfr = $this->getFirstTimeFixRate($technicianId, $period);

        return [
            'completed' => $completed,
            'avg_travel_time' => round($avgTravel, 1),
            'avg_service_time' => round($avgService, 1),
            'first_time_fix_rate' => $ftfr['rate_percent'],
            'customer_rating' => round((float) $avgRating, 1),
            'revenue_generated' => round((float) $revenue, 2),
        ];
    }

    public function getContractProfitability(ServiceContract $contract): array
    {
        $totalBilling = $contract->billing_amount;
        $workOrders = WorkOrder::where('service_contract_id', $contract->id)
            ->whereIn('status', ['completed', 'verified'])
            ->get();

        $totalCost = $workOrders->sum('parts_cost');
        $totalRevenue = $workOrders->sum('service_charge') + $workOrders->sum('parts_cost');
        $woCount = $workOrders->count();

        $billingCyclesPerYear = match ($contract->billing_cycle) {
            'monthly' => 12,
            'quarterly' => 4,
            'annually' => 1,
            default => 12,
        };
        $annualBilling = $totalBilling * $billingCyclesPerYear;

        $profitMargin = $annualBilling > 0
            ? round((($annualBilling - $totalCost) / $annualBilling) * 100, 1)
            : 0;

        return [
            'contract_id' => $contract->id,
            'contract_number' => $contract->contract_number,
            'annual_billing' => $annualBilling,
            'total_parts_cost' => round((float) $totalCost, 2),
            'total_revenue' => round((float) $totalRevenue, 2),
            'work_orders_completed' => $woCount,
            'profit_margin_percent' => $profitMargin,
        ];
    }

    public function optimizeDailySchedule(int $technicianId): array
    {
        $orders = WorkOrder::where('technician_id', $technicianId)
            ->where('status', 'assigned')
            ->whereDate('scheduled_start', now())
            ->orderBy('priority')
            ->get();

        if ($orders->isEmpty()) {
            return [];
        }

        $van = TechnicianVan::where('technician_id', $technicianId)
            ->where('is_active', true)
            ->first();

        $startLat = $van->current_location_lat ?? 0;
        $startLng = $van->current_location_lng ?? 0;

        $points = [];
        foreach ($orders as $wo) {
            if ($wo->gps_checkin_lat) {
                $points[] = [
                    'wo_id' => $wo->id,
                    'wo_number' => $wo->wo_number,
                    'lat' => $wo->gps_checkin_lat,
                    'lng' => $wo->gps_checkin_lng,
                ];
            } else {
                $points[] = [
                    'wo_id' => $wo->id,
                    'wo_number' => $wo->wo_number,
                    'lat' => $startLat + (mt_rand(-100, 100) / 10000),
                    'lng' => $startLng + (mt_rand(-100, 100) / 10000),
                ];
            }
        }

        $optimized = $this->nearestNeighborRoute($startLat, $startLng, $points);

        $sequence = 1;
        foreach ($optimized as &$pt) {
            $pt['sequence'] = $sequence++;
        }

        return $optimized;
    }

    public function attachChecklistToWorkOrder(WorkOrder $wo, int $companyId): void
    {
        $checklists = ServiceChecklist::where('company_id', $companyId)
            ->where('service_type', $wo->service_type)
            ->where('is_active', true)
            ->with('items')
            ->get();

        foreach ($checklists as $checklist) {
            foreach ($checklist->items as $item) {
                WorkOrderChecklistItem::create([
                    'work_order_id' => $wo->id,
                    'checklist_item_id' => $item->id,
                    'is_checked' => false,
                ]);
            }
        }
    }

    public function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    protected function generateWoNumber(int $companyId): string
    {
        $prefix = 'WO-' . date('Ymd');
        $count = WorkOrder::where('wo_number', 'like', $prefix . '%')->count();
        return $prefix . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    protected function calculateNextServiceDate(\Illuminate\Support\Carbon $current, string $frequency): \Illuminate\Support\Carbon
    {
        return match ($frequency) {
            'weekly' => $current->addWeek(),
            'biweekly' => $current->addWeeks(2),
            'monthly' => $current->addMonth(),
            'quarterly' => $current->addMonths(3),
            default => $current->addMonth(),
        };
    }

    protected function resolveClientLocation($client): array
    {
        $lat = null;
        $lng = null;

        if (method_exists($client, 'latitude') && $client->latitude) {
            $lat = $client->latitude;
            $lng = $client->longitude;
        }

        return ['lat' => $lat, 'lng' => $lng];
    }

    protected function calculateMatchScore(float $distanceKm, int $completedCount, float $avgRating): float
    {
        $distanceScore = max(0, 100 - ($distanceKm * 2));
        $experienceScore = min(100, $completedCount * 2);
        $ratingScore = ($avgRating / 5) * 100;
        return ($distanceScore * 0.4) + ($experienceScore * 0.3) + ($ratingScore * 0.3);
    }

    protected function nearestNeighborRoute(float $startLat, float $startLng, array $points): array
    {
        if (empty($points)) return [];

        $unvisited = $points;
        $route = [];
        $currentLat = $startLat;
        $currentLng = $startLng;

        while (!empty($unvisited)) {
            $nearest = null;
            $nearestDist = PHP_FLOAT_MAX;

            foreach ($unvisited as $i => $pt) {
                $dist = $this->haversine($currentLat, $currentLng, $pt['lat'], $pt['lng']);
                if ($dist < $nearestDist) {
                    $nearestDist = $dist;
                    $nearest = $i;
                }
            }

            $route[] = array_merge($unvisited[$nearest], ['distance_from_prev_km' => round($nearestDist, 2)]);
            $currentLat = $unvisited[$nearest]['lat'];
            $currentLng = $unvisited[$nearest]['lng'];
            unset($unvisited[$nearest]);
            $unvisited = array_values($unvisited);
        }

        return $route;
    }

    protected function applyPeriodFilter($query, string $period, bool $previous = false)
    {
        if ($previous) {
            return match ($period) {
                'today' => $query->whereBetween('created_at', [now()->subDays(2)->startOfDay(), now()->subDay()->endOfDay()]),
                'week' => $query->whereBetween('created_at', [now()->subWeeks(2)->startOfWeek(), now()->subWeek()->endOfWeek()]),
                'month' => $query->whereBetween('created_at', [now()->subMonths(2)->startOfMonth(), now()->subMonth()->endOfMonth()]),
                'year' => $query->whereBetween('created_at', [now()->subYears(2)->startOfYear(), now()->subYear()->endOfYear()]),
                default => $query->whereBetween('created_at', [now()->subMonths(2)->startOfMonth(), now()->subMonth()->endOfMonth()]),
            };
        }

        return match ($period) {
            'today' => $query->whereDate('created_at', now()),
            'week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
            'year' => $query->whereYear('created_at', now()->year),
            default => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
        };
    }

    protected function saveBase64Photo(string $base64, string $directory, string $filename): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $matches)) {
            $ext = $matches[1];
            $data = substr($base64, strpos($base64, ',') + 1);
            $data = base64_decode($data);

            $storagePath = storage_path("app/public/{$directory}");
            if (!is_dir($storagePath)) {
                mkdir($storagePath, 0755, true);
            }

            $fullPath = "{$directory}/{$filename}.". ($ext === 'svg+xml' ? 'svg' : $ext);
            file_put_contents(storage_path("app/public/{$fullPath}"), $data);

            return $fullPath;
        }

        return '';
    }
}
