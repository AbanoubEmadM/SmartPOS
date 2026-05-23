<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class OrderSalesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'إحصائيات المبيعات';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = 'today';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'اليوم',
            'month' => 'هذا الشهر',
            'year' => 'هذا العام',
        ];
    }

    protected function getData(): array
    {
        $data = match ($this->filter) {
            'today' => Trend::model(Order::class)
                ->between(
                    start: now()->startOfDay(),
                    end: now()->endOfDay(),
                )
                ->perHour()
                ->sum('total_price_cents'),

            'month' => Trend::model(Order::class)
                ->between(
                    start: now()->startOfMonth(),
                    end: now()->endOfMonth(),
                )
                ->perDay()
                ->sum('total_price_cents'),

            'year' => Trend::model(Order::class)
                ->between(
                    start: now()->startOfYear(),
                    end: now()->endOfYear(),
                )
                ->perMonth()
                ->sum('total_price_cents'),

            default => Trend::model(Order::class)
                ->between(
                    start: now()->startOfDay(),
                    end: now()->endOfDay(),
                )
                ->perHour()
                ->sum('total_price_cents'),
        };

        return [
            'datasets' => [
                [
                    'label' => 'المبيعات (ج.م)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate / 100),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $this->formatLabel($value->date)),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function formatLabel(string $date): string
    {
        return match ($this->filter) {
            'today' => \Carbon\Carbon::parse($date)->format('H:00'),
            'month' => \Carbon\Carbon::parse($date)->format('d M'),
            'year' => \Carbon\Carbon::parse($date)->translatedFormat('M'),
            default => \Carbon\Carbon::parse($date)->format('H:00'),
        };
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return value.toLocaleString() + " ج.م"; }',
                    ],
                ],
            ],
        ];
    }
}
