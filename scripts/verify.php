<?php
echo "=== Resource file checks (targeting getNavigationGroup) ===\n";
$checks = [
    'AssetCategory'       => '💵 Finance & Accounting',
    'BlockchainTransaction' => '🔷 Blockchain',
    'ApprovalRequests'    => '⚡ Automation & Workflow',
    'AbcClassifications'  => '📦 Product & Inventory',
    'EnergyMeter'         => '🌱 ESG & Sustainability',
    'SubscriptionPlans'   => '💳 Billing & Licensing',
    'MarketplaceApps'     => '🧩 Platform',
    'BizForms'            => '💬 Collaboration',
    'Competencies'        => '👥 Human Capital',
    'AttendanceConfigs'   => '👥 Human Capital',
    'AiProviders'         => '🤖 AI Assistant',
    'WasteRecords'        => '🌱 ESG & Sustainability',
    'SodRules'            => '🛡️ Compliance',
    'Licenses'            => '💳 Billing & Licensing',
];

foreach ($checks as $dir => $expected) {
    $pattern = __DIR__ . "/../app/Filament/Resources/$dir/*Resource.php";
    $files = glob($pattern);
    if (empty($files)) {
        echo "MISSING DIR: $dir\n";
        continue;
    }
    $content = file_get_contents($files[0]);
    if (preg_match("/function\s+getNavigationGroup\(\).*?return\s+'([^']+)'/s", $content, $m)) {
        $status = $m[1] === $expected ? '✓' : '✗ MISMATCH';
        echo "$status $dir\n  Expected: [$expected]\n  Got:      [{$m[1]}]\n";
    } else {
        echo "NO MATCH: $dir\n";
        if (preg_match('/getNavigationGroup.*return\s+(\S+)/s', $content, $mm)) {
            echo "  Found return: {$mm[1]}\n";
        }
    }
}
