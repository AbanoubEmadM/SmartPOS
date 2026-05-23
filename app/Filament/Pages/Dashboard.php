<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-home';
    protected function getHeaderWidgets(): array
    {
        return [
            AccountWidget::class,
            \App\Filament\Widgets\TodayRevenueWidget::class,
            \App\Filament\Widgets\TodayOrdersWidget::class,
            \App\Filament\Widgets\LowStockAlertsWidget::class,
            \App\Filament\Widgets\TopSellingProductsWidget::class,
        ];
    }
}
