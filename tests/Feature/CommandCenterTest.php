<?php

namespace Tests\Feature;

use App\Filament\Pages\CommandCenter;
use App\Http\Middleware\RequirePair;
use App\Models\Branch;
use App\Models\Company;
use App\Models\DashboardWidget;
use App\Models\Invoice;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Dashboard\DashboardContext;
use App\Services\Dashboard\ExecutiveDashboardService;
use App\Services\DashboardBuilderService;
use App\Services\Dashboard\FinanceDashboardService;
use App\Services\Dashboard\SalesDashboardService;
use App\Services\Dashboard\HrDashboardService;
use App\Services\Dashboard\ProcurementDashboardService;
use App\Services\Dashboard\OperationsDashboardService;
use App\Services\Dashboard\ProjectDashboardService;
use App\Services\Dashboard\ComplianceDashboardService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class CommandCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->withoutMiddleware(RequirePair::class);
    }

    public function test_authorized_user_can_open_command_center(): void
    {
        [$user] = $this->makeUser('staff', ['dashboard']);

        $this->actingAs($user)->get(CommandCenter::getUrl())->assertOk()->assertSee('Dashboard Command Center');
    }

    public function test_company_and_branch_filters_are_tenant_scoped(): void
    {
        [$user, $company] = $this->makeUser('staff', ['dashboard']);
        $branch = Branch::create(['company_id' => $company->id, 'code' => 'HQ', 'name' => 'Pusat', 'is_active' => true]);
        $filter = app(DashboardContext::class)->forUser($user, ['branch_id' => $branch->id]);

        $this->assertSame($company->id, $filter->companyId);
        $this->assertSame($branch->id, $filter->branchId);

        $other = Company::factory()->create();
        $this->expectException(AuthorizationException::class);
        app(DashboardContext::class)->forUser($user, ['company_id' => $other->id]);
    }

    public function test_kpis_do_not_include_other_company_data(): void
    {
        [$user, $company] = $this->makeUser('admin', ['dashboard', 'report.view', 'finance.view']);
        $other = Company::factory()->create();
        $this->invoice($company, 'INV-TENANT', 1250000);
        $this->invoice($other, 'INV-OTHER', 99000000);
        $filter = app(DashboardContext::class)->forUser($user, ['date_from' => today()->subDay()->toDateString(), 'date_to' => today()->toDateString()]);

        $data = app(ExecutiveDashboardService::class)->get($filter, $user);

        $this->assertSame(1250000.0, (float) collect($data['kpis'])->firstWhere('key', 'revenue')['value']);
    }

    public function test_staff_defaults_to_my_work_and_cannot_select_finance(): void
    {
        [$user] = $this->makeUser('staff', ['dashboard']);

        Livewire::actingAs($user)->test(CommandCenter::class)
            ->assertSet('activeTab', 'my-work')
            ->assertSet('availableTabs.finance', null);
    }

    public function test_empty_database_returns_zero_real_kpis(): void
    {
        [$user] = $this->makeUser('admin', ['dashboard', 'report.view', 'finance.view']);
        $filter = app(DashboardContext::class)->forUser($user);
        $data = app(ExecutiveDashboardService::class)->get($filter, $user);

        $this->assertCount(6, $data['kpis']);
        $this->assertSame(0.0, (float) collect($data['kpis'])->firstWhere('key', 'revenue')['value']);
    }

    public function test_filter_preferences_are_saved_per_user(): void
    {
        [$user] = $this->makeUser('staff', ['dashboard']);

        Livewire::actingAs($user)->test(CommandCenter::class)
            ->set('filters.currency', 'USD')
            ->call('applyFilters');

        $this->assertSame('USD', $user->fresh()->dashboard_preferences['filters']['currency']);
    }

    public function test_every_dashboard_service_handles_an_empty_tenant(): void
    {
        [$user] = $this->makeUser('admin', ['dashboard', 'report.view', 'finance.view']);
        $filter = app(DashboardContext::class)->forUser($user);
        $services = [
            ExecutiveDashboardService::class,
            FinanceDashboardService::class,
            SalesDashboardService::class,
            HrDashboardService::class,
            ProcurementDashboardService::class,
            OperationsDashboardService::class,
            ProjectDashboardService::class,
            ComplianceDashboardService::class,
        ];

        foreach ($services as $service) {
            $this->assertIsArray(app($service)->get($filter, $user), $service);
        }
    }

    public function test_dashboard_query_count_stays_bounded(): void
    {
        [$user] = $this->makeUser('admin', ['dashboard', 'report.view', 'finance.view']);
        $filter = app(DashboardContext::class)->forUser($user);
        DB::enableQueryLog();

        app(ExecutiveDashboardService::class)->get($filter, $user);

        $this->assertLessThanOrEqual(24, count(DB::getQueryLog()));
    }

    public function test_legacy_dashboard_url_redirects_to_matching_tab(): void
    {
        [$user] = $this->makeUser('admin', ['dashboard', 'finance.view']);

        $this->actingAs($user)->get('/admin/cfo-dashboard')
            ->assertRedirect(CommandCenter::getUrl(['tab' => 'finance']));
    }

    public function test_excel_export_is_downloadable(): void
    {
        [$user] = $this->makeUser('staff', ['dashboard']);

        Livewire::actingAs($user)->test(CommandCenter::class)
            ->call('exportExcel')
            ->assertFileDownloaded();
    }

    public function test_widget_personalization_is_owned_and_reset_per_user(): void
    {
        [$user, $company] = $this->makeUser('staff', ['dashboard']);
        $service = app(DashboardBuilderService::class);
        $widget = $service->addWidget([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'widget_type' => 'my_tasks',
            'title' => 'Tugas Pribadi',
            'is_pinned' => true,
        ]);

        $this->assertTrue($service->getWidgets($user->id)->contains('id', $widget->id));
        $service->resetToRoleDefault($user->id);
        $this->assertFalse(DashboardWidget::query()->whereKey($widget->id)->exists());
        $this->assertNotEmpty($service->getWidgets($user->id));
    }

    private function makeUser(string $roleSlug, array $permissions): array
    {
        $company = Company::factory()->create();
        $role = Role::create(['company_id' => $company->id, 'name' => ucfirst($roleSlug), 'slug' => $roleSlug]);
        foreach ($permissions as $slug) {
            $permission = Permission::firstOrCreate(['slug' => $slug], ['name' => $slug, 'group' => 'dashboard']);
            $role->permissions()->syncWithoutDetaching($permission);
        }
        $user = User::factory()->create(['company_id' => $company->id, 'role_id' => $role->id, 'is_active' => true]);

        return [$user, $company];
    }

    private function invoice(Company $company, string $number, float $total): Invoice
    {
        return Invoice::create([
            'company_id' => $company->id,
            'invoice_number' => $number,
            'invoice_type' => 'sales',
            'invoice_date' => today(),
            'due_date' => today()->addWeek(),
            'reference_entity' => 'test',
            'reference_id' => 1,
            'subtotal' => $total,
            'total' => $total,
            'paid_amount' => $total,
            'remaining_amount' => 0,
            'status' => 'paid',
        ]);
    }
}
