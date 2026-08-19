<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/app/Filament/Pages', FilesystemIterator::SKIP_DOTS),
);

$updated = [];

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();

    if ($file->getBasename() === 'Home.php' || ! str_contains($file->getBasename(), 'Dashboard')) {
        continue;
    }

    $contents = file_get_contents($path);

    if ($contents === false) {
        throw new RuntimeException("Tidak dapat membaca {$path}");
    }

    $pattern = '/public static function getNavigationGroup\(\):\s*\??string\s*\{\s*return\s+[^;]+;\s*\}/s';
    $replacement = <<<'PHP'
public static function getNavigationGroup(): ?string
    {
        return 'Dashboard';
    }
PHP;

    $revised = preg_replace($pattern, $replacement, $contents, 1, $count);

    if ($revised === null) {
        throw new RuntimeException("Gagal memproses {$path}");
    }

    if ($count === 0) {
        $position = strrpos($contents, '}');

        if ($position === false) {
            throw new RuntimeException("Class tidak valid: {$path}");
        }

        $method = "\n    public static function getNavigationGroup(): ?string\n    {\n        return 'Dashboard';\n    }\n";
        $revised = substr($contents, 0, $position) . $method . substr($contents, $position);
    }

    $revised = preg_replace(
        '/protected static bool \$shouldRegisterNavigation\s*=\s*(?:true|false);/',
        'protected static bool $shouldRegisterNavigation = false;',
        $revised,
        1,
        $propertyCount,
    );

    if ($propertyCount === 0) {
        $revised = preg_replace(
            '/public static function shouldRegisterNavigation\(\): bool\s*\{\s*return\s+(?:true|false);\s*\}/s',
            "public static function shouldRegisterNavigation(): bool\n    {\n        return false;\n    }",
            $revised,
            1,
            $methodCount,
        );

        if ($methodCount === 0) {
            $revised = preg_replace(
                '/(class\s+[A-Za-z0-9_]+\s+extends\s+Page[^\{]*\{)/',
                "$1\n    protected static bool \$shouldRegisterNavigation = false;",
                $revised,
                1,
                $insertCount,
            );

            if ($insertCount === 0) {
                throw new RuntimeException("Tidak dapat menyembunyikan navigasi dashboard: {$path}");
            }
        }
    }

    if ($revised === $contents) {
        continue;
    }

    file_put_contents($path, $revised);
    $updated[] = str_replace($root . DIRECTORY_SEPARATOR, '', $path);
}

echo sprintf("Dashboard navigation consolidated: %d file(s).\n", count($updated));

foreach ($updated as $path) {
    echo " - {$path}\n";
}
