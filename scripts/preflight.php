<?php
// Quick pre-flight: test regex on all files
$resourcesPath = __DIR__ . '/../app/Filament/Resources';
$pattern = "/(function\s+getNavigationGroup\(\).*?return\s+)'[^']*'(\s*;)/s";

$dirs = array_filter(glob($resourcesPath . '/*'), 'is_dir');
$failures = [];

foreach ($dirs as $dir) {
    $dirname = basename($dir);
    $phpFiles = glob($dir . '/*Resource.php');
    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        if (!preg_match($pattern, $content)) {
            $method = '';
            if (preg_match('/getNavigationGroup.*?{[^}]*}/s', $content, $m)) {
                $method = $m[0];
            } elseif (preg_match('/function\s+getNavigationGroup\(\)[^\n]*/s', $content, $m)) {
                $method = $m[0];
            }
            $failures[] = "$dirname: $method";
        }
    }
}

if (empty($failures)) {
    echo "ALL FILES MATCH THE REGEX!\n";
} else {
    echo count($failures) . " FAILURES:\n";
    foreach ($failures as $f) {
        echo "  - $f\n";
    }
}
