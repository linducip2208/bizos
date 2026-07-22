<?php

namespace App\Filament\Pages;

use App\Services\GamificationService;
use Filament\Pages\Page;

class GamificationDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-trophy';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.gamification-dashboard';

    protected static ?string $title = 'Dashboard Gamifikasi';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $slug = 'gamification';

    public static function getNavigationGroup(): ?string
    {
        return 'Gamifikasi';
    }

    public array $leaderboard = [];
    public array $topBadges = [];
    public array $pointsDistribution = [];
    public array $mostRecognized = [];
    public array $stats = [];
    public array $levelDistribution = [];
    public string $leaderboardPeriod = 'weekly';

    public function mount(): void
    {
        $this->leaderboardPeriod = request('period', 'weekly');
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(GamificationService::class);
        $companyId = auth()->user()->company_id;

        $this->leaderboard = $service->getLeaderboard('company', $this->leaderboardPeriod, 20);
        $this->pointsDistribution = $service->getPointsDistribution($companyId);
        $this->mostRecognized = $service->getMostRecognized($companyId, 10);
        $this->stats = $service->getCompanyGamificationStats($companyId);

        $badgeCounts = \App\Models\UserBadge::whereHas('user', fn($q) => $q->where('company_id', $companyId))
            ->select('badge_id', \DB::raw('COUNT(*) as count'))
            ->groupBy('badge_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->with('badge')
            ->get()
            ->map(fn($r) => [
                'name' => $r->badge->name ?? 'Unknown',
                'icon' => $r->badge->icon ?? 'heroicon-o-star',
                'color' => $r->badge->color ?? 'indigo',
                'count' => $r->count,
            ])
            ->toArray();

        $this->topBadges = $badgeCounts;

        $levels = ['Bronze' => 0, 'Silver' => 0, 'Gold' => 0, 'Platinum' => 0, 'Diamond' => 0, 'Legend' => 0];
        $users = \App\Models\User::where('company_id', $companyId)->where('is_active', true)->get();
        foreach ($users as $user) {
            $level = $service->getUserLevel($user->id);
            $levels[$level['title']] = ($levels[$level['title']] ?? 0) + 1;
        }
        $this->levelDistribution = $levels;
    }

    public function setPeriod(string $period): void
    {
        $this->leaderboardPeriod = $period;
        $this->loadData();
    }
}
