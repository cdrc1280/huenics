<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));

// Force HTTPS environment when behind Vercel edge reverse proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
}

// Ensure writable storage directories exist in /tmp for Vercel Serverless
$storageDirs = [
    '/tmp/storage/app/public',
    '/tmp/storage/app/private',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// ─── Database Setup ───────────────────────────────────────────────────────────
$dbConnection = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? null);
$dbHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? null);
$isNewDb = false;

if ($dbConnection === 'sqlite' || empty($dbHost)) {
    putenv('DB_CONNECTION=sqlite');
    $_ENV['DB_CONNECTION'] = 'sqlite';
    $_SERVER['DB_CONNECTION'] = 'sqlite';

    $dbFile = '/tmp/database.sqlite';
    if (!file_exists($dbFile)) {
        touch($dbFile);
        $isNewDb = true;
    }

    putenv("DB_DATABASE={$dbFile}");
    $_ENV['DB_DATABASE'] = $dbFile;
    $_SERVER['DB_DATABASE'] = $dbFile;
}

// ─── Session Driver ───────────────────────────────────────────────────────────
// Force cookie sessions on Vercel — no writable filesystem needed for sessions
if (empty(getenv('SESSION_DRIVER')) && empty($_ENV['SESSION_DRIVER'])) {
    putenv('SESSION_DRIVER=cookie');
    $_ENV['SESSION_DRIVER'] = 'cookie';
    $_SERVER['SESSION_DRIVER'] = 'cookie';
}

// ─── APP_KEY ──────────────────────────────────────────────────────────────────
// Must be set in Vercel Environment Variables. This is a secure fallback only.
if (empty(getenv('APP_KEY')) && empty($_ENV['APP_KEY'])) {
    $fallbackKey = 'base64:OmixpRBaKmg+k4HjJgrTq+v3v5yWXMAR05omeeVOW2c=';
    putenv("APP_KEY={$fallbackKey}");
    $_ENV['APP_KEY'] = $fallbackKey;
    $_SERVER['APP_KEY'] = $fallbackKey;
}

// ─── Bootstrap Laravel ────────────────────────────────────────────────────────
require __DIR__ . '/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath('/tmp/storage');

// ─── Run Migrations ───────────────────────────────────────────────────────────
// Always ensure DB schema is up to date (safe: --force skips confirmation,
// migrations are idempotent). Only runs for SQLite fallback mode.
if ($dbConnection === 'sqlite' || empty($dbHost)) {
    try {
        Artisan::call('migrate', ['--force' => true, '--graceful' => true]);
        if ($isNewDb) {
            Artisan::call('db:seed', ['--force' => true]);
        }
    } catch (\Throwable $e) {
        // Continue; request handler will surface any real errors
    }
}

$app->handleRequest(Request::capture());
