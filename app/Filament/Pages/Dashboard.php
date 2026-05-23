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
            \App\Filament\Widgets\TodayRevenueWidget::class,
            \App\Filament\Widgets\TodayOrdersWidget::class,
            \App\Filament\Widgets\LowStockAlertsWidget::class,
            \App\Filament\Widgets\OrderSalesChartWidget::class,
            \App\Filament\Widgets\TopSellingProductsWidget::class,
        ];
    }
    public function getColumns(): int | array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
