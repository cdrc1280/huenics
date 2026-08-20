<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

define('LARAVEL_START', microtime(true));

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

// Fallback SQLite database handling if no remote DB host is configured
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

// Fallback APP_KEY if not yet set in Vercel Environment Variables
if (empty(getenv('APP_KEY')) && empty($_ENV['APP_KEY'])) {
    $fallbackKey = 'base64:XG8d0M1kYf1Z2V3W4E5R6T7Y8U9I0O1P2A3S4D5F6G7=';
    putenv("APP_KEY={$fallbackKey}");
    $_ENV['APP_KEY'] = $fallbackKey;
    $_SERVER['APP_KEY'] = $fallbackKey;
}

// Require Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel 11 Application
/** @var Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath('/tmp/storage');

// Auto-run migrations & seeders on first cold boot if using fallback SQLite
if ($isNewDb) {
    try {
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--force' => true]);
    } catch (\Throwable $e) {
        // Continue and handle request
    }
}

$app->handleRequest(Request::capture());

