<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\OrderResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class OrdersRelationManager extends RelationManager
{
    // اسم دالة العلاقة المكتوبة جوه موديل الـ User/Employee (مثال: public function orders())
    protected static string $relationship = 'orders';

    // العنوان المترجم اللي هيظهر فوق جدول مبيعات الموظف
    protected static ?string $title = 'المبيعات والفواتير الصادرة';

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

                // 2. اسم العميل المربوط بالفاتورة
                TextColumn::make('customer.name')
                    ->label('اسم العميل')
                    ->searchable()
                    ->default('عميل نقدي / سريع')
                    ->weight('medium'),

                // 3. وسيلة الدفع (نقدي أو فيزا)
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

                // 4. إجمالي السعر بعد تحويله من سنت (Cents) إلى جنيه
                TextColumn::make('total_price_cents')
                    ->label('إجمالي المبيعات')
                    ->money('EGP')
                    ->state(fn ($record) => $record->total_price_cents / 100)
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->color('success')
                    ->sortable(),

                // 5. تاريخ وتوقيت الفاتورة
                TextColumn::make('created_at')
                    ->label('تاريخ الصدور')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('وسيلة الدفع')
                    ->options([
                        'cash' => 'نقدي',
                        'card' => 'كارت / فيزا',
                    ]),
            ])
            ->headerActions([
                // خالي لأن الفواتير بتصدر حصراً من شاشة الـ POS
            ])
            ->actions([
                // زرار View بينقلك فوراً لصفحة الفاتورة الأساسية عشان تشوف الـ Items اللي الكاشير ده باعها
            ])
            ->bulkActions([
                //
            ]);
    }
}
