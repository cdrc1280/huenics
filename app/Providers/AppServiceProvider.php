<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use App\Models\PurchaseOrder;
use App\Observers\PurchaseOrderObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS when configured or behind HTTPS reverse proxies (Cloudflare, Railway, AWS, Load Balancers)
        $isHttps = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1'))
            || str_starts_with((string) config('app.url'), 'https://');

        if ($isHttps) {
            URL::forceScheme('https');
        }

        // Register Eloquent Observers
        PurchaseOrder::observe(PurchaseOrderObserver::class);

        // Register Authentication Activity Listeners
        Event::listen(Login::class, [LogAuthenticationActivity::class, 'handleLogin']);
        Event::listen(Logout::class, [LogAuthenticationActivity::class, 'handleLogout']);

        // Polyfill json_extract for SQLite runtimes lacking JSON1 (e.g. AWS Lambda / Vercel PHP)
        $registerSqliteJsonPolyfill = function ($connection) {
            if ($connection instanceof \Illuminate\Database\SQLiteConnection) {
                $pdo = $connection->getPdo();
                if ($pdo && method_exists($pdo, 'sqliteCreateFunction')) {
                    $pdo->sqliteCreateFunction('json_extract', function ($json, $path) {
                        if (empty($json) || empty($path)) {
                            return null;
                        }
                        $data = json_decode((string) $json, true);
                        if (!is_array($data)) {
                            return null;
                        }

                        $cleanPath = trim(str_replace(['$', '"', "'", '[', ']'], '', $path));
                        $parts = explode('.', $cleanPath);
                        $val = $data;
                        foreach ($parts as $part) {
                            if ($part === '') {
                                continue;
                            }
                            if (is_array($val) && array_key_exists($part, $val)) {
                                $val = $val[$part];
                            } else {
                                return null;
                            }
                        }

                        return is_scalar($val) ? $val : json_encode($val);
                    });
                }
            }
        };

        Event::listen(
            \Illuminate\Database\Events\ConnectionEstablished::class,
            function ($event) use ($registerSqliteJsonPolyfill) {
                $registerSqliteJsonPolyfill($event->connection);
            }
        );

        try {
            if (\Illuminate\Support\Facades\DB::connection() instanceof \Illuminate\Database\SQLiteConnection) {
                $registerSqliteJsonPolyfill(\Illuminate\Support\Facades\DB::connection());
            }
        } catch (\Throwable $e) {
            // DB not yet initialized
        }

        // Ensure Filament frontend notifications are dispatched directly to the browser
        // without relying on multi-request session round-trips (crucial for serverless Vercel)
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\on('dehydrate', function (\Livewire\Component $component): void {
                if (! \Livewire\Livewire::isLivewireRequest()) {
                    return;
                }

                rescue(function () use ($component) {
                    $notifications = session()->get('filament.notifications')
                        ?? session()->get('filament.claimed_notifications')
                        ?? [];

                    if (!empty($notifications) && is_array($notifications)) {
                        foreach ($notifications as $notification) {
                            if (is_array($notification)) {
                                $component->dispatch('notificationSent', notification: $notification);
                            }
                        }
                    }
                }, report: false);
            });
        }
    }
}
