<?php

namespace App\Filament\Pages;

use App\Models\ConsentRecord;
use App\Models\DataBreach;
use App\Models\DpiaAssessment;
use App\Models\DataErasureLog;
use Filament\Pages\Page;

class PdpDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 100;

    protected static ?string $title = 'Dashboard PDP';

    protected static ?string $navigationLabel = 'Dashboard PDP';

    protected static ?string $slug = 'kepatuhan/pdp';

    protected static string $view = 'filament.pages.kepatuhan.pdp-dashboard';

    public array $complianceReport = [];
    public array $consentStats = [];
    public array $breachStats = [];
    public array $dpiaStats = [];
    public array $erasureStats = [];
    public array $recentBreaches = [];
    public int $lateNotifications = 0;

    public static function getNavigationGroup(): ?string
    {
        return 'Kepatuhan';
    }

    public function mount(): void
    {
        $this->complianceReport = app(\App\Services\PdpComplianceService::class)->generateComplianceReport();

        $this->consentStats = [
            'total' => ConsentRecord::count(),
            'active' => ConsentRecord::where('status', 'active')->count(),
            'withdrawn' => ConsentRecord::where('status', 'withdrawn')->count(),
            'expired' => ConsentRecord::where('status', 'expired')->count(),
        ];

        $this->breachStats = [
            'total' => DataBreach::count(),
            'open' => DataBreach::where('status', 'open')->count(),
            'investigating' => DataBreach::where('status', 'investigating')->count(),
            'resolved' => DataBreach::whereIn('status', ['resolved', 'closed'])->count(),
        ];

        $this->lateNotifications = DataBreach::where('status', '!=', 'closed')
            ->get()
            ->filter(fn($b) => app(\App\Services\PdpComplianceService::class)->isLateBreachNotification($b))
            ->count();

        $this->dpiaStats = [
            'total' => DpiaAssessment::count(),
            'draft' => DpiaAssessment::where('status', 'draft')->count(),
            'in_review' => DpiaAssessment::where('status', 'in_review')->count(),
            'approved' => DpiaAssessment::where('status', 'approved')->count(),
        ];

        $this->erasureStats = [
            'total' => DataErasureLog::count(),
            'pending' => DataErasureLog::where('status', 'pending')->count(),
            'completed' => DataErasureLog::where('status', 'completed')->count(),
        ];

        $this->recentBreaches = DataBreach::orderBy('discovered_at', 'desc')
            ->limit(10)
            ->get()
            ->toArray();
    }
}
