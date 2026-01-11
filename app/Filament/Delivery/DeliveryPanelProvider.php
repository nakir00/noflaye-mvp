<?php

namespace App\Filament\Delivery;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class DeliveryPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('delivery')
            ->path('delivery')
            ->login()
            ->colors([
                'primary' => Color::Green,
            ])
            // Découverte des clusters dans le panel Delivery
            ->discoverClusters(in: app_path('Filament/Delivery/Clusters'), for: 'App\\Filament\\Delivery\\Clusters')
            ->discoverPages(in: app_path('Filament/Delivery/Pages'), for: 'App\\Filament\\Delivery\\Pages')
            ->discoverWidgets(in: app_path('Filament/Delivery/Widgets'), for: 'App\\Filament\\Delivery\\Widgets')
            ->navigationGroups([
                'Routes & Orders',
                'Permissions & Security',
                'My Account',
            ])
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
