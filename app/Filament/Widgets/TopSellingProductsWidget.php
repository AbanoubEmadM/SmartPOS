<?php

namespace App\Filament\Widgets;

use App\Models\OrderItem;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class TopSellingProductsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('أفضل 5 منتجات مبيعاً هذا الأسبوع')
            ->query(
                OrderItem::query()
                    ->select(
                        'order_items.id',
                        'order_items.product_name',
                        DB::raw('SUM(order_items.quantity) as total_quantity'),
                        DB::raw('SUM(order_items.quantity * order_items.current_price_cents) as total_revenue')
                    )
                    ->join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->whereBetween('orders.created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ])
                    ->groupBy('order_items.product_name', 'order_items.id')
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
