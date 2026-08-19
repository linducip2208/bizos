<?php

use App\Models\User;
use App\Services\Dashboard\ComplianceDashboardService;
use App\Services\Dashboard\DashboardContext;
use App\Services\Dashboard\ExecutiveDashboardService;
use App\Services\Dashboard\FinanceDashboardService;
use App\Services\Dashboard\HrDashboardService;
use App\Services\Dashboard\MyWorkDashboardService;
use App\Services\Dashboard\OperationsDashboardService;
use App\Services\Dashboard\ProcurementDashboardService;
use App\Services\Dashboard\ProjectDashboardService;
use App\Services\Dashboard\SalesDashboardService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Cache;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$user = User::query()->with(['role.permissions', 'employee'])->find((int) ($argv[1] ?? 1));
if (! $user) {
    fwrite(STDERR, "User audit tidak ditemukan.\n");
    exit(1);
}

Cache::flush();
echo 'User: ' . json_encode([
    'id' => $user->id,
    'company_id' => $user->company_id,
    'employee_id' => $user->employee_id,
    'role' => $user->role?->slug,
    'saved_preferences' => $user->dashboard_preferences,
], JSON_UNESCAPED_SLASHES) . "\n";
$filterInput = [
    'company_id' => $user->company_id,
    'branch_id' => null,
    'department_id' => null,
    'business_unit_id' => null,
    'project_id' => null,
    'date_from' => now()->subYear()->startOfDay()->toDateString(),
    'date_to' => now()->endOfDay()->toDateString(),
];
if (($argv[2] ?? null) === '--saved') {
    $filterInput = array_merge($filterInput, $user->dashboard_preferences['filters'] ?? []);
}
$filter = app(DashboardContext::class)->forUser($user, $filterInput);

$services = [
    'overview' => ExecutiveDashboardService::class,
    'finance' => FinanceDashboardService::class,
    'sales' => SalesDashboardService::class,
    'hr-payroll' => HrDashboardService::class,
    'procurement-inventory' => ProcurementDashboardService::class,
    'operations' => OperationsDashboardService::class,
    'projects' => ProjectDashboardService::class,
    'risk-compliance' => ComplianceDashboardService::class,
    'my-work' => MyWorkDashboardService::class,
];

$failed = 0;
foreach ($services as $tab => $service) {
    try {
        $data = app($service)->get($filter, $user);
        $count = count($data['kpis'] ?? $data['cards'] ?? []);
        $summary = collect($data['kpis'] ?? $data['cards'] ?? [])->mapWithKeys(fn ($card) => [$card['label'] => $card['value'] ?? null])->all();
        echo "PASS {$tab}: {$count} card(s) " . json_encode($summary, JSON_UNESCAPED_UNICODE) . "\n";
    } catch (Throwable $exception) {
        $failed++;
        echo "FAIL {$tab}: {$exception->getMessage()}\n";
    }
}

exit($failed > 0 ? 1 : 0);
