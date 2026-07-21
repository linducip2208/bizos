<?php

namespace App\Filament\Pages;

use App\Models\SodRule;
use App\Models\SodConflict;
use App\Models\User;
use Filament\Pages\Page;

class SodDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-no-symbol';

    protected static ?int $navigationSort = 102;

    protected static ?string $title = 'Dashboard SoD';

    protected static ?string $navigationLabel = 'Dashboard SoD';

    protected static ?string $slug = 'kepatuhan/sod';

    protected string $view = 'filament.pages.kepatuhan.sod-dashboard';

    public array $ruleStats = [];
    public array $conflictStats = [];
    public array $activeConflicts = [];
    public array $conflictMatrix = [];
    public int $usersScanned = 0;
    public int $totalConflicts = 0;

    public static function getNavigationGroup(): ?string
    {
        return 'Kepatuhan';
    }

    public function mount(): void
    {
        $this->ruleStats = [
            'total' => SodRule::count(),
            'active' => SodRule::where('is_active', true)->count(),
            'system_default' => SodRule::where('is_system_default', true)->count(),
            'by_risk' => [
                'critical' => SodRule::where('risk_level', 'critical')->count(),
                'high' => SodRule::where('risk_level', 'high')->count(),
                'medium' => SodRule::where('risk_level', 'medium')->count(),
                'low' => SodRule::where('risk_level', 'low')->count(),
            ],
        ];

        $this->conflictStats = [
            'total' => SodConflict::count(),
            'detected' => SodConflict::where('status', 'detected')->count(),
            'mitigated' => SodConflict::where('status', 'mitigated')->count(),
            'resolved' => SodConflict::where('status', 'resolved')->count(),
        ];

        $this->activeConflicts = SodConflict::with(['user', 'rule'])
            ->where('status', 'detected')
            ->orderBy('risk_level', 'desc')
            ->get()
            ->toArray();

        $this->conflictMatrix = app(\App\Services\SodService::class)->getConflictMatrix();

        $userCount = User::whereHas('role')->count();
        $this->usersScanned = $userCount;
        $this->totalConflicts = $this->conflictStats['detected'];
    }
}
