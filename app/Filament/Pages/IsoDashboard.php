<?php

namespace App\Filament\Pages;

use App\Models\IsoRisk;
use App\Models\IsoIncident;
use App\Models\IsoAudit;
use App\Models\IsoPolicy;
use Filament\Pages\Page;

class IsoDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?int $navigationSort = 101;

    protected static ?string $title = 'Dashboard ISO 27001';

    protected static ?string $navigationLabel = 'Dashboard ISO';

    protected static ?string $slug = 'kepatuhan/iso';

    protected static string $view = 'filament.pages.kepatuhan.iso-dashboard';

    public array $riskStats = [];
    public array $incidentStats = [];
    public array $auditStats = [];
    public array $policyStats = [];
    public array $riskHeatmap = [];
    public array $soaSummary = [];
    public array $recentIncidents = [];
    public array $upcomingAudits = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Kepatuhan';
    }

    public function mount(): void
    {
        $this->riskStats = [
            'total' => IsoRisk::count(),
            'critical' => IsoRisk::where('risk_level', 'critical')->count(),
            'high' => IsoRisk::where('risk_level', 'high')->count(),
            'medium' => IsoRisk::where('risk_level', 'medium')->count(),
            'low' => IsoRisk::where('risk_level', 'low')->count(),
            'open' => IsoRisk::where('status', 'open')->count(),
            'treated' => IsoRisk::whereIn('status', ['treated', 'closed'])->count(),
        ];

        $this->incidentStats = [
            'total' => IsoIncident::count(),
            'open' => IsoIncident::where('status', 'open')->count(),
            'investigating' => IsoIncident::where('status', 'investigating')->count(),
            'resolved' => IsoIncident::whereIn('status', ['resolved', 'closed'])->count(),
            'critical_high' => IsoIncident::whereIn('severity', ['critical', 'high'])->count(),
        ];

        $this->auditStats = [
            'total' => IsoAudit::count(),
            'planned' => IsoAudit::where('status', 'planned')->count(),
            'in_progress' => IsoAudit::where('status', 'in_progress')->count(),
            'completed' => IsoAudit::where('status', 'completed')->count(),
            'pass_rate' => IsoAudit::where('status', 'completed')->count() > 0
                ? round(IsoAudit::where('result', 'pass')->count() / max(IsoAudit::where('status', 'completed')->count(), 1) * 100, 1)
                : 0,
        ];

        $this->policyStats = [
            'total' => IsoPolicy::count(),
            'active' => IsoPolicy::where('status', 'active')->count(),
            'draft' => IsoPolicy::where('status', 'draft')->count(),
            'compliance' => app(\App\Services\IsoComplianceService::class)->getPolicyCompliance(),
        ];

        $this->riskHeatmap = app(\App\Services\IsoComplianceService::class)->getRiskHeatmap();
        $this->soaSummary = app(\App\Services\IsoComplianceService::class)->generateSoa();

        $this->recentIncidents = IsoIncident::orderBy('detected_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();

        $this->upcomingAudits = IsoAudit::where('status', 'planned')
            ->where('planned_date', '>=', now())
            ->orderBy('planned_date')
            ->limit(5)
            ->get()
            ->toArray();
    }
}
