<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\WorkOrder;
use App\Models\TechnicianVan;
use App\Models\ServiceContract;
use App\Models\Employee;
use App\Services\FieldServiceService;

class FieldServiceDashboard extends Page
{
    public static function getNavigationGroup(): ?string
    {
        return 'Field Service';
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Dashboard Field Service';

    protected static ?string $slug = 'fieldservice-dashboard';

    protected static string $view = 'filament.pages.field-service-dashboard';

    public array $stats = [];
    public array $todayOrders = [];
    public array $technicianLeaderboard = [];
    public array $vanLocations = [];
    public string $ftfrRate = '0';

    public function mount(): void
    {
        $service = app(FieldServiceService::class);
        $companyId = auth()->user()?->company_id;

        $this->stats = [
            'total_contracts' => ServiceContract::where('status', 'active')->count(),
            'open_work_orders' => WorkOrder::whereIn('status', ['open', 'assigned', 'en_route', 'in_progress'])->count(),
            'completed_today' => WorkOrder::whereDate('actual_end', now())->whereIn('status', ['completed', 'verified'])->count(),
            'total_technicians' => Employee::where('status', 'aktif')
                ->where(function ($q) {
                    $q->where('employee_type', 'technician')
                        ->orWhere('specialization', 'like', '%teknisi%')
                        ->orWhereHas('designation', fn($d) => $d->where('name', 'like', '%teknisi%'));
                })->count(),
        ];

        $this->todayOrders = WorkOrder::with(['client', 'technician', 'equipment'])
            ->whereDate('scheduled_start', now())
            ->orWhereDate('actual_start', now())
            ->latest()
            ->limit(20)
            ->get()
            ->toArray();

        $technicians = Employee::where('status', 'aktif')
            ->where(function ($q) {
                $q->where('employee_type', 'technician')
                    ->orWhere('specialization', 'like', '%teknisi%')
                    ->orWhereHas('designation', fn($d) => $d->where('name', 'like', '%teknisi%'));
            })
            ->limit(20)
            ->get();

        foreach ($technicians as $tech) {
            $kpi = $service->getTechnicianKpi($tech->id, 'month');
            $this->technicianLeaderboard[] = [
                'id' => $tech->id,
                'name' => $tech->first_name . ' ' . $tech->last_name,
                'completed' => $kpi['completed'],
                'rating' => $kpi['customer_rating'],
                'revenue' => $kpi['revenue_generated'],
            ];
        }

        usort($this->technicianLeaderboard, fn($a, $b) => $b['completed'] <=> $a['completed']);

        $this->vanLocations = TechnicianVan::with('technician')
            ->where('is_active', true)
            ->whereNotNull('current_location_lat')
            ->get()
            ->map(fn ($van) => [
                'id' => $van->id,
                'technician' => $van->technician->first_name ?? 'Unknown',
                'license_plate' => $van->license_plate,
                'lat' => $van->current_location_lat,
                'lng' => $van->current_location_lng,
                'updated' => $van->last_location_update?->diffForHumans(),
            ])
            ->toArray();

        $completedAll = WorkOrder::whereIn('status', ['completed', 'verified'])->count();
        $fixedFirst = WorkOrder::whereIn('status', ['completed', 'verified'])->whereDoesntHave('parts')->count();
        $this->ftfrRate = $completedAll > 0 ? round(($fixedFirst / $completedAll) * 100, 1) : '0';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
