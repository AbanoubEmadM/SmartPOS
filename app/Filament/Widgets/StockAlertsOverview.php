<?php

namespace App\Filament\Widgets;

use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockAlertsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $lowStockCount = ProductVariant::whereRaw('stock <= low_stock_threshold')->count();

        return [
            Stat::make('منتجات أوشكت على النفاد', $lowStockCount)
                ->description($lowStockCount > 0 ? 'يرجى مراجعة نواقص المخزن فوراً' : 'جميع المنتجات بمخزون آمن')
                ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-check-circle')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),
        ];
    }
}
