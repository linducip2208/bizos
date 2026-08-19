<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$paths = [
    $root . '/app/Filament/Resources',
    $root . '/app/Filament/Pages',
];

$mapping = [
    'Dashboard' => 'DASHBOARD',
    'Organization' => 'ORGANIZATION',
    'Human Capital' => 'HUMAN_CAPITAL',
    'Payroll' => 'PAYROLL',
    'Finance & Accounting' => 'FINANCE',
    'Sales & CRM' => 'SALES',
    'CRM' => 'SALES',
    'Procurement' => 'PROCUREMENT',
    'Inventory & Warehouse' => 'INVENTORY',
    'Fleet & Field Service' => 'OPERATIONS',
    'Maintenance' => 'OPERATIONS',
    'Manufacturing' => 'OPERATIONS',
    'Quality' => 'OPERATIONS',
    'Industry' => 'OPERATIONS',
    'Projects & Operations' => 'PROJECTS',
    'Project' => 'PROJECTS',
    'POS & Retail' => 'COMMERCE',
    'Marketing' => 'SALES',
    'Collaboration' => 'COLLABORATION',
    'Learning' => 'COLLABORATION',
    'Support & Service' => 'COLLABORATION',
    'Automation' => 'AUTOMATION',
    'AI & Intelligence' => 'AUTOMATION',
    'Reports & Analytics' => 'REPORTS',
    'Compliance & Risk' => 'REPORTS',
    'Documents & Contracts' => 'COLLABORATION',
    'Billing & Licensing' => 'SYSTEM',
    'Integrations' => 'SYSTEM',
    'System' => 'SYSTEM',
    'Tools' => 'SYSTEM',
];

$changed = 0;
$unknown = [];

foreach ($paths as $base) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        $revised = $contents;
        $currentGroup = null;

        $revised = preg_replace_callback(
            '/\s*public static function getSlug\([^)]*\):\s*string\s*\{\s*return\s+[\'\"]([^\'\"]+)[\'\"]\s*;\s*\}/s',
            function (array $match) use ($mapping): string {
                return isset($mapping[$match[1]]) ? '' : $match[0];
            },
            $revised,
        );

        if (preg_match('/function\s+getNavigationGroup\(\)[^{]*\{[^}]*return\s+[\'\"]([^\'\"]+)[\'\"]/s', $revised, $match)) {
            $currentGroup = $match[1];
        } elseif (preg_match('/\$navigationGroup\s*=\s*[\'\"]([^\'\"]+)[\'\"]/', $revised, $match)) {
            $currentGroup = $match[1];
        }

        if ($currentGroup !== null) {
            if (! isset($mapping[$currentGroup])) {
                $unknown[$currentGroup][] = $file->getPathname();
                continue;
            }

            $case = $mapping[$currentGroup];
            $revised = preg_replace(
                '/(function\s+getNavigationGroup\(\)[^{]*\{[^}]*return\s+)[\'\"][^\'\"]+[\'\"](\s*;)/s',
                '$1\\App\\Filament\\Navigation\\NavigationGroup::' . $case . '->value$2',
                $revised,
                1,
            );

            if (preg_match('/\$navigationGroup\s*=/', $revised)) {
                $revised = preg_replace('/^\s*protected\s+static\s+[^;]*\$navigationGroup\s*=\s*[\'\"][^\'\"]+[\'\"]\s*;\s*$/m', '', $revised);

                if (! preg_match('/function\s+getNavigationGroup\(/', $revised)) {
                    $position = strrpos($revised, '}');
                    $method = "\n    public static function getNavigationGroup(): ?string\n    {\n        return \\App\\Filament\\Navigation\\NavigationGroup::{$case}->value;\n    }\n";
                    $revised = substr($revised, 0, $position) . $method . substr($revised, $position);
                }
            }
        }

        $className = $file->getBasename('.php');
        $isChildResource = preg_match('/(?:Item|Entry|Attendee|Answer|Reviewer)Resource$/', $className) === 1;

        if ($isChildResource && ! str_contains($revised, '$shouldRegisterNavigation')) {
            $revised = preg_replace(
                '/(class\s+' . preg_quote($className, '/') . '\s+extends\s+Resource[^\{]*\{)/',
                "$1\n    protected static bool \$shouldRegisterNavigation = false;",
                $revised,
                1,
            );
        }

        if ($revised !== $contents) {
            file_put_contents($file->getPathname(), $revised);
            $changed++;
        }
    }
}

if ($unknown !== []) {
    foreach ($unknown as $group => $files) {
        fwrite(STDERR, "Unknown group {$group}: " . implode(', ', $files) . PHP_EOL);
    }
    exit(1);
}

echo "Centralized navigation in {$changed} files." . PHP_EOL;

$providerPath = $root . '/app/Providers/Filament/AdminPanelProvider.php';
$provider = file_get_contents($providerPath);
if (! str_contains($provider, 'NavigationGroup as NavigationGroupDefinition')) {
    $provider = str_replace(
        "namespace App\\Providers\\Filament;\n",
        "namespace App\\Providers\\Filament;\n\nuse App\\Filament\\Navigation\\NavigationGroup as NavigationGroupDefinition;",
        $provider,
    );
}
$provider = preg_replace(
    '/\s*->navigationGroups\(\[.*?\]\)\s*->renderHook/s',
    "\n            ->navigationGroups(array_map(\n                fn (NavigationGroupDefinition \$group): \\Filament\\Navigation\\NavigationGroup => \\Filament\\Navigation\\NavigationGroup::make(\$group->value)\n                    ->icon(\$group->icon())\n                    ->collapsed(! in_array(\$group, [\n                        NavigationGroupDefinition::DASHBOARD,\n                        NavigationGroupDefinition::ORGANIZATION,\n                        NavigationGroupDefinition::HUMAN_CAPITAL,\n                    ], true)),\n                NavigationGroupDefinition::ordered(),\n            ))\n            ->renderHook",
    $provider,
    1,
    $providerCount,
);

if ($providerCount === 1) {
    file_put_contents($providerPath, $provider);
    echo "AdminPanelProvider groups centralized." . PHP_EOL;
}
