<?php

namespace App\Services;

use App\Models\DeliveryItem;
use App\Models\DeliveryOrder;
use App\Models\FleetGpsTrack;
use App\Models\ColdChainLog;
use App\Models\Invoice;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LogisticsIntegrationService
{
    /**
     * Buat Delivery Order otomatis dari transaksi POS.
     */
    public function createDeliveryFromPos(PosTransaction $transaction): DeliveryOrder
    {
        $existing = DeliveryOrder::where('pos_transaction_id', $transaction->id)->first();
        if ($existing) {
            return $existing;
        }

        $customerAddress = $transaction->member?->address
            ?? ($transaction->member?->name ?? 'Customer')
            . ' | No. Transaksi: ' . $transaction->receipt_number;

        return DB::transaction(function () use ($transaction, $customerAddress) {
            $delivery = DeliveryOrder::create([
                'company_id' => $transaction->company_id,
                'pos_transaction_id' => $transaction->id,
                'customer_name' => $transaction->member?->name ?? 'Walk-in Customer',
                'delivery_address' => $customerAddress,
                'delivery_date' => now(),
                'status' => 'pending',
                'notes' => 'Auto-generated dari transaksi POS #' . $transaction->receipt_number,
            ]);

            foreach ($transaction->items as $posItem) {
                DeliveryItem::create([
                    'delivery_order_id' => $delivery->id,
                    'product_id' => $posItem->product_id,
                    'quantity' => $posItem->quantity,
                    'unit_price' => $posItem->unit_price,
                    'subtotal' => $posItem->subtotal,
                ]);
            }

            return $delivery;
        });
    }

    /**
     * Delivery selesai → auto-update status invoice.
     * Jika invoice terhubung, tandai sebagai delivered.
     */
    public function onDeliveryComplete(DeliveryOrder $delivery): void
    {
        $delivery->update([
            'status' => 'delivered',
            'actual_arrival' => now(),
        ]);

        if ($delivery->invoice_id) {
            $invoice = $delivery->invoice;
            if ($invoice && $invoice->status === 'unpaid') {
                $invoice->update([
                    'status' => 'delivered',
                    'notes' => ($invoice->notes ? $invoice->notes . ' | ' : '')
                        . 'Telah dikirim: DO #' . $delivery->do_number,
                ]);
            }
        }

        // Jika ada POS transaction terkait, update payment_status
        if ($delivery->pos_transaction_id) {
            $posTransaction = $delivery->posTransaction;
            if ($posTransaction && $posTransaction->payment_status === 'pending') {
                $posTransaction->update([
                    'payment_status' => 'delivered',
                ]);
            }
        }
    }

    /**
     * GPS Fleet → auto-update estimasi kedatangan (ETA) delivery.
     * Cari delivery order terdekat berdasarkan koordinat GPS.
     */
    public function updateEtaFromGps(FleetGpsTrack $track): void
    {
        $nearbyDeliveries = DeliveryOrder::where('vehicle_id', $track->vehicle_id)
            ->where('status', 'in_transit')
            ->get();

        if ($nearbyDeliveries->isEmpty()) {
            return;
        }

        foreach ($nearbyDeliveries as $delivery) {
            $deliveryLat = $delivery->gps_lat;
            $deliveryLng = $delivery->gps_lng;

            if (!$deliveryLat || !$deliveryLng) {
                continue;
            }

            $distanceKm = $this->haversineDistance(
                $track->latitude,
                $track->longitude,
                $deliveryLat,
                $deliveryLng
            );

            $speedKmh = $track->speed_kmh ?? 40;

            if ($speedKmh > 0) {
                $estimatedMinutes = ($distanceKm / $speedKmh) * 60;
                $eta = Carbon::parse($track->recorded_at)->addMinutes((int) ceil($estimatedMinutes));

                $delivery->update([
                    'estimated_arrival' => $eta,
                    'gps_lat' => $deliveryLat,
                    'gps_lng' => $deliveryLng,
                ]);
            }
        }
    }

    /**
     * Cold chain breach → auto-create ticket helpdesk untuk investigasi.
     */
    public function onColdChainBreach(ColdChainLog $log): Ticket
    {
        $delivery = $log->deliveryOrder;

        $ticket = Ticket::create([
            'company_id' => $delivery?->company_id,
            'ticket_number' => 'TKT-CC-' . date('ym') . '-' . str_pad($log->id, 5, '0', STR_PAD_LEFT),
            'subject' => 'Pelanggaran Cold Chain - DO #' . ($delivery?->do_number ?? 'N/A'),
            'description' => 'Terjadi pelanggaran suhu cold chain pada pengiriman #'
                . ($delivery?->do_number ?? 'N/A') . "\n\n"
                . 'Suhu tercatat: ' . $log->temperature_celsius . '°C' . "\n"
                . 'Kelembaban: ' . ($log->humidity_percent ?? 'N/A') . '%' . "\n"
                . 'Waktu: ' . $log->recorded_at . "\n"
                . 'Sensor: ' . ($log->sensor_id ?? 'N/A') . "\n\n"
                . 'Detail pelanggaran: ' . ($log->breach_details ?? 'Tidak tersedia'),
            'priority' => 'high',
            'status' => 'open',
            'source' => 'iot_cold_chain',
            'due_date' => now()->addHours(2),
        ]);

        $log->update([
            'is_breached' => true,
            'breach_details' => ($log->breach_details ?? '')
                . ' Ticket #' . $ticket->ticket_number . ' dibuat pada ' . now()->toDateTimeString(),
        ]);

        return $ticket;
    }

    /**
     * Hitung jarak haversine antara dua titik koordinat (dalam km).
     */
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
}
