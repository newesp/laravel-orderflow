<?php

// Diagnostic endpoint for Vercel — remove after debugging
header('Content-Type: application/json');

$checks = [];

// 1. PHP version
$checks['php_version'] = PHP_VERSION;

// 2. Storage paths writable
$storageDirs = [
    '/tmp/storage/app',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];
foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}
$checks['storage_writable'] = is_writable('/tmp/storage');
$checks['views_writable'] = is_writable('/tmp/storage/framework/views');

// 3. Environment variables
$checks['env'] = [
    'APP_KEY_set' => !empty(getenv('APP_KEY') ?: ($_ENV['APP_KEY'] ?? ($_SERVER['APP_KEY'] ?? ''))),
    'APP_KEY_preview' => substr(getenv('APP_KEY') ?: ($_ENV['APP_KEY'] ?? ($_SERVER['APP_KEY'] ?? 'NOT_SET')), 0, 12) . '...',
    'DB_CONNECTION' => getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? ($_SERVER['DB_CONNECTION'] ?? 'NOT_SET')),
    'DB_HOST_set' => !empty(getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? ($_SERVER['DB_HOST'] ?? ''))),
    'SESSION_DRIVER' => getenv('SESSION_DRIVER') ?: ($_ENV['SESSION_DRIVER'] ?? ($_SERVER['SESSION_DRIVER'] ?? 'NOT_SET')),
    'CACHE_STORE' => getenv('CACHE_STORE') ?: ($_ENV['CACHE_STORE'] ?? ($_SERVER['CACHE_STORE'] ?? 'NOT_SET')),
    'LOG_CHANNEL' => getenv('LOG_CHANNEL') ?: ($_ENV['LOG_CHANNEL'] ?? ($_SERVER['LOG_CHANNEL'] ?? 'NOT_SET')),
    'DEMO_ADMIN_ENABLED' => getenv('DEMO_ADMIN_ENABLED') ?: ($_ENV['DEMO_ADMIN_ENABLED'] ?? ($_SERVER['DEMO_ADMIN_ENABLED'] ?? 'NOT_SET')),
    'SUPABASE_URL_set' => !empty(getenv('SUPABASE_URL') ?: ($_ENV['SUPABASE_URL'] ?? ($_SERVER['SUPABASE_URL'] ?? ''))),
];

// 4. Required PHP extensions
$requiredExts = ['pdo', 'pdo_pgsql', 'pgsql', 'mbstring', 'openssl', 'fileinfo', 'ctype', 'json', 'tokenizer'];
$checks['extensions'] = [];
foreach ($requiredExts as $ext) {
    $checks['extensions'][$ext] = extension_loaded($ext);
}

// 5. Try Laravel bootstrap
$checks['laravel_boot'] = 'not_attempted';
try {
    putenv('APP_STORAGE=/tmp/storage');
    putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
    putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
    putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
    putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
    putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
    putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');

    require __DIR__ . '/../vendor/autoload.php';
    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath('/tmp/storage');

    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $checks['laravel_boot'] = 'success';

    // 6. Try DB connection
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $checks['db_connection'] = 'success';
        $checks['db_driver'] = config('database.default');
    } catch (\Throwable $dbErr) {
        $checks['db_connection'] = 'failed';
        $checks['db_error'] = $dbErr->getMessage();
    }

    // 7. Check config values
    $checks['config'] = [
        'session_driver' => config('session.driver'),
        'cache_default' => config('cache.default'),
        'log_channel' => config('logging.default'),
        'db_default' => config('database.default'),
        'app_env' => config('app.env'),
        'app_debug' => config('app.debug'),
    ];

} catch (\Throwable $e) {
    $checks['laravel_boot'] = 'failed';
    $checks['boot_error'] = $e->getMessage();
    $checks['boot_error_file'] = $e->getFile() . ':' . $e->getLine();
    $checks['boot_error_trace'] = array_slice(
        array_map(fn($t) => ($t['file'] ?? '?') . ':' . ($t['line'] ?? '?') . ' ' . ($t['class'] ?? '') . ($t['type'] ?? '') . ($t['function'] ?? ''), $e->getTrace()),
        0, 10
    );
}

echo json_encode($checks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
