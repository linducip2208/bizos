<?php
// Full verification: check every resource file has a valid new group
$resourcesPath = __DIR__ . '/../app/Filament/Resources';
$validGroups = [
    '🏠 Dashboard & Reporting',
    '🏢 Organisasi',
    '👥 Human Capital',
    '💰 Payroll',
    '💵 Finance & Accounting',
    '📦 Product & Inventory',
    '📈 Sales & CRM',
    '📋 Project Management',
    '💬 Collaboration',
    '🛒 POS & Retail',
    '🎓 Learning',
    '🏆 Gamification',
    '🎫 Support',
    '🤖 AI Assistant',
    '⚡ Automation & Workflow',
    '🔗 Integrations',
    '🏭 Industry',
    '🌱 ESG & Sustainability',
    '🛡️ Compliance',
    '🔷 Blockchain',
    '💳 Billing & Licensing',
    '🧩 Platform',
    '⚙️ Sistem',
];

$total = 0;
$ok = 0;
$issues = [];

$dirs = glob($resourcesPath . '/*', GLOB_ONLYDIR);
foreach ($dirs as $dir) {
    $dirname = basename($dir);
    $phpFiles = glob($dir . '/*Resource.php');
    foreach ($phpFiles as $file) {
        $total++;
        $content = file_get_contents($file);
        if (preg_match("/function\s+getNavigationGroup\(\).*?return\s+'([^']+)'/s", $content, $m)) {
            $group = $m[1];
            if (in_array($group, $validGroups)) {
                $ok++;
            } else {
                $issues[] = "INVALID GROUP: $dirname -> [{$group}]";
            }
        } else {
            $issues[] = "NO getNavigationGroup: $dirname/" . basename($file);
        }
    }
}

echo "============================================\n";
echo "  BizOS Navigation Group Restructure Report\n";
echo "============================================\n";
echo "Total resource files : $total\n";
echo "Valid new groups     : $ok\n";
echo "Issues               : " . count($issues) . "\n";
echo "Success rate         : " . ($total > 0 ? round($ok / $total * 100, 1) : 0) . "%\n";

if (!empty($issues)) {
    echo "\n⚠️ Issues:\n";
    foreach ($issues as $issue) {
        echo "  - $issue\n";
    }
}

echo "\nDone.\n";
