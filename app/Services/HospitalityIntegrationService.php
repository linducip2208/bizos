<?php

namespace App\Services;

use App\Models\Client;
use App\Models\FolioItem;
use App\Models\GuestFolio;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Room;
use App\Models\RoomBooking;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HospitalityIntegrationService
{
    /**
     * Room service / tagihan restoran → PosTransaction yang terhubung ke folio tamu.
     */
    public function chargeToFolio(int $folioId, array $items): FolioItem
    {
        return DB::transaction(function () use ($folioId, $items) {
            $folio = GuestFolio::findOrFail($folioId);

            $posTransaction = PosTransaction::create([
                'company_id' => $folio->booking?->company_id,
                'receipt_number' => 'FOLIO-' . date('ymd') . '-' . str_pad($folioId, 6, '0', STR_PAD_LEFT),
                'transaction_date' => now(),
                'subtotal' => collect($items)->sum(fn($i) => ($i['quantity'] ?? 1) * ($i['unit_price'] ?? 0)),
                'discount_total' => 0,
                'tax_total' => 0,
                'grand_total' => collect($items)->sum(fn($i) => ($i['quantity'] ?? 1) * ($i['unit_price'] ?? 0)),
                'payment_status' => 'pending',
                'notes' => 'Tagihan ke folio #' . $folio->folio_number,
            ]);

            $totalAmount = 0;

            foreach ($items as $item) {
                $quantity = $item['quantity'] ?? 1;
                $price = $item['unit_price'] ?? 0;
                $amount = $quantity * $price;

                PosTransactionItem::create([
                    'transaction_id' => $posTransaction->id,
                    'product_id' => $item['product_id'] ?? null,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'subtotal' => $amount,
                ]);

                $totalAmount += $amount;
            }

            $folioItem = FolioItem::create([
                'folio_id' => $folio->id,
                'pos_transaction_id' => $posTransaction->id,
                'description' => $item['description'] ?? 'Charge POS ke folio',
                'amount' => $totalAmount,
                'charge_date' => now(),
            ]);

            // Update total folio
            $folio->total_service_charges += $totalAmount;
            $folio->grand_total = $folio->total_room_charges
                + $folio->total_service_charges
                + $folio->total_tax;
            $folio->balance_due = $folio->grand_total - $folio->deposit_paid;
            $folio->save();

            return $folioItem;
        });
    }

    /**
     * Settlement folio tamu → Invoice.
     * Buat Invoice dari total folio beserta record Payment.
     */
    public function settleFolio(GuestFolio $folio): Invoice
    {
        if ($folio->invoice_id) {
            return $folio->invoice;
        }

        return DB::transaction(function () use ($folio) {
            $booking = $folio->booking;

            $invoice = Invoice::create([
                'company_id' => $booking->company_id,
                'invoice_number' => 'INV-HTL-' . date('Ym') . '-' . str_pad($folio->id, 6, '0', STR_PAD_LEFT),
                'invoice_type' => 'hotel',
                'invoice_date' => now(),
                'due_date' => now(),
                'reference_entity' => GuestFolio::class,
                'reference_id' => $folio->id,
                'subtotal' => $folio->total_room_charges + $folio->total_service_charges,
                'discount_amount' => 0,
                'tax_amount' => $folio->total_tax,
                'total' => $folio->grand_total,
                'paid_amount' => 0,
                'remaining_amount' => $folio->grand_total,
                'status' => 'unpaid',
                'notes' => 'Penyelesaian folio #' . $folio->folio_number
                    . ' | Tamu: ' . $booking->guest_name
                    . ' | Kamar: ' . ($booking->room?->room_number ?? '-'),
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Biaya kamar (' . $booking->check_in_date?->format('d/m/Y')
                    . ' - ' . $booking->check_out_date?->format('d/m/Y') . ')',
                'quantity' => 1,
                'unit_price' => $folio->total_room_charges,
                'tax_rate' => 0,
                'amount' => $folio->total_room_charges,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Biaya layanan tambahan',
                'quantity' => 1,
                'unit_price' => $folio->total_service_charges,
                'tax_rate' => 0,
                'amount' => $folio->total_service_charges,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'PPN / Pajak',
                'quantity' => 1,
                'unit_price' => $folio->total_tax,
                'tax_rate' => 0,
                'amount' => $folio->total_tax,
            ]);

            // Record deposit sebagai payment
            if ($folio->deposit_paid > 0) {
                $payment = Payment::create([
                    'company_id' => $booking->company_id,
                    'payment_number' => 'PAY-FOLIO-' . date('ym') . '-' . str_pad($folio->id, 5, '0', STR_PAD_LEFT),
                    'payment_date' => now(),
                    'amount' => $folio->deposit_paid,
                    'reference_number' => $folio->folio_number,
                    'notes' => 'Deposit folio #' . $folio->folio_number,
                    'status' => 'confirmed',
                ]);

                $invoice->payments()->attach($payment->id, ['amount' => $folio->deposit_paid]);
                $invoice->paid_amount = $folio->deposit_paid;
                $invoice->remaining_amount = $folio->grand_total - $folio->deposit_paid;
                $invoice->status = $invoice->remaining_amount <= 0 ? 'paid' : 'partial';
                $invoice->save();

                $folio->payment_id = $payment->id;
            }

            $folio->invoice_id = $invoice->id;
            $folio->payment_status = 'settled';
            $folio->settled_at = now();
            $folio->save();

            return $invoice;
        });
    }

    /**
     * Booking dikonfirmasi → auto-block room.
     */
    public function onBookingConfirmed(RoomBooking $booking): void
    {
        $room = $booking->room;

        if ($room && $room->status !== 'occupied') {
            $room->update([
                'status' => 'occupied',
                'current_guest_name' => $booking->guest_name,
            ]);
        }

        $booking->update(['status' => 'checked_in']);
    }

    /**
     * Check-out → auto-set room ke dirty/needs cleaning.
     */
    public function onCheckout(RoomBooking $booking): void
    {
        $room = $booking->room;

        if ($room) {
            $room->update([
                'status' => 'dirty',
                'current_guest_name' => null,
            ]);
        }

        $booking->update(['status' => 'checked_out']);
    }

    /**
     * Booking dari OTA → auto-buat Client jika tamu baru.
     * Jika tamu berulang → link ke Client yang sudah ada.
     */
    public function syncOtaBooking(RoomBooking $booking): Client
    {
        if ($booking->client_id) {
            return $booking->client;
        }

        if ($booking->guest_email) {
            $existingClient = Client::where('email', $booking->guest_email)
                ->where('company_id', $booking->company_id)
                ->first();

            if ($existingClient) {
                $booking->update(['client_id' => $existingClient->id]);
                return $existingClient;
            }
        }

        $client = Client::create([
            'company_id' => $booking->company_id,
            'name' => $booking->guest_name,
            'email' => $booking->guest_email,
            'phone' => $booking->guest_phone,
            'source' => 'ota_' . ($booking->booking_source ?? 'direct'),
            'notes' => 'Tamu hotel - OTA Booking ID: ' . ($booking->ota_booking_id ?? '-')
                . ' | Check-in: ' . ($booking->check_in_date?->format('d/m/Y') ?? '-'),
        ]);

        $booking->update(['client_id' => $client->id]);

        return $client;
    }
}
