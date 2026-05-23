<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\ProductVariant;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Calculate total revenue for today
        $todayRevenue = Order::whereDate('created_at', today())
            ->sum('total_price_cents');
        $formattedRevenue = number_format($todayRevenue / 100, 2);

        // Count total orders for today
        $todayOrders = Order::whereDate('created_at', today())->count();

        // Calculate percentage change from yesterday
        $yesterdayOrders = Order::whereDate('created_at', today()->subDay())->count();
        $percentageChange = $yesterdayOrders > 0
            ? round((($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100)
            : 0;

        // Count products with low stock
        $lowStockCount = ProductVariant::whereRaw('stock <= low_stock_threshold')->count();

        // Count critical stock (stock = 0)
        $outOfStockCount = ProductVariant::where('stock', 0)->count();

        return [
            Stat::make('مبيعات اليوم', $formattedRevenue . ' ج.م')
                ->description('إجمالي الإيرادات لليوم')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),

            Stat::make('طلبات اليوم', $todayOrders)
                ->description($percentageChange > 0 ? "زيادة {$percentageChange}% عن الأمس" : ($percentageChange < 0 ? "انخفاض " . abs($percentageChange) . "% عن الأمس" : 'نفس عدد طلبات الأمس'))
                ->descriptionIcon($percentageChange > 0 ? 'heroicon-m-arrow-trending-up' : ($percentageChange < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'))
                ->color($percentageChange > 0 ? 'success' : ($percentageChange < 0 ? 'warning' : 'gray'))
                ->chart([5, 10, 7, 8, 12, 9, $todayOrders]),

            Stat::make('تنبيهات المخزون المنخفض', $lowStockCount)
                ->description($outOfStockCount > 0 ? "{$outOfStockCount} منتج نفذ من المخزون تماماً" : 'جميع المنتجات متوفرة')
                ->descriptionIcon($lowStockCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($outOfStockCount > 0 ? 'danger' : ($lowStockCount > 0 ? 'warning' : 'success')),
        ];
    }
}
