<?php

namespace App\Filament\Widgets;

use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LowStockAlertsWidget extends BaseWidget
{

    protected function getStats(): array
    {
        // Count products with low stock
        $lowStockCount = ProductVariant::whereRaw('stock <= low_stock_threshold')->count();

        // Count critical stock (stock = 0)
        $outOfStockCount = ProductVariant::where('stock', 0)->count();

        return [
            Stat::make('تنبيهات المخزون المنخفض', $lowStockCount)
                ->description($outOfStockCount > 0 ? "{$outOfStockCount} منتج نفذ من المخزون تماماً" : 'جميع المنتجات متوفرة')
                ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($outOfStockCount > 0 ? 'danger' : ($lowStockCount > 0 ? 'warning' : 'success')),
        ];
    }
}
