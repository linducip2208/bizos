<?php

$root = dirname(__DIR__);
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app/Filament/Pages', FilesystemIterator::SKIP_DOTS));
$changed = 0;

foreach ($iterator as $file) {
    if (! $file->isFile() || ! str_ends_with($file->getBasename(), 'Dashboard.php')) {
        continue;
    }

    $source = file_get_contents($file->getPathname());
    if (str_contains($source, '@deprecated Use CommandCenter')) {
        continue;
    }

    $updated = preg_replace('/^(class\s+\w+\s+extends\s+Page)/m', "/** @deprecated Use CommandCenter with the matching tab; the legacy URL is preserved by redirect middleware. */\n$1", $source, 1, $count);
    if ($count === 1) {
        file_put_contents($file->getPathname(), $updated);
        $changed++;
    }
}

echo "Deprecated {$changed} legacy dashboard class(es).\n";
