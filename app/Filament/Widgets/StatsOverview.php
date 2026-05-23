<?php
namespace App\App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use App\Models\Product; // أو الموديل المسؤول عن المخزون

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('مبيعات اليوم', 'ج.م ' . Order::whereDate('created_at', today())->sum('total_price'))
                ->description('إجمالي الإيرادات لليوم')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('طلبات اليوم', Order::whereDate('created_at', today())->count())
                ->description('انخفاض أو ارتفاع')
                ->color('warning'),

            Stat::make('تنبيهات المخزون المنخفض', Product::where('quantity', '<', 5)->count())
                ->description('منتجات أوشكت على النفاد')
                ->color('danger'),
        ];
    }
}
