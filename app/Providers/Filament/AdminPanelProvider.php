<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->brandName('Code Garage Admin')
            ->colors([
                'primary' => Color::Sky,
            ])
            ->navigationItems([
                NavigationItem::make('Back To Site')
                    ->icon('heroicon-o-home')
                    ->url(url('/'))
                    ->sort(-2),
                NavigationItem::make('My Profile')
                    ->icon('heroicon-o-user-circle')
                    ->url(url('/user/profile'))
                    ->sort(-1),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverResources(in: base_path('packages/Courses/src/Presentation/Filament/Resources'), for: 'CodeGarage\\Courses\\Presentation\\Filament\\Resources')
            ->discoverResources(in: base_path('packages/Lessons/src/Presentation/Filament/Resources'), for: 'CodeGarage\\Lessons\\Presentation\\Filament\\Resources')
            ->discoverResources(in: base_path('packages/Assignments/src/Presentation/Filament/Resources'), for: 'CodeGarage\\Assignments\\Presentation\\Filament\\Resources')
            ->discoverResources(in: base_path('packages/Queries/src/Presentation/Filament/Resources'), for: 'CodeGarage\\Queries\\Presentation\\Filament\\Resources')
            ->discoverResources(in: base_path('packages/Events/src/Presentation/Filament/Resources'), for: 'CodeGarage\\Events\\Presentation\\Filament\\Resources')
            ->discoverResources(in: base_path('packages/DevelopmentRequests/src/Presentation/Filament/Resources'), for: 'CodeGarage\\DevelopmentRequests\\Presentation\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
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

