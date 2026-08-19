<?php
$dir = __DIR__ . '/../app/Filament/Resources';
$results = [];

// Get all directories inside Resources (each is a potential group)
$groupDirs = array_filter(
    glob($dir . '/*', GLOB_ONLYDIR),
    fn($d) => basename($d)
);

foreach ($groupDirs as $groupDir) {
    $resourceFiles = glob($groupDir . '/*Resource.php');
    foreach ($resourceFiles as $filePath) {
        $content = file_get_contents($filePath);
        $name = str_replace('.php', '', basename($filePath));

        // --- navigationGroup ---
        $grp = null;
        // Method: return 'Group' (handles string|null, ?string return types)
        if (preg_match('/function\s+getNavigationGroup\(\).*?return\s+[\'"](.+?)[\'"]/s', $content, $m)) {
            $grp = $m[1];
        }
        // Static property
        if ($grp === null && preg_match('/protected\s+static\s+\??string\s+\\$navigationGroup\s*=\s*[\'"](.+?)[\'"]/', $content, $m)) {
            $grp = $m[1];
        }
        // NavigationGroup::make('...')
        if ($grp === null && preg_match('/NavigationGroup::make\(\s*[\'"](.+?)[\'"]\s*\)/', $content, $m)) {
            $grp = $m[1];
        }

        // --- navigationSort ---
        $sort = null;
        if (preg_match('/protected\s+static\s+\?int\s+\\$navigationSort\s*=\s*(\d+)/', $content, $m)) {
            $sort = (int) $m[1];
        }
        if ($sort === null && preg_match('/function\s+getNavigationSort\(\).*?return\s+(\d+)/s', $content, $m)) {
            $sort = (int) $m[1];
        }

        // --- navigationIcon ---
        $icon = null;
        // Heroicon enum: Heroicon::OutlinedXxx or Heroicon::Xxx
        if (preg_match('/\\$navigationIcon\s*=\s*Heroicon::(\w+)/', $content, $m)) {
            $icon = 'Heroicon::' . $m[1];
        }
        // String icon
        if ($icon === null && preg_match('/\\$navigationIcon\s*=\s*[\'"](.+?)[\'"]/', $content, $m)) {
            $icon = $m[1];
        }
        // Method return
        if ($icon === null && preg_match('/function\s+getNavigationIcon\(\).*?return\s+([\'"](.+?)[\'"]|Heroicon::(\w+))/s', $content, $m)) {
            $icon = $m[2] ?? ('Heroicon::' . $m[3]);
        }

        $results[] = [
            'name'  => $name,
            'group' => $grp,
            'sort'  => $sort,
            'icon'  => $icon,
        ];
    }
}

// --- Group by navigation group ---
$groups = [];
foreach ($results as $r) {
    $g = $r['group'] ?? '(no group)';
    $groups[$g][] = $r;
}
ksort($groups);

// --- Build report ---
$lines = [];
$lines[] = str_repeat('=', 110);
$lines[] = 'NAVIGATION SORT & ICON AUDIT REPORT — BizOS';
$lines[] = 'Generated: ' . date('Y-m-d H:i:s');
$lines[] = 'Total Resources: ' . count($results);
$lines[] = 'Total Groups: ' . count($groups);
$lines[] = str_repeat('=', 110);
$lines[] = '';

$totalDupeGroups = 0;

foreach ($groups as $g => $items) {
    $lines[] = str_repeat('-', 110);
    $lines[] = sprintf('GROUP: %s (%d resources)', $g, count($items));
    $lines[] = str_repeat('-', 110);

    // Sort by navigationSort (nulls last)
    usort($items, function ($a, $b) {
        if ($a['sort'] === null && $b['sort'] === null) return 0;
        if ($a['sort'] === null) return 1;
        if ($b['sort'] === null) return -1;
        return $a['sort'] <=> $b['sort'];
    });

    foreach ($items as $i) {
        $s = $i['sort'] !== null ? str_pad((string) $i['sort'], 5) : ' NULL';
        $ico = $i['icon'] ?? 'NULL';
        $lines[] = sprintf('  sort=%s  icon=%-48s  %s', $s, $ico, $i['name']);
    }

    // Check duplicate icons within group
    $iconMap = [];
    foreach ($items as $i) {
        $ic = $i['icon'] ?? 'NULL';
        if (!isset($iconMap[$ic])) $iconMap[$ic] = [];
        $iconMap[$ic][] = $i['name'];
    }
    $dupes = array_filter($iconMap, fn($v) => count($v) > 1);
    if ($dupes) {
        $lines[] = '';
        $lines[] = '  ** DUPLICATE ICONS IN THIS GROUP:';
        foreach ($dupes as $ic => $names) {
            $totalDupeGroups++;
            $lines[] = sprintf('     "%s" (%dx): %s', $ic, count($names), implode(', ', $names));
        }
    }

    // Check null/0 sort
    $nullSorts = array_filter($items, fn($x) => $x['sort'] === null || $x['sort'] === 0);
    if ($nullSorts) {
        $lines[] = '';
        $lines[] = '  ** NULL/0 SORT: ' . implode(', ', array_column($nullSorts, 'name'));
    }

    $lines[] = '';
}

// --- Summary ---
$lines[] = str_repeat('=', 110);
$lines[] = 'SUMMARY';
$lines[] = str_repeat('=', 110);
$lines[] = 'Total Resources: ' . count($results);
$lines[] = 'Total Groups: ' . count($groups);
$lines[] = 'Groups with duplicate icons: ' . $totalDupeGroups;
$nullSortCount = count(array_filter($results, fn($x) => $x['sort'] === null || $x['sort'] === 0));
$lines[] = 'Resources with NULL/0 sort: ' . $nullSortCount;
$noGroup = count($groups['(no group)'] ?? []);
$lines[] = 'Resources with no group: ' . $noGroup;
$lines[] = str_repeat('=', 110);

// --- Sort gaps per group (only flag duplicates, not gaps — ranges are intentional) ---
$lines[] = '';
$lines[] = str_repeat('=', 110);
$lines[] = 'SORT NUMBER COLLISIONS PER GROUP (same sort value used by multiple resources):';
$lines[] = str_repeat('=', 110);
foreach ($groups as $g => $items) {
    if ($g === '(no group)') continue;
    $sortMap = [];
    foreach ($items as $i) {
        if ($i['sort'] === null) continue;
        if (!isset($sortMap[$i['sort']])) $sortMap[$i['sort']] = [];
        $sortMap[$i['sort']][] = $i['name'];
    }
    $collisions = array_filter($sortMap, fn($v) => count($v) > 1);
    if ($collisions) {
        foreach ($collisions as $s => $names) {
            $lines[] = sprintf('  %-55s  sort=%d → %s', $g, $s, implode(', ', $names));
        }
    }
}

// --- Sort collision across ALL groups ---
$lines[] = '';
$lines[] = str_repeat('=', 110);
$lines[] = 'GLOBAL SORT COLLISIONS (same sort number used in different groups):';
$lines[] = str_repeat('=', 110);
$globalSort = [];
foreach ($groups as $g => $items) {
    foreach ($items as $i) {
        if ($i['sort'] === null) continue;
        $key = $i['sort'];
        if (!isset($globalSort[$key])) $globalSort[$key] = [];
        $globalSort[$key][] = $g . ' / ' . $i['name'];
    }
}
ksort($globalSort);
$hasCollision = false;
foreach ($globalSort as $s => $refs) {
    if (count($refs) > 1) {
        $hasCollision = true;
        $lines[] = sprintf('  sort=%d used %d times: %s', $s, count($refs), implode('  |  ', $refs));
    }
}
if (!$hasCollision) {
    $lines[] = '  (none)';
}

$lines[] = '';
$lines[] = 'END OF REPORT';

// Write
$output = implode("\n", $lines);
file_put_contents(__DIR__ . '/audit-sort-icons.txt', $output);

echo "Done. " . count($results) . " resources across " . count($groups) . " groups.\n";
echo "Output: " . __DIR__ . "/audit-sort-icons.txt\n";
