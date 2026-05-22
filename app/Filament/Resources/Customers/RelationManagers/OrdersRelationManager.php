<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\OrderResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class OrdersRelationManager extends RelationManager
{
    // اسم دالة العلاقة المكتوبة جوه موديل الـ Customer (مثال: public function orders())
    protected static string $relationship = 'orders';

    // العنوان المترجم فوق الجدول
    protected static ?string $title = 'سجل الفواتير والطلبات';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                // 1. رقم الفاتورة
                TextColumn::make('id')
                    ->label('رقم الفاتورة')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->prefix('#'),

                // 2. وسيلة الدفع (نقدي أو فيزا) مع تلوين شيك للـ Badges
                TextColumn::make('payment_method')
                    ->label('وسيلة الدفع')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cash' => 'success',
                        'card' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cash' => 'نقدي (Cash)',
                        'card' => 'فيزا / كارت (Card)',
                        default => $state,
                    }),

                // 3. إجمالي السعر بعد تحويله من سنت (Cents) إلى جنيه
                TextColumn::make('total_price_cents')
                    ->label('إجمالي الفاتورة')
                    ->money('EGP')
                    ->state(fn ($record) => $record->total_price_cents / 100)
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->color('primary')
                    ->sortable(),

                // 4. تاريخ وتوقيت إنشاء الفاتورة
                TextColumn::make('created_at')
                    ->label('تاريخ الفاتورة')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->filters([
                // تقدر تضيف هنا فلتر للتصفية بناءً على وسيلة الدفع (cash/card)
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('وسيلة الدفع')
                    ->options([
                        'cash' => 'نقدي',
                        'card' => 'كارت / فيزا',
                    ]),
            ])
            ->headerActions([
                // خالي تماماً لأن الفواتير بتنزل أوتوماتيك من شاشة الـ POS
            ])
            ->actions([
                // زرار View بينقلك فوراً لصفحة تفاصيل الفاتورة الأساسية في الـ OrderResource عشان تشوف الـ Items بتاعتها
            ])
            ->bulkActions([
                //
            ]);
    }
}
