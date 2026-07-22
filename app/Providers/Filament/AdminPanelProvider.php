<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\HtmlString;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->favicon(asset('favicon.ico'))
            ->brandName('BizOS')
            ->brandLogoHeight('2rem')
            ->brandLogo(fn () => new HtmlString('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="w-8 h-8"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>'))
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('15.5rem')
            ->collapsedSidebarWidth('4rem')
            ->topbar(true)
            ->globalSearch(true)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\SalesStatsOverview::class,
                \App\Filament\Widgets\RecentEmployees::class,
                \App\Filament\Widgets\AttendanceToday::class,
                \App\Filament\Widgets\PendingApprovals::class,
                \App\Filament\Widgets\RevenueChart::class,
                \App\Filament\Widgets\MeetingStatsOverview::class,
                \App\Filament\Widgets\BpmnStatsOverview::class,
                \App\Filament\Widgets\BlockchainStatsOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                \App\Http\Middleware\TrackRecentlyViewed::class,
                \App\Http\Middleware\ThemeMiddleware::class,
            ])
            ->navigationGroups([
                NavigationGroup::make("\u{1F3E0} Dashboard & Reporting")->collapsed(false),
                NavigationGroup::make("\u{1F3E2} Organisasi")->collapsed(false),
                NavigationGroup::make("\u{1F465} Human Capital")->collapsed(false),
                NavigationGroup::make("\u{1F4B0} Payroll")->collapsed(false),
                NavigationGroup::make("\u{1F4B5} Finance & Accounting")->collapsed(false),
                NavigationGroup::make("\u{1F4E6} Product & Inventory")->collapsed(false),
                NavigationGroup::make("\u{1F4C8} Sales & CRM")->collapsed(true),
                NavigationGroup::make("\u{1F4CB} Project Management")->collapsed(true),
                NavigationGroup::make("\u{1F4AC} Collaboration")->collapsed(true),
                NavigationGroup::make("\u{1F6D2} POS & Retail")->collapsed(true),
                NavigationGroup::make("\u{1F393} Learning")->collapsed(true),
                NavigationGroup::make("\u{1F3C6} Gamification")->collapsed(true),
                NavigationGroup::make("\u{1F3AB} Support")->collapsed(true),
                NavigationGroup::make("\u{1F916} AI Assistant")->collapsed(true),
                NavigationGroup::make("\u26A1 Automation & Workflow")->collapsed(true),
                NavigationGroup::make("\u{1F517} Integrations")->collapsed(true),
                NavigationGroup::make("\u{1F3ED} Industry")->collapsed(true),
                NavigationGroup::make("\u{1F331} ESG & Sustainability")->collapsed(true),
                NavigationGroup::make("\u{1F6E1}\uFE0F Compliance")->collapsed(true),
                NavigationGroup::make("\u{1F537} Blockchain")->collapsed(true),
                NavigationGroup::make("\u{1F4B3} Billing & Licensing")->collapsed(true),
                NavigationGroup::make("\u{1F9E9} Platform")->collapsed(true),
                NavigationGroup::make("\u2699\uFE0F Sistem")->collapsed(true),
            ])
            ->renderHook(
                'panels::topbar.start',
                fn (): string => view('filament.hooks.app-switcher-trigger')->render(),
            )
            ->renderHook(
                'panels::user-menu.before',
                fn (): string => view('filament.hooks.sandbox-badge')->render(),
            )
            ->renderHook(
                'panels::body.end',
                fn (): string => view('filament.hooks.body-end')->render(),
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
