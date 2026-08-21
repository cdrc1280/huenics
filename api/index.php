<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// ─── Force HTTPS behind Vercel edge ──────────────────────────────────────────
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = '443';
}

// ─── Writable storage dirs in /tmp ───────────────────────────────────────────
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

// ─── Database: copy pre-built SQLite if no external DB ───────────────────────
$dbHost = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? '');

if (empty($dbHost)) {
    $dbTarget = '/tmp/database.sqlite';

    // Copy the pre-built base database on cold start (first request per Lambda)
    if (!file_exists($dbTarget)) {
        $baseDb = dirname(__DIR__) . '/database/base.sqlite';
        if (file_exists($baseDb)) {
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

// ─── Session: cookie — no DB table needed ────────────────────────────────────
if (empty(getenv('SESSION_DRIVER')) && empty($_ENV['SESSION_DRIVER'])) {
    putenv('SESSION_DRIVER=cookie');
    $_ENV['SESSION_DRIVER'] = $_SERVER['SESSION_DRIVER'] = 'cookie';
}

// ─── APP_KEY fallback (set real key in Vercel Dashboard) ─────────────────────
if (empty(getenv('APP_KEY')) && empty($_ENV['APP_KEY'])) {
    $key = 'base64:OmixpRBaKmg+k4HjJgrTq+v3v5yWXMAR05omeeVOW2c=';
    putenv("APP_KEY={$key}");
    $_ENV['APP_KEY'] = $_SERVER['APP_KEY'] = $key;
}

// ─── Bootstrap Laravel and handle the HTTP request ───────────────────────────
require __DIR__ . '/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath('/tmp/storage');

$app->handleRequest(Request::capture());
