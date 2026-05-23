<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardStatsOverview::class,
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
