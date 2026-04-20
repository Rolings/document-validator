<?php
spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\'   => __DIR__ . '/src/',
        'Tests\\' => __DIR__ . '/tests/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }
        $relative = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});
