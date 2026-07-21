<?php

namespace App\Services;

use App\Models\ColdChainLog;
use App\Models\DeliveryOrder;
use App\Models\DeliveryStop;
use App\Models\Employee;
use App\Models\FleetGpsTrack;
use App\Models\Invoice;
use App\Models\PosTransaction;
use App\Models\Product;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LogisticsService
{
    public function createDelivery(Invoice|PosTransaction $source): DeliveryOrder
    {
        $customerName = '';
        $items = [];

        if ($source instanceof Invoice) {
            $customerName = $source->client?->name ?? 'N/A';
            foreach ($source->invoiceItems as $item) {
                $items[] = [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit ?? 'pcs',
                ];
            }
        } elseif ($source instanceof PosTransaction) {
            $customerName = $source->member?->name ?? $source->customer_name ?? 'Walk-in';
            $customerName = match (true) {
                (bool) $source->member?->name => $source->member->name,
                default => $source->member?->name ?? 'Walk-in',
            };
            foreach ($source->items as $item) {
                $items[] = [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit ?? 'pcs',
                ];
            }
        }

        return DB::transaction(function () use ($source, $customerName, $items) {
            $delivery = DeliveryOrder::create([
                'company_id' => $source->company_id,
                'invoice_id' => $source instanceof Invoice ? $source->id : null,
                'pos_transaction_id' => $source instanceof PosTransaction ? $source->id : null,
                'customer_name' => $customerName,
                'delivery_address' => $source instanceof Invoice
                    ? ($source->client?->address ?? '')
                    : ($source->member?->address ?? ''),
                'delivery_date' => now()->addDay()->toDateString(),
                'status' => 'pending',
                'notes' => 'Dibuat otomatis dari ' . ($source instanceof Invoice ? 'Invoice #' . $source->invoice_number : 'POS #' . $source->receipt_number),
            ]);

            foreach ($items as $item) {
                $delivery->items()->create($item);
            }

            return $delivery;
        });
    }

    public function optimizeRoute(array $deliveryOrderIds): array
    {
        $orders = DeliveryOrder::with('stops')
            ->whereIn('id', $deliveryOrderIds)
            ->where('status', 'pending')
            ->get();

        if ($orders->isEmpty()) {
            return [];
        }

        $allStops = [];
        foreach ($orders as $order) {
            $stops = $order->stops;
            if ($stops->isEmpty()) {
                $allStops[] = [
                    'delivery_order_id' => $order->id,
                    'stop_id' => null,
                    'address' => $order->delivery_address,
                    'contact_name' => $order->customer_name,
                    'gps_lat' => $order->gps_lat,
                    'gps_lng' => $order->gps_lng,
                ];
            } else {
                foreach ($stops as $stop) {
                    $allStops[] = [
                        'delivery_order_id' => $order->id,
                        'stop_id' => $stop->id,
                        'address' => $stop->address,
                        'contact_name' => $stop->contact_name,
                        'gps_lat' => $stop->gps_lat,
                        'gps_lng' => $stop->gps_lng,
                    ];
                }
            }
        }

        $validStops = array_filter($allStops, fn($s) => $s['gps_lat'] && $s['gps_lng']);
        $noCoordsStops = array_filter($allStops, fn($s) => !$s['gps_lat'] || !$s['gps_lng']);

        $optimized = $this->nearestNeighbor($validStops);

        $result = [];
        $sequence = 1;

        foreach ($optimized as $stop) {
            $delivery = $orders->firstWhere('id', $stop['delivery_order_id']);
            $travelTime = 0;
            $eta = null;

            if ($sequence > 1 && isset($result[$sequence - 2])) {
                $prev = $result[$sequence - 2];
                $distance = $this->haversineDistance(
                    $prev['gps_lat'],
                    $prev['gps_lng'],
                    $stop['gps_lat'],
                    $stop['gps_lng']
                );
                $travelTime = (int) ceil($distance / 30 * 60);
                $eta = Carbon::parse($prev['estimated_arrival'] ?? now())->addMinutes($travelTime);
            } else {
                $eta = now()->addMinutes(15);
            }

            $result[] = [
                'sequence' => $sequence,
                'delivery_order_id' => $stop['delivery_order_id'],
                'do_number' => $delivery?->do_number,
                'stop_id' => $stop['stop_id'],
                'address' => $stop['address'],
                'contact_name' => $stop['contact_name'],
                'gps_lat' => $stop['gps_lat'],
                'gps_lng' => $stop['gps_lng'],
                'estimated_arrival' => $eta?->format('Y-m-d H:i:s'),
                'estimated_travel_minutes' => $travelTime,
            ];

            $sequence++;
        }

        foreach ($noCoordsStops as $stop) {
            $delivery = $orders->firstWhere('id', $stop['delivery_order_id']);
            $result[] = [
                'sequence' => $sequence,
                'delivery_order_id' => $stop['delivery_order_id'],
                'do_number' => $delivery?->do_number,
                'stop_id' => $stop['stop_id'],
                'address' => $stop['address'],
                'contact_name' => $stop['contact_name'],
                'gps_lat' => null,
                'gps_lng' => null,
                'estimated_arrival' => now()->addMinutes($sequence * 30)->format('Y-m-d H:i:s'),
                'estimated_travel_minutes' => 30,
            ];
            $sequence++;
        }

        foreach ($result as $item) {
            if ($item['stop_id']) {
                DeliveryStop::where('id', $item['stop_id'])->update([
                    'stop_sequence' => $item['sequence'],
                    'planned_arrival' => $item['estimated_arrival'],
                ]);
            } elseif (!$item['stop_id']) {
                $order = $orders->firstWhere('id', $item['delivery_order_id']);
                if ($order) {
                    $order->stops()->updateOrCreate(
                        ['delivery_order_id' => $order->id],
                        [
                            'stop_sequence' => $item['sequence'],
                            'address' => $item['address'],
                            'contact_name' => $item['contact_name'],
                            'planned_arrival' => $item['estimated_arrival'],
                        ]
                    );
                }
            }
        }

        return $result;
    }

    private function nearestNeighbor(array $stops): array
    {
        if (empty($stops)) {
            return [];
        }

        $unvisited = $stops;
        $ordered = [];
        $startIndex = 0;

        $ordered[] = $unvisited[$startIndex];
        unset($unvisited[$startIndex]);
        $unvisited = array_values($unvisited);

        $current = $ordered[0];

        while (!empty($unvisited)) {
            $nearestIdx = 0;
            $nearestDist = PHP_FLOAT_MAX;

            foreach ($unvisited as $idx => $stop) {
                $dist = $this->haversineDistance(
                    $current['gps_lat'],
                    $current['gps_lng'],
                    $stop['gps_lat'],
                    $stop['gps_lng']
                );
                if ($dist < $nearestDist) {
                    $nearestDist = $dist;
                    $nearestIdx = $idx;
                }
            }

            $current = $unvisited[$nearestIdx];
            $ordered[] = $current;
            unset($unvisited[$nearestIdx]);
            $unvisited = array_values($unvisited);
        }

        return $ordered;
    }

    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
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

    public function capturePod(DeliveryOrder $delivery, string $receiverName, string $signatureBase64, string $photoBase64): void
    {
        $signaturePath = null;
        $photoPath = null;

        if ($signatureBase64) {
            $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));
            $signaturePath = 'pod/signatures/' . $delivery->id . '_' . time() . '.png';
            \Storage::disk('public')->put($signaturePath, $signatureData);
        }

        if ($photoBase64) {
            $photoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photoBase64));
            $photoPath = 'pod/photos/' . $delivery->id . '_' . time() . '.jpg';
            \Storage::disk('public')->put($photoPath, $photoData);
        }

        $delivery->update([
            'status' => 'delivered',
            'actual_arrival' => now(),
            'receiver_name' => $receiverName,
            'receiver_signature_path' => $signaturePath,
            'pod_photo_path' => $photoPath,
        ]);
    }

    public function recordGpsTrack(Vehicle $vehicle, float $lat, float $lng, float $speed, float $heading): FleetGpsTrack
    {
        $driver = $vehicle->currentDriver()->first();

        return FleetGpsTrack::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver?->id,
            'latitude' => $lat,
            'longitude' => $lng,
            'speed_kmh' => $speed,
            'heading' => $heading,
            'ignition_status' => $speed > 0,
            'recorded_at' => now(),
        ]);
    }

    public function checkColdChain(ColdChainLog $log): bool
    {
        $delivery = $log->deliveryOrder()->with('items.product')->first();
        if (!$delivery) {
            return false;
        }

        $minTemp = 2;
        $maxTemp = 8;
        $hasColdChain = false;

        foreach ($delivery->items as $item) {
            if ($item->product && $item->product->category?->name) {
                $cat = strtolower($item->product->category->name);
                if (str_contains($cat, 'dingin') || str_contains($cat, 'beku') || str_contains($cat, 'frozen')) {
                    $hasColdChain = true;
                    $minTemp = str_contains($cat, 'beku') || str_contains($cat, 'frozen') ? -20 : 0;
                    $maxTemp = str_contains($cat, 'beku') || str_contains($cat, 'frozen') ? -10 : 5;
                }
            }
        }

        if (!$hasColdChain) {
            return false;
        }

        $isBreached = false;
        $breachDetails = [];

        if ($log->temperature_celsius < $minTemp) {
            $isBreached = true;
            $breachDetails[] = "Suhu terlalu rendah: {$log->temperature_celsius}°C (min: {$minTemp}°C)";
        }
        if ($log->temperature_celsius > $maxTemp) {
            $isBreached = true;
            $breachDetails[] = "Suhu terlalu tinggi: {$log->temperature_celsius}°C (max: {$maxTemp}°C)";
        }

        $log->update([
            'is_breached' => $isBreached,
            'breach_details' => $isBreached ? implode('; ', $breachDetails) : null,
        ]);

        return $isBreached;
    }

    public function getDeliveryPerformance(string $period): array
    {
        $date = match ($period) {
            'today' => now()->startOfDay(),
            'yesterday' => now()->subDay()->startOfDay(),
            'this_week' => now()->startOfWeek(),
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $deliveries = DeliveryOrder::where('delivery_date', '>=', $date)->get();

        $total = $deliveries->count();
        $onTime = $deliveries->filter(fn($d) => $d->status === 'delivered' && $d->actual_arrival && $d->estimated_arrival && $d->actual_arrival->lte($d->estimated_arrival))->count();
        $late = $deliveries->filter(fn($d) => $d->status === 'delivered' && $d->actual_arrival && $d->estimated_arrival && $d->actual_arrival->gt($d->estimated_arrival))->count();
        $failed = $deliveries->filter(fn($d) => in_array($d->status, ['failed', 'returned']))->count();
        $delivered = $deliveries->filter(fn($d) => $d->status === 'delivered' && $d->actual_arrival);

        $avgMinutes = 0;
        if ($delivered->isNotEmpty()) {
            $avgMinutes = round($delivered->avg(function ($d) {
                return $d->created_at->diffInMinutes($d->actual_arrival);
            }), 0);
        }

        return [
            'total_deliveries' => $total,
            'on_time' => $onTime,
            'late' => $late,
            'failed' => $failed,
            'in_progress' => $deliveries->whereIn('status', ['picked', 'in_transit'])->count(),
            'on_time_rate' => $total > 0 ? round(($onTime / $total) * 100, 1) : 0,
            'late_rate' => $total > 0 ? round(($late / $total) * 100, 1) : 0,
            'failed_rate' => $total > 0 ? round(($failed / $total) * 100, 1) : 0,
            'avg_delivery_time_minutes' => $avgMinutes,
        ];
    }

    public function getDriverPerformance(int $driverId, string $period): array
    {
        $date = match ($period) {
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $deliveries = DeliveryOrder::where('driver_id', $driverId)
            ->where('delivery_date', '>=', $date)
            ->get();

        $total = $deliveries->count();
        $onTime = $deliveries->filter(fn($d) => $d->status === 'delivered' && $d->actual_arrival && $d->estimated_arrival && $d->actual_arrival->lte($d->estimated_arrival))->count();
        $delivered = $deliveries->where('status', 'delivered');

        $totalDistance = 0;
        if ($delivered->isNotEmpty()) {
            $totalDistance = $delivered->sum(function ($d) {
                if (!$d->gps_lat || !$d->gps_lng) return 0;
                return $this->haversineDistance(
                    $d->vehicle?->assignments()?->whereNull('returned_at')?->first()?->gps_lat ?? 0,
                    $d->vehicle?->assignments()?->whereNull('returned_at')?->first()?->gps_lng ?? 0,
                    $d->gps_lat,
                    $d->gps_lng
                );
            });
        }

        return [
            'total_deliveries' => $total,
            'delivered' => $delivered->count(),
            'on_time' => $onTime,
            'on_time_rate' => $total > 0 ? round(($onTime / $total) * 100, 1) : 0,
            'avg_rating' => 4.5,
            'distance_km' => round($totalDistance, 1),
        ];
    }
}
