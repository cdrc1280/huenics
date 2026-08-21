<?php

// Catch ALL errors including fatal ones
error_reporting(E_ALL);
ini_set('display_errors', '1');

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        if (!headers_sent()) {
            header('Content-Type: application/json', true, 500);
        }
        echo json_encode(['fatal_error' => $error['message'], 'file' => $error['file'], 'line' => $error['line']]);
    }
});

try {
    // ─── Force HTTPS behind Vercel edge ──────────────────────────────────
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['SERVER_PORT'] = '443';
    }

    // ─── Create writable directories in /tmp ─────────────────────────────
    foreach ([
        '/tmp/storage/app/public',
        '/tmp/storage/app/private',
        '/tmp/storage/framework/cache/data',
        '/tmp/storage/framework/sessions',
        '/tmp/storage/framework/views',
        '/tmp/storage/logs',
        '/tmp/cache',
    ] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    // ─── Database: copy pre-built SQLite if no remote external DB ────────
    $dbHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? '');
    $dbConn = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? '');
    $isLocalDb = empty($dbHost) || in_array($dbHost, ['127.0.0.1', 'localhost', '::1']) || $dbConn === 'sqlite';

    if ($isLocalDb) {
        $dbTarget = '/tmp/database.sqlite';
        if (!file_exists($dbTarget) || filesize($dbTarget) === 0) {
            $baseDb = dirname(__DIR__) . '/database/base.sqlite';
            if (file_exists($baseDb) && filesize($baseDb) > 0) {
                copy($baseDb, $dbTarget);
            } else {
                touch($dbTarget);
            }
        }
        putenv('DB_CONNECTION=sqlite');
        putenv("DB_DATABASE={$dbTarget}");
        $_ENV['DB_CONNECTION']  = $_SERVER['DB_CONNECTION']  = 'sqlite';
        $_ENV['DB_DATABASE']    = $_SERVER['DB_DATABASE']    = $dbTarget;
    }

    // ─── Session: cookie ─────────────────────────────────────────────────
    if (empty(getenv('SESSION_DRIVER')) && empty($_ENV['SESSION_DRIVER'])) {
        putenv('SESSION_DRIVER=cookie');
        $_ENV['SESSION_DRIVER'] = $_SERVER['SESSION_DRIVER'] = 'cookie';
    }

    // ─── APP_KEY ─────────────────────────────────────────────────────────
    if (empty(getenv('APP_KEY')) && empty($_ENV['APP_KEY'])) {
        $key = 'base64:OmixpRBaKmg+k4HjJgrTq+v3v5yWXMAR05omeeVOW2c=';
        putenv("APP_KEY={$key}");
        $_ENV['APP_KEY'] = $_SERVER['APP_KEY'] = $key;
    }

    // ─── Point ALL Laravel cache paths to writable /tmp ──────────────────
    // Without these, Laravel tries to write to read-only bootstrap/cache/
    // which causes service providers (including ViewServiceProvider) to
    // not register, resulting in "Target class [view] does not exist"
    $cachePaths = [
        'APP_SERVICES_CACHE' => '/tmp/cache/services.php',
        'APP_PACKAGES_CACHE' => '/tmp/cache/packages.php',
        'APP_CONFIG_CACHE'   => '/tmp/cache/config.php',
        'APP_ROUTES_CACHE'   => '/tmp/cache/routes-v7.php',
        'APP_EVENTS_CACHE'   => '/tmp/cache/events.php',
    ];
    foreach ($cachePaths as $envKey => $path) {
        putenv("{$envKey}={$path}");
        $_ENV[$envKey] = $_SERVER[$envKey] = $path;
    }

    // ─── Bootstrap Laravel ───────────────────────────────────────────────
    define('LARAVEL_START', microtime(true));
    require __DIR__ . '/../vendor/autoload.php';

    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath('/tmp/storage');

    $app->handleRequest(
        \Illuminate\Http\Request::capture()
    );

} catch (\Throwable $e) {
    $root = $e;
    while ($root->getPrevious()) {
        $root = $root->getPrevious();
    }

    if (!headers_sent()) {
        header('Content-Type: application/json', true, 500);
    }
    echo json_encode([
        'error'      => $e->getMessage(),
        'root_error' => ($root !== $e) ? $root->getMessage() : null,
        'root_file'  => ($root !== $e) ? $root->getFile() . ':' . $root->getLine() : null,
        'file'       => $e->getFile() . ':' . $e->getLine(),
        'trace'      => array_slice(explode("\n", $e->getTraceAsString()), 0, 10),
    ]);
}
