<?php

namespace App\Filament\Pages;

use App\Models\Room;
use App\Models\RoomBooking;
use App\Models\GuestFolio;
use Filament\Pages\Page;

class HotelDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 700;

    protected string $view = 'filament.pages.hotel-dashboard';

    protected static ?string $title = 'Dashboard Hotel';

    public static function getNavigationGroup(): ?string
    {
        return '🏨 Perhotelan';
    }

    public array $rooms = [];
    public array $todayCheckIns = [];
    public array $todayCheckOuts = [];
    public int $totalRooms = 0;
    public int $occupiedRooms = 0;
    public int $availableRooms = 0;
    public int $dirtyRooms = 0;
    public int $maintenanceRooms = 0;
    public float $occupancyRate = 0;
    public float $revenueToday = 0;
    public float $revenueMtd = 0;

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $today = now()->format('Y-m-d');

        $rooms = Room::where('company_id', $companyId)->get();

        $this->rooms = $rooms->toArray();
        $this->totalRooms = $rooms->count();
        $this->occupiedRooms = $rooms->where('status', 'occupied')->count();
        $this->availableRooms = $rooms->where('status', 'available')->count();
        $this->dirtyRooms = $rooms->where('status', 'dirty')->count();
        $this->maintenanceRooms = $rooms->where('status', 'maintenance')->count();
        $this->occupancyRate = $this->totalRooms > 0
            ? round(($this->occupiedRooms / $this->totalRooms) * 100, 1)
            : 0;

        $this->todayCheckIns = RoomBooking::where('company_id', $companyId)
            ->where('check_in_date', $today)
            ->with('room')
            ->get()
            ->toArray();

        $this->todayCheckOuts = RoomBooking::where('company_id', $companyId)
            ->where('check_out_date', $today)
            ->with('room')
            ->get()
            ->toArray();

        $this->revenueToday = GuestFolio::whereHas('booking', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->whereDate('created_at', $today)->sum('grand_total');

        $this->revenueMtd = GuestFolio::whereHas('booking', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('grand_total');
    }
}
