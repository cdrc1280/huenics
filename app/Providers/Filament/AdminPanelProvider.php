<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->sidebarCollapsibleOnDesktop()
            ->brandName('Huenics')
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
            ])
            ->navigationGroups([
                'Sales & Order Lifecycle',
                'Inventory & Operations',
                'Reports & Analytics',
                'Master Data & Registry',
                'System Administration',
            ])
            ->renderHook(
                'panels::head.end',
                fn (): string => Blade::render('
                    <style>
                        /* Responsive ToggleButtons Layout - Equal Distribution & No Overflow */
                        .fi-fo-toggle-buttons-btn-group,
                        .fi-fo-toggle-buttons .fi-btn-group,
                        .fi-fo-toggle-buttons div[role="group"],
                        .fi-fo-toggle-buttons .fi-fo-toggle-buttons-options-container {
                            display: flex !important;
                            width: 100% !important;
                            max-width: 100% !important;
                        }
                        .fi-fo-toggle-buttons-btn-group > *,
                        .fi-fo-toggle-buttons .fi-btn-group > *,
                        .fi-fo-toggle-buttons div[role="group"] > *,
                        .fi-fo-toggle-buttons .fi-fo-toggle-buttons-options-container > * {
                            flex: 1 1 0% !important;
                            min-width: 0 !important;
                            display: inline-flex !important;
                            justify-content: center !important;
                            align-items: center !important;
                            text-align: center !important;
                            padding-left: 0.375rem !important;
                            padding-right: 0.375rem !important;
                        }
                        .fi-fo-toggle-buttons-btn-group > * span,
                        .fi-fo-toggle-buttons .fi-btn-group > * span,
                        .fi-fo-toggle-buttons div[role="group"] > * span,
                        .fi-fo-toggle-buttons .fi-fo-toggle-buttons-options-container > * span {
                            white-space: nowrap !important;
                            overflow: hidden !important;
                            text-overflow: ellipsis !important;
                            font-size: 0.8125rem !important;
                        }
                        @media (max-width: 640px) {
                            .fi-fo-toggle-buttons-btn-group > *,
                            .fi-fo-toggle-buttons .fi-btn-group > *,
                            .fi-fo-toggle-buttons div[role="group"] > *,
                            .fi-fo-toggle-buttons .fi-fo-toggle-buttons-options-container > * {
                                padding-left: 0.25rem !important;
                                padding-right: 0.25rem !important;
                            }
                            .fi-fo-toggle-buttons-btn-group > * svg,
                            .fi-fo-toggle-buttons .fi-btn-group > * svg,
                            .fi-fo-toggle-buttons div[role="group"] > * svg,
                            .fi-fo-toggle-buttons .fi-fo-toggle-buttons-options-container > * svg {
                                width: 0.875rem !important;
                                height: 0.875rem !important;
                                margin-right: 0.25rem !important;
                            }
                        }
                    </style>
                ')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
