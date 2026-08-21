<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Force HTTPS when behind Vercel's edge proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
}

// Ensure writable storage directories exist in /tmp
foreach ([
    '/tmp/storage/app/public',
    '/tmp/storage/app/private',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
] as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// ─── SQLite fallback if no external DB ───────────────────────────────────────
$dbHost  = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? '');
$freshDb = false;

if (empty($dbHost)) {
    $dbFile  = '/tmp/database.sqlite';
    $freshDb = !file_exists($dbFile);

    if ($freshDb) {
        touch($dbFile);
    }

    putenv('DB_CONNECTION=sqlite');
    putenv("DB_DATABASE={$dbFile}");
    $_ENV['DB_CONNECTION']  = $_SERVER['DB_CONNECTION']  = 'sqlite';
    $_ENV['DB_DATABASE']    = $_SERVER['DB_DATABASE']    = $dbFile;
}

// ─── Always cookie sessions — no sessions table dependency ───────────────────
if (empty(getenv('SESSION_DRIVER')) && empty($_ENV['SESSION_DRIVER'])) {
    putenv('SESSION_DRIVER=cookie');
    $_ENV['SESSION_DRIVER'] = $_SERVER['SESSION_DRIVER'] = 'cookie';
}

// ─── APP_KEY (set a real one in Vercel Dashboard → Environment Variables) ────
if (empty(getenv('APP_KEY')) && empty($_ENV['APP_KEY'])) {
    $key = 'base64:OmixpRBaKmg+k4HjJgrTq+v3v5yWXMAR05omeeVOW2c=';
    putenv("APP_KEY={$key}");
    $_ENV['APP_KEY'] = $_SERVER['APP_KEY'] = $key;
}

// ─── Run migrations in a separate process (avoids Console/HTTP Kernel conflict)
if (empty($dbHost)) {
    $artisan = dirname(__DIR__) . '/artisan';
    $php     = PHP_BINARY;

    exec("{$php} {$artisan} migrate --force 2>/dev/null");

    if ($freshDb) {
        exec("{$php} {$artisan} db:seed --force 2>/dev/null");
    }
}

// ─── Bootstrap and handle HTTP request ───────────────────────────────────────
require __DIR__ . '/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath('/tmp/storage');

$app->handleRequest(Request::capture());
