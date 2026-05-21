<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class OrderItemsRelationManager extends RelationManager
{
    // اسم العلاقة المكتوبة جوه موديل الـ Order
    protected static string $relationship = 'items';

    // العنوان المترجم اللي هيظهر فوق جدول تفاصيل الطلب
    protected static ?string $title = 'منتجات الفاتورة';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                // 1. عرض صورة الـ Variant أو المنتج من الـ Storage
                ImageColumn::make('variant.variant_img')
                    ->label('الصورة')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(asset('images/default-product.png')),

                // 2. اسم المنتج الأساسي وقت الشراء
                TextColumn::make('product_name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->weight('bold'),

                // 3. تفاصيل الـ Variant (المقاس واللون) المجمعة في Badge شيك
                TextColumn::make('variant')
                    ->label('الخيارات')
                    ->state(function ($record) {
                        if (! $record->variant) return '-';
                        return trim("{$record->variant->size} {$record->variant->color}");
                    })
                    ->badge()
                    ->color('gray'),

                // 4. سعر القطعة الواحدة بعد تحويله من سنت (Cents) إلى جنيه
                TextColumn::make('current_price_cents')
                    ->label('سعر الوحدة')
                    ->money('EGP')
                    ->state(fn ($record) => $record->current_price_cents / 100)
                    ->fontFamily('mono'),

                // 5. الكمية المطلوبة في الفاتورة
                TextColumn::make('quantity')
                    ->label('الكمية')
                    ->alignment('center')
                    ->badge()
                    ->color('info'),

                // 6. الإجمالي الكلي للعنصر ده (الكمية × السعر)
                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->money('EGP')
                    ->state(fn ($record) => ($record->current_price_cents * $record->quantity) / 100)
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->color('success'),
            ])
            ->filters([
                // ضِف فيلترز هنا لو حابب تصفح المنتجات جوه الفاتورة
            ])
            ->headerActions([
                // خالي تماماً لأن الإضافة بتتم حصراً من شاشة الـ POS
            ])
            ->actions([
                // إتاحة الحذف الفردي لو مسموح بتعديل عناصر الفواتير من لوحة التحكم
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
