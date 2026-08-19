<?php

$root = dirname(__DIR__);
$targets = [
    'Resources' => glob($root . '/app/Filament/Resources/**/*Resource.php'),
    'Pages' => array_merge(glob($root . '/app/Filament/Pages/*.php'), glob($root . '/app/Filament/Pages/Dashboards/*.php')),
];
$groups = [];

foreach ($targets as $type => $files) {
    foreach ($files as $file) {
        $source = file_get_contents($file);
        preg_match('/class\s+(\w+)/', $source, $classMatch);
        preg_match('/NavigationGroup::(\w+)->value/', $source, $groupMatch);
        $group = $groupMatch[1] ?? 'INHERITED/NONE';
        $groups[$group][] = [
            'type' => rtrim($type, 's'),
            'class' => $classMatch[1] ?? basename($file, '.php'),
            'hidden' => preg_match('/shouldRegisterNavigation\s*=\s*false/', $source) === 1,
            'path' => str_replace('\\', '/', substr($file, strlen($root) + 1)),
        ];
    }
}

ksort($groups);
$labels = [
    'DASHBOARD' => 'Dashboard', 'ORGANIZATION' => 'Organization', 'HUMAN_CAPITAL' => 'Human Capital',
    'PAYROLL' => 'Payroll', 'FINANCE' => 'Finance & Accounting', 'SALES' => 'Sales & CRM',
    'PROCUREMENT' => 'Procurement', 'INVENTORY' => 'Inventory & Warehouse', 'OPERATIONS' => 'Operations',
    'PROJECTS' => 'Projects', 'COMMERCE' => 'POS & Commerce', 'COLLABORATION' => 'Collaboration & Service',
    'AUTOMATION' => 'Automation & AI', 'REPORTS' => 'Reports & Compliance', 'SYSTEM' => 'System Settings',
    'INHERITED/NONE' => 'Inherited / no direct menu group',
];

$out = "# Command Center & Navigation Audit\n\nGenerated: " . date('Y-m-d H:i:s') . " Asia/Jakarta\n\n";
$out .= "## Navigation mapping\n\nHidden entries are retained and accessible through their parent resource or direct route.\n\n";
foreach ($groups as $key => $items) {
    $out .= '### ' . ($labels[$key] ?? $key) . ' (' . count($items) . ")\n\n";
    foreach ($items as $item) {
        $suffix = $item['hidden'] ? ' — hidden child/internal entry' : '';
        $out .= "- `{$item['class']}` ({$item['type']}){$suffix} — `{$item['path']}`\n";
    }
    $out .= "\n";
}

$out .= <<<'MD'
## Dashboard lama → tab Command Center

- CEO, Manager, Tenant, Ecommerce, Hotel, Property, Rab, Blockchain, IoT, Integration Hub, Gamification → Overview
- CFO, Cash Flow, Treasury → Finance
- Sales, Sales Forecast, Marketing, Funnel Analysis, RFM, Cohort, Discount → Sales & CRM
- Performance, OKR, Flight Risk → HR & Payroll
- MRP, Logistics, Field Service, Process Mining → Operations & Manufacturing
- ISO, ESG, Fraud Detection, Anomaly, SoD, PDP → Risk & Compliance
- Dashboard Builder → Personalisasi (tetap tersedia, tidak menjadi menu dashboard kedua)

URL lama dipertahankan dan dialihkan oleh `RedirectLegacyDashboard` ke tab yang relevan.

## KPI per tab

- Overview: revenue, net profit, cash balance, outstanding AR/AP, pending approvals, tren revenue/expense, budget vs actual, pipeline, stok minimum, project health, overdue tasks.
- Finance: revenue, expense, net profit, AR, AP, journal posted, tren revenue/expense.
- Sales & CRM: lead, deal aktif, pipeline value, won revenue, conversion, stage pipeline.
- HR & Payroll: headcount, attendance, late/absent, payroll, leave, department distribution.
- Procurement & Inventory: submitted PR, active PO, period PO value, minimum stock, active products.
- Operations & Manufacturing: work orders, production output, quality checks, deliveries, field service.
- Projects: active projects, budget, actual cost, delayed projects, milestones, overdue tasks.
- Risk & Compliance: open risks, incidents, audit findings, SoD conflicts, anomalies, pending approvals.
- My Work: own active/overdue tasks, own requests, own attendance, unread notifications.

## Security & performance

- Filter IDs are revalidated against active company before queries run.
- Every aggregate is company-scoped and branch-scoped where the table supports branches.
- Tab authorization uses existing permissions; roles are only used to choose the initial tab.
- Dashboard service cache key includes company, branch, filters, date period and permission hash (TTL 10 minutes).
- Transaction observers increment per-company cache versions for invalidation.
- Composite indexes cover common scope/date/status query paths.
- Tabs load one service at a time; no eager execution of every dashboard query.

## Legacy issues found

- Resource `getSlug()` methods contained navigation-group labels, producing route collisions; group pollution was removed.
- `PurchaseOrder` and `PurchaseRequisition` models enabled soft deletes while their tables lacked `deleted_at`; additive migrations repair the mismatch without deleting data.
- Procurement query used status values not present in the database enums; values now match `submitted` and `partially_received`.
- Staff My Work previously fell back to executive metrics; it now uses a dedicated employee-scoped service.
MD;

file_put_contents($root . '/docs/COMMAND-CENTER-AUDIT.md', $out . "\n");
echo 'Wrote docs/COMMAND-CENTER-AUDIT.md with ' . array_sum(array_map('count', $groups)) . " mapped entries.\n";
