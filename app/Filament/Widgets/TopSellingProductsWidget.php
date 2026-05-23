<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopSellingProductsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        // Get top 5 selling products this week
        $topProducts = OrderItem::select(
            'order_items.product_name',
            DB::raw('SUM(order_items.quantity) as total_quantity'),
            DB::raw('SUM(order_items.quantity * order_items.current_price_cents) as total_revenue')
        )
            ->join('orders', 'order_items.order_id', '=', 'orders.order_id')
            ->whereBetween('orders.created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        return $table
            ->heading('أفضل 5 منتجات مبيعاً هذا الأسبوع')
            ->query(
                OrderItem::query()
                    ->select(
                        'order_items.product_name',
                        DB::raw('SUM(order_items.quantity) as total_quantity'),
                        DB::raw('SUM(order_items.quantity * order_items.current_price_cents) as total_revenue')
                    )
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereBetween('orders.created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])
                    ->groupBy('order_items.product_name')
                    ->orderByDesc('total_quantity')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('product_name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('الكمية المباعة')
                    ->sortable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('إجمالي الإيرادات')
                    ->formatStateUsing(fn ($state) => number_format($state / 100, 2) . ' ج.م')
                    ->sortable(),
            ])
            ->paginated(false);
    }
}
