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
            ->pages([
                \App\Filament\Pages\EmailInbox::class,
                \App\Filament\Pages\SearchAnalytics::class,
            ])
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
                NavigationGroup::make('🏠 Dashboard & Reporting')->collapsed(false),
                NavigationGroup::make('🏢 Organisasi')->collapsed(false),
                NavigationGroup::make('👥 Human Capital')->collapsed(false),
                NavigationGroup::make('💰 Payroll')->collapsed(false),
                NavigationGroup::make('💵 Finance & Accounting')->collapsed(false),
                NavigationGroup::make('📦 Product & Inventory')->collapsed(false),
                NavigationGroup::make('📈 Sales & CRM')->collapsed(true),
                NavigationGroup::make('📋 Project Management')->collapsed(true),
                NavigationGroup::make('💬 Collaboration')->collapsed(true),
                NavigationGroup::make('🛒 POS & Retail')->collapsed(true),
                NavigationGroup::make('🎓 Learning')->collapsed(true),
                NavigationGroup::make('🏆 Gamification')->collapsed(true),
                NavigationGroup::make('🎫 Support')->collapsed(true),
                NavigationGroup::make('🤖 AI Assistant')->collapsed(true),
                NavigationGroup::make('⚡ Automation & Workflow')->collapsed(true),
                NavigationGroup::make('🔗 Integrations')->collapsed(true),
                NavigationGroup::make('🏭 Industry')->collapsed(true),
                NavigationGroup::make('🌱 ESG & Sustainability')->collapsed(true),
                NavigationGroup::make('🛡️ Compliance')->collapsed(true),
                NavigationGroup::make('🔷 Blockchain')->collapsed(true),
                NavigationGroup::make('💳 Billing & Licensing')->collapsed(true),
                NavigationGroup::make('🧩 Platform')->collapsed(true),
                NavigationGroup::make('⚙️ Sistem')->collapsed(true),
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
