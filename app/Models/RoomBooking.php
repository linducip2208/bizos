<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomBooking extends Model
{
    protected $fillable = [
        'company_id',
        'room_id',
        'client_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'check_in_date',
        'check_out_date',
        'adults',
        'children',
        'booking_source',
        'ota_booking_id',
        'ota_commission_percent',
        'total_room_charge',
        'status',
        'special_requests',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'ota_commission_percent' => 'decimal:2',
        'total_room_charge' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function folio()
    {
        return $this->hasOne(GuestFolio::class, 'booking_id');
    }
}
