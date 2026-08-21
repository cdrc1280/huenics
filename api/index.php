<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Force HTTPS when behind Vercel's edge proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
}

// Ensure writable storage directories exist in /tmp for Vercel Serverless
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

// ─── Database: fallback to SQLite if no external DB is configured ─────────────
$dbHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? '');
if (empty($dbHost)) {
    $dbFile   = '/tmp/database.sqlite';
    $freshDb  = !file_exists($dbFile);

    if ($freshDb) {
        touch($dbFile);
    }

    foreach (['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $dbFile] as $k => $v) {
        putenv("{$k}={$v}");
        $_ENV[$k] = $_SERVER[$k] = $v;
    }
}

// ─── Session: always cookie (no sessions table dependency) ───────────────────
foreach (['SESSION_DRIVER' => 'cookie'] as $k => $v) {
    if (empty(getenv($k)) && empty($_ENV[$k])) {
        putenv("{$k}={$v}");
        $_ENV[$k] = $_SERVER[$k] = $v;
    }
}

// ─── APP_KEY fallback (set real key in Vercel Dashboard → Environment Vars) ──
if (empty(getenv('APP_KEY')) && empty($_ENV['APP_KEY'])) {
    $key = 'base64:OmixpRBaKmg+k4HjJgrTq+v3v5yWXMAR05omeeVOW2c=';
    putenv("APP_KEY={$key}");
    $_ENV['APP_KEY'] = $_SERVER['APP_KEY'] = $key;
}

// ─── Bootstrap Laravel ────────────────────────────────────────────────────────
require __DIR__ . '/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath('/tmp/storage');

// ─── Run migrations inside a properly-booted kernel ──────────────────────────
// We do this only for SQLite fallback; safe to run every request (idempotent).
if (empty($dbHost)) {
    try {
        $artisan = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $artisan->call('migrate', ['--force' => true]);
        if (!empty($freshDb)) {
            $artisan->call('db:seed', ['--force' => true]);
        }
        $artisan->terminate();
    } catch (\Throwable $e) {
        // Continue — real errors will surface in the HTTP response
    }
}

$app->handleRequest(Request::capture());
