<?php

namespace App\Filament\Pages;

use App\Filament\Navigation\NavigationGroup;
use App\Models\Branch;
use App\Models\BusinessUnit;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use App\Services\Dashboard\ComplianceDashboardService;
use App\Services\Dashboard\DashboardContext;
use App\Services\Dashboard\DashboardFilter;
use App\Services\Dashboard\ExecutiveDashboardService;
use App\Services\Dashboard\FinanceDashboardService;
use App\Services\Dashboard\HrDashboardService;
use App\Services\Dashboard\MyWorkDashboardService;
use App\Services\Dashboard\OperationsDashboardService;
use App\Services\Dashboard\ProcurementDashboardService;
use App\Services\Dashboard\ProjectDashboardService;
use App\Services\Dashboard\SalesDashboardService;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CommandCenter extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-command-line';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard Command Center';
    protected static ?string $slug = '';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.command-center';

    public string $activeTab = 'overview';
    public array $filters = [];
    public array $data = [];
    public array $availableTabs = [];
    public ?string $loadError = null;
    public ?string $lastUpdated = null;

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroup::DASHBOARD->value;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && ($user->hasPermission('dashboard') || in_array($user->role?->slug, ['super-admin', 'admin'], true));
    }

    public function getTitle(): string|Htmlable
    {
        return 'Dashboard Command Center';
    }

    public function mount(DashboardContext $context): void
    {
        abort_unless(static::canAccess(), 403);

        /** @var User $user */
        $user = auth()->user();
        $preferences = $user->dashboard_preferences ?? [];
        $this->filters = array_merge($this->defaultFilters($user), $preferences['filters'] ?? []);
        $this->availableTabs = $this->authorizedTabs($user);
        $preferredTab = request()->query('tab', $preferences['default_tab'] ?? $this->defaultTab($user));
        $this->activeTab = array_key_exists($preferredTab, $this->availableTabs)
            ? $preferredTab
            : (array_key_first($this->availableTabs) ?? 'my-work');
        $this->loadActiveTab($context);
    }

    public function selectTab(string $tab, DashboardContext $context): void
    {
        abort_unless(array_key_exists($tab, $this->availableTabs), 403);
        $this->activeTab = $tab;
        $this->persistPreferences();
        $this->loadActiveTab($context);
    }

    public function applyFilters(DashboardContext $context): void
    {
        $this->persistPreferences();
        $this->loadActiveTab($context);
    }

    public function refreshDashboard(DashboardContext $context): void
    {
        $this->loadActiveTab($context);
    }

    public function loadActiveTab(DashboardContext $context): void
    {
        $this->loadError = null;

        try {
            /** @var User $user */
            $user = auth()->user();
            $filter = $context->forUser($user, $this->filters);
            $this->filters = $filter->toArray();
            $this->data = $this->resolveService($this->activeTab)->get($filter, $user);
            $this->lastUpdated = now()->translatedFormat('d M Y, H:i:s');
        } catch (Throwable $exception) {
            report($exception);
            $this->data = [];
            $this->loadError = 'Data dashboard tidak dapat dimuat. Periksa filter atau coba refresh.';
        }
    }

    public function exportPdf(DashboardContext $context): Response
    {
        $filter = $context->forUser(auth()->user(), $this->filters);
        $data = $this->resolveService($this->activeTab)->get($filter, auth()->user());

        return response()->streamDownload(
            fn () => print(Pdf::loadView('pdf.command-center', compact('data', 'filter'))->output()),
            "dashboard-{$this->activeTab}-" . now()->format('Ymd-His') . '.pdf',
        );
    }

    public function exportExcel(DashboardContext $context): StreamedResponse
    {
        $filter = $context->forUser(auth()->user(), $this->filters);
        $data = $this->resolveService($this->activeTab)->get($filter, auth()->user());
        $rows = $data['kpis'] ?? $data['cards'] ?? $data['signals'] ?? [];

        return response()->streamDownload(function () use ($rows): void {
            echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="UTF-8"></head><body><table border="1">';
            echo '<tr><th>Metrik</th><th>Nilai</th><th>Format</th></tr>';
            foreach ($rows as $row) {
                echo '<tr><td>' . e($row['label'] ?? '-') . '</td><td>' . e((string) ($row['value'] ?? 0)) . '</td><td>' . e($row['format'] ?? 'number') . '</td></tr>';
            }
            echo '</table></body></html>';
        }, "dashboard-{$this->activeTab}-" . now()->format('Ymd-His') . '.xls', ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    public function getFilterOptionsProperty(): array
    {
        /** @var User $user */
        $user = auth()->user();
        $companyId = (int) ($this->filters['company_id'] ?? $user->company_id);

        return [
            'companies' => $user->role?->slug === 'super-admin'
                ? Company::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all()
                : Company::query()->whereKey($user->company_id)->pluck('name', 'id')->all(),
            'branches' => Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all(),
            'business_units' => BusinessUnit::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all(),
            'departments' => Department::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all(),
            'projects' => Project::query()->where('company_id', $companyId)->whereIn('status', ['planning', 'active'])->orderBy('name')->pluck('name', 'id')->all(),
            'currencies' => Currency::query()->where('is_active', true)->orderByDesc('is_base')->pluck('code', 'code')->all(),
        ];
    }

    public function formatValue(float|int|string|null $value, string $format = 'number'): string
    {
        if ($format !== 'currency') {
            return number_format((float) $value, 0, ',', '.');
        }

        $currency = strtoupper((string) ($this->filters['currency'] ?? 'IDR'));

        return ($currency === 'IDR' ? 'Rp' : $currency) . ' ' . number_format((float) $value, $currency === 'IDR' ? 0 : 2, ',', '.');
    }

    protected function resolveService(string $tab): ExecutiveDashboardService|FinanceDashboardService|SalesDashboardService|HrDashboardService|ProcurementDashboardService|OperationsDashboardService|ProjectDashboardService|ComplianceDashboardService|MyWorkDashboardService
    {
        return app(match ($tab) {
            'finance' => FinanceDashboardService::class,
            'sales' => SalesDashboardService::class,
            'hr-payroll' => HrDashboardService::class,
            'procurement-inventory' => ProcurementDashboardService::class,
            'operations' => OperationsDashboardService::class,
            'projects' => ProjectDashboardService::class,
            'risk-compliance' => ComplianceDashboardService::class,
            'my-work' => MyWorkDashboardService::class,
            default => ExecutiveDashboardService::class,
        });
    }

    protected function authorizedTabs(User $user): array
    {
        $all = in_array($user->role?->slug, ['super-admin', 'admin'], true);
        $definitions = [
            'overview' => ['label' => 'Overview', 'icon' => 'heroicon-o-squares-2x2', 'permissions' => ['report.view', 'finance.view']],
            'finance' => ['label' => 'Finance', 'icon' => 'heroicon-o-banknotes', 'permissions' => ['finance.view', 'finance.manage']],
            'sales' => ['label' => 'Sales & CRM', 'icon' => 'heroicon-o-chart-bar', 'permissions' => ['client.view', 'client.manage']],
            'hr-payroll' => ['label' => 'HR & Payroll', 'icon' => 'heroicon-o-users', 'permissions' => ['employee.view', 'payroll.view']],
            'procurement-inventory' => ['label' => 'Procurement & Inventory', 'icon' => 'heroicon-o-cube', 'permissions' => ['pos.view', 'finance.view']],
            'operations' => ['label' => 'Operations & Manufacturing', 'icon' => 'heroicon-o-cog-6-tooth', 'permissions' => ['project.view', 'pos.view']],
            'projects' => ['label' => 'Projects', 'icon' => 'heroicon-o-clipboard-document-list', 'permissions' => ['project.view', 'project.manage']],
            'risk-compliance' => ['label' => 'Risk & Compliance', 'icon' => 'heroicon-o-shield-check', 'permissions' => ['report.view', 'settings.manage']],
            'my-work' => ['label' => 'My Work', 'icon' => 'heroicon-o-user-circle', 'permissions' => ['dashboard']],
        ];

        return array_filter($definitions, fn (array $tab) => $all || $user->hasAnyPermission($tab['permissions']));
    }

    protected function defaultTab(User $user): string
    {
        return match ($user->role?->slug) {
            'finance', 'cfo' => 'finance',
            'sales-manager' => 'sales',
            'hr-manager' => 'hr-payroll',
            'procurement', 'warehouse' => 'procurement-inventory',
            'production-manager' => 'operations',
            'project-manager' => 'projects',
            'auditor', 'compliance' => 'risk-compliance',
            'staff' => 'my-work',
            default => 'overview',
        };
    }

    protected function defaultFilters(User $user): array
    {
        return [
            'company_id' => $user->company_id,
            'branch_id' => $user->employee?->branch_id,
            'business_unit_id' => null,
            'department_id' => $user->employee?->department_id,
            'project_id' => null,
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'comparison_period' => 'previous_period',
            'currency' => 'IDR',
        ];
    }

    protected function persistPreferences(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $user->forceFill(['dashboard_preferences' => [
            'default_tab' => $this->activeTab,
            'filters' => $this->filters,
        ]])->save();
    }
}
