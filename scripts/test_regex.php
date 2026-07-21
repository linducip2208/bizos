<?php
$testFiles = [
    'app/Filament/Resources/AssetCategory/AssetCategoryResource.php',
    'app/Filament/Resources/Competencies/CompetenciesResource.php',
    'app/Filament/Resources/AiProviders/AiProviderResource.php',
    'app/Filament/Resources/VehicleFuelLog/VehicleFuelLogResource.php',
];

$pattern = "/(function\s+getNavigationGroup\(\).*?return\s+)'[^']*'(\s*;)/s";

foreach ($testFiles as $f) {
    $content = file_get_contents($f);
    if (preg_match($pattern, $content, $m)) {
        echo basename(dirname($f)) . ': MATCHED -> "' . $m[0] . '"' . PHP_EOL;
    } else {
        echo basename(dirname($f)) . ': NO MATCH' . PHP_EOL;
        if (preg_match('/getNavigationGroup.*/', $content, $mm)) {
            echo "  " . $mm[0] . PHP_EOL;
        }
    }
}

echo PHP_EOL . "Test replacements:" . PHP_EOL;
$testReplace = 'app/Filament/Resources/AssetCategory/AssetCategoryResource.php';
$content = file_get_contents($testReplace);
$newContent = preg_replace($pattern, "$1'TEST_GROUP'$2", $content, 1);
if ($newContent !== $content) {
    echo "AssetCategory replacement WORKS" . PHP_EOL;
} else {
    echo "AssetCategory replacement FAILED" . PHP_EOL;
}
