<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayOrdersWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Count total orders for today
        $todayOrders = Order::whereDate('created_at', today())->count();

        // Calculate percentage change from yesterday
        $yesterdayOrders = Order::whereDate('created_at', today()->subDay())->count();
        $percentageChange = $yesterdayOrders > 0
            ? round((($todayOrders - $yesterdayOrders) / $yesterdayOrders) * 100)
            : 0;

        return [
            Stat::make('طلبات اليوم', $todayOrders)
                ->description($percentageChange > 0 ? "زيادة {$percentageChange}% عن الأمس" : ($percentageChange < 0 ? "انخفاض " . abs($percentageChange) . "% عن الأمس" : 'نفس عدد طلبات الأمس'))
                ->descriptionIcon($percentageChange > 0 ? 'heroicon-m-arrow-trending-up' : ($percentageChange < 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-minus'))
                ->color($percentageChange > 0 ? 'success' : ($percentageChange < 0 ? 'warning' : 'gray'))
                ->chart([5, 10, 7, 8, 12, 9, $todayOrders]),
        ];
    }
}
