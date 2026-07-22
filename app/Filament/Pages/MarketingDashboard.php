<?php

namespace App\Filament\Pages;

use App\Models\EmailCampaign;
use App\Models\LandingPage;
use App\Models\Lead;
use App\Models\LeadActivityLog;
use App\Services\LeadScoringService;
use Carbon\Carbon;
use Filament\Pages\Page;

class MarketingDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?int $navigationSort = 1300;

    protected static ?string $title = 'Dashboard Marketing';

    protected static string $view = 'filament.pages.marketing-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return 'Marketing';
    }

    public array $campaignStats = [];
    public array $leadFunnel = [];
    public array $recentActivities = [];
    public array $landingPageStats = [];

    public function mount(): void
    {
        $this->loadCampaignStats();
        $this->loadLeadFunnel();
        $this->loadRecentActivities();
        $this->loadLandingPageStats();
    }

    protected function loadCampaignStats(): void
    {
        $totalCampaigns = EmailCampaign::count();
        $activeCampaigns = EmailCampaign::whereIn('status', ['sending', 'sent'])->count();
        $totalSent = EmailCampaign::sum('sent_count');
        $totalOpened = EmailCampaign::sum('opened_count');
        $totalClicked = EmailCampaign::sum('clicked_count');
        $avgOpenRate = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 2) : 0;
        $avgClickRate = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 2) : 0;

        $this->campaignStats = [
            'total_campaigns' => $totalCampaigns,
            'active_campaigns' => $activeCampaigns,
            'total_sent' => $totalSent,
            'total_opened' => $totalOpened,
            'total_clicked' => $totalClicked,
            'avg_open_rate' => $avgOpenRate,
            'avg_click_rate' => $avgClickRate,
        ];

        $this->landingPageStats = [
            'total_pages' => LandingPage::count(),
            'published_pages' => LandingPage::where('status', 'published')->count(),
            'draft_pages' => LandingPage::where('status', 'draft')->count(),
        ];
    }

    protected function loadLeadFunnel(): void
    {
        $scoringService = app(LeadScoringService::class);
        $leads = Lead::all();

        $hot = 0;
        $warm = 0;
        $cold = 0;

        foreach ($leads as $lead) {
            $grade = $scoringService->getLeadGrade($lead);
            match ($grade) {
                'hot' => $hot++,
                'warm' => $warm++,
                'cold' => $cold++,
                default => null,
            };
        }

        $this->leadFunnel = [
            'total' => $leads->count(),
            'hot' => $hot,
            'warm' => $warm,
            'cold' => $cold,
            'new_this_week' => Lead::where('created_at', '>=', Carbon::now()->subWeek())->count(),
            'new_this_month' => Lead::where('created_at', '>=', Carbon::now()->subMonth())->count(),
        ];
    }

    protected function loadRecentActivities(): void
    {
        $this->recentActivities = LeadActivityLog::with('lead')
            ->latest('created_at')
            ->limit(20)
            ->get()
            ->map(function ($log) {
                return [
                    'lead_name' => $log->lead ? trim($log->lead->first_name . ' ' . $log->lead->last_name) : 'Unknown',
                    'lead_email' => $log->lead?->email,
                    'activity_type' => $log->activity_type,
                    'activity_label' => match ($log->activity_type) {
                        'email_opened' => 'Buka Email',
                        'email_clicked' => 'Klik Email',
                        'page_visited' => 'Kunjungi Halaman',
                        'form_submitted' => 'Submit Form',
                        'wa_replied' => 'Balas WA',
                        'deal_created' => 'Deal Dibuat',
                        default => $log->activity_type,
                    },
                    'metadata' => $log->metadata,
                    'created_at' => $log->created_at?->diffForHumans(),
                ];
            })
            ->toArray();
    }

    protected function loadLandingPageStats(): void
    {
        // loaded in loadCampaignStats for now
    }
}
