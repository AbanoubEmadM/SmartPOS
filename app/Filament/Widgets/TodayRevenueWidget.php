<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TodayRevenueWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Calculate total revenue for today
        $todayRevenue = Order::whereDate('created_at', today())
            ->sum('total_price_cents');

        // Convert cents to currency format
        $formattedRevenue = number_format($todayRevenue / 100, 2);

        return [
            Stat::make('مبيعات اليوم', $formattedRevenue . ' ج.م')
                ->description('إجمالي الإيرادات لليوم')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]), // Example trend data
        ];
    }
}
