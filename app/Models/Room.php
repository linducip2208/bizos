<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'room_number',
        'room_type',
        'floor',
        'bed_type',
        'max_guests',
        'base_price',
        'weekend_price',
        'holiday_price',
        'description',
        'amenities',
        'status',
        'current_guest_name',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'weekend_price' => 'decimal:2',
        'holiday_price' => 'decimal:2',
        'amenities' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function bookings()
    {
        return $this->hasMany(RoomBooking::class);
    }
}
