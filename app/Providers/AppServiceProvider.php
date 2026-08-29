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

        // Ensure Filament frontend notifications are dispatched directly to the browser
        // without relying on multi-request session round-trips (crucial for serverless Vercel)
        if (class_exists(\Livewire\Livewire::class)) {
            \Livewire\on('dehydrate', function (\Livewire\Component $component): void {
                if (! \Livewire\Livewire::isLivewireRequest()) {
                    return;
                }

                $notifications = session()->get('filament.notifications') ?? [];
                if (!empty($notifications)) {
                    foreach ($notifications as $notification) {
                        $component->dispatch('notificationSent', notification: $notification);
                    }
                }
            });
        }
    }
}
