<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use App\Models\VehicleFuelLog;
use App\Models\VehicleMaintenanceLog;
use Illuminate\Support\Collection;

class FleetService
{
    public function assignVehicle(Employee $employee, Vehicle $vehicle, ?int $byUserId = null): VehicleAssignment
    {
        $vehicle->update(['status' => 'in_use']);

        return VehicleAssignment::create([
            'vehicle_id' => $vehicle->id,
            'employee_id' => $employee->id,
            'assigned_at' => now(),
            'odometer_start' => $vehicle->last_odometer,
            'assigned_by' => $byUserId ?? auth()->id(),
        ]);
    }

    public function returnVehicle(VehicleAssignment $assignment, ?int $endOdometer = null): void
    {
        $odometer = $endOdometer ?? $assignment->vehicle->last_odometer;

        $assignment->update([
            'returned_at' => now(),
            'odometer_end' => $odometer,
        ]);

        $assignment->vehicle->update([
            'status' => 'available',
            'last_odometer' => max($assignment->vehicle->last_odometer, $odometer),
        ]);
    }

    public function logFuel(Vehicle $vehicle, array $data): VehicleFuelLog
    {
        $previousLog = VehicleFuelLog::where('vehicle_id', $vehicle->id)
            ->where('odometer', '<', $data['odometer'])
            ->orderByDesc('odometer')
            ->first();

        $efficiency = null;
        if ($previousLog && isset($data['liters']) && $data['liters'] > 0) {
            $distance = $data['odometer'] - $previousLog->odometer;
            if ($distance > 0) {
                $efficiency = round($distance / $data['liters'], 2);
            }
        }

        $vehicle->update(['last_odometer' => max($vehicle->last_odometer, $data['odometer'])]);

        return VehicleFuelLog::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $data['driver_id'] ?? null,
            'odometer' => $data['odometer'],
            'liters' => $data['liters'],
            'cost' => $data['cost'] ?? 0,
            'fuel_type' => $data['fuel_type'] ?? $vehicle->fuel_type,
            'station' => $data['station'] ?? null,
            'receipt_photo' => $data['receipt_photo'] ?? null,
            'fuel_efficiency' => $efficiency,
            'date' => $data['date'] ?? now()->toDateString(),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function scheduleMaintenance(Vehicle $vehicle, array $data): VehicleMaintenanceLog
    {
        return VehicleMaintenanceLog::create([
            'vehicle_id' => $vehicle->id,
            'type' => $data['type'],
            'description' => $data['description'],
            'cost' => $data['cost'] ?? 0,
            'vendor' => $data['vendor'] ?? null,
            'odometer_at' => $data['odometer_at'] ?? $vehicle->last_odometer,
            'next_odometer_due' => $data['next_odometer_due'] ?? null,
            'date' => $data['date'] ?? now()->toDateString(),
            'next_due_date' => $data['next_due_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'attachment' => $data['attachment'] ?? null,
        ]);
    }

    public function getMaintenanceDue(): Collection
    {
        return Vehicle::where('status', '!=', 'sold')
            ->where(function ($q) {
                $q->whereHas('maintenanceLogs', function ($sq) {
                    $sq->where('next_due_date', '<=', now()->addDays(14))
                       ->where('next_due_date', '>=', now());
                })->orWhere(function ($q2) {
                    $q2->whereDoesntHave('maintenanceLogs')
                       ->where('created_at', '<=', now()->subMonths(6));
                });
            })
            ->with(['maintenanceLogs' => fn($q) => $q->latest('date')->limit(1)])
            ->get();
    }

    public function getFuelEfficiency(Vehicle $vehicle): array
    {
        $logs = VehicleFuelLog::where('vehicle_id', $vehicle->id)
            ->whereNotNull('fuel_efficiency')
            ->where('date', '>=', now()->subMonths(6))
            ->orderBy('date')
            ->get();

        $monthly = $logs->groupBy(fn($log) => $log->date->format('Y-m'))
            ->map(fn($group) => [
                'month' => $group->first()->date->format('M Y'),
                'avg_efficiency' => round($group->avg('fuel_efficiency'), 2),
                'total_liters' => round($group->sum('liters'), 2),
                'total_cost' => round($group->sum('cost'), 2),
            ])
            ->values()
            ->toArray();

        return [
            'overall_avg' => round($logs->avg('fuel_efficiency') ?? 0, 2),
            'monthly' => $monthly,
            'total_cost' => round($logs->sum('cost'), 2),
            'total_liters' => round($logs->sum('liters'), 2),
        ];
    }
}
