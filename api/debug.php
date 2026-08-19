<?php

// Diagnostic endpoint — captures the EXACT error from the main entry point
header('Content-Type: application/json');

$checks = [];
$checks['php_version'] = PHP_VERSION;

// Setup writable dirs
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

// Set all env overrides exactly like api/index.php
putenv('APP_STORAGE=/tmp/storage');
putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
}

try {
    require __DIR__ . '/../vendor/autoload.php';

    /** @var \Illuminate\Foundation\Application $app */
    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath('/tmp/storage');

    $checks['app_created'] = true;

    // Actually boot the app by handling a fake GET /up request
    $request = \Illuminate\Http\Request::create('/up', 'GET');
    
    // Boot the kernel manually
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);
    
    $checks['laravel_boot'] = 'success';
    $checks['health_status'] = $response->getStatusCode();

    // Now config and facades are available
    $checks['config'] = [
        'session_driver' => config('session.driver'),
        'cache_default' => config('cache.default'),
        'log_channel' => config('logging.default'),
        'db_default' => config('database.default'),
        'db_host' => config('database.connections.pgsql.host') ? 'SET' : 'NOT_SET',
        'app_env' => config('app.env'),
        'app_debug' => config('app.debug'),
        'app_key_set' => !empty(config('app.key')),
    ];

    // Test DB connection
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $checks['db_connection'] = 'success';
        
        // Check if required tables exist
        $checks['tables'] = [];
        foreach (['profiles', 'products', 'orders', 'order_items', 'integration_logs'] as $table) {
            $checks['tables'][$table] = \Illuminate\Support\Facades\Schema::hasTable($table);
        }
    } catch (\Throwable $dbErr) {
        $checks['db_connection'] = 'failed';
        $checks['db_error'] = $dbErr->getMessage();
    }

    // Now simulate the actual login page request
    try {
        $loginRequest = \Illuminate\Http\Request::create('/admin/login', 'GET');
        $loginResponse = $kernel->handle($loginRequest);
        $checks['login_page_status'] = $loginResponse->getStatusCode();
        if ($loginResponse->getStatusCode() >= 400) {
            $checks['login_page_body_preview'] = substr($loginResponse->getContent(), 0, 2000);
        }
    } catch (\Throwable $loginErr) {
        $checks['login_page_error'] = $loginErr->getMessage();
        $checks['login_page_error_file'] = $loginErr->getFile() . ':' . $loginErr->getLine();
    }

} catch (\Throwable $e) {
    $checks['laravel_boot'] = 'failed';
    $checks['boot_error'] = $e->getMessage();
    $checks['boot_error_file'] = $e->getFile() . ':' . $e->getLine();
    $checks['boot_error_trace'] = array_slice(
        array_map(
            fn($t) => ($t['file'] ?? '?') . ':' . ($t['line'] ?? '?') . ' ' . ($t['class'] ?? '') . ($t['type'] ?? '') . ($t['function'] ?? ''),
            $e->getTrace()
        ),
        0, 15
    );
}

echo json_encode($checks, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
