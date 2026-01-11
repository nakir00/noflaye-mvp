<?php

namespace App\Filament\Kitchen;

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

class KitchenPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('kitchen')
            ->path('kitchen')
            ->login()
            ->colors([
                'primary' => Color::Orange,
            ])
            // Découverte des clusters dans le panel Kitchen
            ->discoverClusters(in: app_path('Filament/Kitchen/Clusters'), for: 'App\\Filament\\Kitchen\\Clusters')
            ->discoverPages(in: app_path('Filament/Kitchen/Pages'), for: 'App\\Filament\\Kitchen\\Pages')
            ->discoverWidgets(in: app_path('Filament/Kitchen/Widgets'), for: 'App\\Filament\\Kitchen\\Widgets')
            ->navigationGroups([
                'Production',
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
