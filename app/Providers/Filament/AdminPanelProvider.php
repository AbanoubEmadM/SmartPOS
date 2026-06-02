<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\DashboardStatsOverview;
use App\Filament\Widgets\OrderSalesChartWidget;
use App\Filament\Widgets\TopSellingProductsWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
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
            ->colors([
                'primary' => Color::Amber,
            ])
            ->authGuard('web')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([])
            ->brandName('K&H Shoes')
            ->brandLogo(asset('images/logo.jpeg'))
            ->brandLogoHeight('4.5rem')
            ->favicon(asset('images/favicon.png'))
        ->homeUrl(function () {
                /** @var \App\Models\Employee $user */
                $user = auth()->user();

                if ($user && $user->role === 'cashier') {
                    return '/pos';
                }

                return '/admin';
            })
            ->widgets([
                DashboardStatsOverview::class,
                OrderSalesChartWidget::class,
                TopSellingProductsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->navigationItems([
                NavigationItem::make('شاشة البيع (POS)')
                    ->url('/pos', shouldOpenInNewTab: false)
                    ->icon('heroicon-o-shopping-cart')
                    ->activeIcon('heroicon-s-shopping-cart')
                    ->group('المبيعات')
                    ->sort(2),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
