<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\GuestFolio;
use App\Models\FolioItem;
use App\Models\HotelService;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HospitalityService
{
    public function checkAvailability(Carbon $checkIn, Carbon $checkOut, ?string $roomType = null): array
    {
        $bookedRoomIds = RoomBooking::where(function ($query) use ($checkIn, $checkOut) {
            $query->whereBetween('check_in_date', [$checkIn, $checkOut])
                ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                ->orWhere(function ($q) use ($checkIn, $checkOut) {
                    $q->where('check_in_date', '<=', $checkIn)
                        ->where('check_out_date', '>=', $checkOut);
                });
        })
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->pluck('room_id');

        $query = Room::whereNotIn('id', $bookedRoomIds)
            ->where('status', 'available');

        if ($roomType) {
            $query->where('room_type', $roomType);
        }

        $available = $query->get();

        $byType = [];
        foreach ($available as $room) {
            $type = $room->room_type;
            $byType[$type][] = $room;
            $byType["{$type}_count"] = ($byType["{$type}_count"] ?? 0) + 1;
        }

        return [
            'available_rooms' => $available,
            'total_available' => $available->count(),
            'by_type' => $byType,
        ];
    }

    public function createBooking(array $data): RoomBooking
    {
        $room = Room::findOrFail($data['room_id']);
        $checkIn = Carbon::parse($data['check_in_date']);
        $checkOut = Carbon::parse($data['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);

        $isWeekend = $checkIn->isWeekend();
        $totalCharge = $nights * (float) ($isWeekend && $room->weekend_price ? $room->weekend_price : $room->base_price);

        $booking = RoomBooking::create([
            'company_id' => auth()->user()->company_id ?? $room->company_id,
            'room_id' => $room->id,
            'client_id' => $data['client_id'] ?? null,
            'guest_name' => $data['guest_name'],
            'guest_phone' => $data['guest_phone'] ?? null,
            'guest_email' => $data['guest_email'] ?? null,
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'adults' => $data['adults'] ?? 1,
            'children' => $data['children'] ?? 0,
            'booking_source' => $data['booking_source'] ?? 'direct',
            'ota_booking_id' => $data['ota_booking_id'] ?? null,
            'ota_commission_percent' => $data['ota_commission_percent'] ?? null,
            'total_room_charge' => $totalCharge,
            'status' => 'confirmed',
            'special_requests' => $data['special_requests'] ?? null,
        ]);

        $room->update([
            'status' => 'reserved',
            'current_guest_name' => $data['guest_name'],
        ]);

        return $booking;
    }

    public function checkIn(RoomBooking $booking): void
    {
        $booking->update(['status' => 'checked_in']);
        $booking->room->update([
            'status' => 'occupied',
            'current_guest_name' => $booking->guest_name,
        ]);
    }

    public function checkOut(RoomBooking $booking): void
    {
        $booking->update(['status' => 'checked_out']);
        $booking->room->update([
            'status' => 'dirty',
            'current_guest_name' => null,
        ]);

        $folio = $this->getOrCreateFolio($booking);
        $this->recalculateFolio($folio);
    }

    public function getOrCreateFolio(RoomBooking $booking): GuestFolio
    {
        if ($booking->folio) {
            return $booking->folio;
        }

        return GuestFolio::create([
            'booking_id' => $booking->id,
            'folio_number' => 'FOL-' . date('Ymd') . '-' . str_pad($booking->id, 5, '0', STR_PAD_LEFT),
            'total_room_charges' => $booking->total_room_charge,
            'total_service_charges' => 0,
            'total_tax' => 0,
            'grand_total' => $booking->total_room_charge,
            'deposit_paid' => 0,
            'balance_due' => $booking->total_room_charge,
            'payment_status' => 'pending',
        ]);
    }

    public function addCharge(GuestFolio $folio, array $data): FolioItem
    {
        $item = FolioItem::create([
            'folio_id' => $folio->id,
            'service_id' => $data['service_id'] ?? null,
            'pos_transaction_id' => $data['pos_transaction_id'] ?? null,
            'description' => $data['description'],
            'amount' => $data['amount'],
            'charge_date' => $data['charge_date'] ?? now(),
        ]);

        $this->recalculateFolio($folio);

        return $item;
    }

    public function recalculateFolio(GuestFolio $folio): void
    {
        $serviceCharges = $folio->folioItems()->sum('amount');
        $roomCharges = $folio->total_room_charges;
        $subtotal = $roomCharges + (float) $serviceCharges;
        $tax = $subtotal * 0.11;

        $folio->update([
            'total_service_charges' => $serviceCharges,
            'total_room_charges' => $roomCharges,
            'total_tax' => $tax,
            'grand_total' => $subtotal + $tax,
            'balance_due' => ($subtotal + $tax) - $folio->deposit_paid,
        ]);
    }

    public function settleFolio(GuestFolio $folio, int $paymentMethodId): Payment
    {
        $payment = Payment::create([
            'company_id' => $folio->booking->company_id,
            'payment_number' => 'PAY-FOL-' . $folio->folio_number,
            'payment_date' => now(),
            'payment_method_id' => $paymentMethodId,
            'amount' => $folio->balance_due,
            'reference_number' => $folio->folio_number,
            'notes' => 'Pembayaran folio ' . $folio->folio_number,
            'status' => 'confirmed',
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        $invoice = Invoice::create([
            'company_id' => $folio->booking->company_id,
            'invoice_number' => 'INV-' . $folio->folio_number,
            'invoice_type' => 'hospitality',
            'invoice_date' => now(),
            'due_date' => now(),
            'subtotal' => $folio->total_room_charges + $folio->total_service_charges,
            'tax_amount' => $folio->total_tax,
            'total' => $folio->grand_total,
            'paid_amount' => $folio->grand_total,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Biaya Kamar - Booking #' . $folio->booking_id,
            'quantity' => 1,
            'unit_price' => $folio->total_room_charges,
            'tax_rate' => 11,
            'amount' => $folio->total_room_charges,
        ]);

        if ($folio->total_service_charges > 0) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => 'Biaya Layanan - Folio ' . $folio->folio_number,
                'quantity' => 1,
                'unit_price' => $folio->total_service_charges,
                'tax_rate' => 11,
                'amount' => $folio->total_service_charges,
            ]);
        }

        $folio->update([
            'deposit_paid' => $folio->grand_total,
            'balance_due' => 0,
            'payment_status' => 'paid',
            'settled_at' => now(),
        ]);

        return $payment;
    }

    public function updateRoomStatus(Room $room, string $status): void
    {
        $room->update(['status' => $status]);

        if ($status === 'available') {
            $room->update(['current_guest_name' => null]);
        }
    }

    public function getRoomsNeedingCleaning(): Collection
    {
        return Room::where('status', 'dirty')->get();
    }

    public function syncOtaInventory(): void
    {
        // Simulated: push room availability to OTAs
        $rooms = Room::where('status', 'available')->get();
        $summary = [
            'total_rooms' => Room::count(),
            'available_rooms' => $rooms->count(),
            'synced_at' => now()->toIso8601String(),
            'channels' => ['traveloka', 'agoda', 'booking_com'],
        ];
    }

    public function pullOtaBookings(): array
    {
        return [];
    }
}
