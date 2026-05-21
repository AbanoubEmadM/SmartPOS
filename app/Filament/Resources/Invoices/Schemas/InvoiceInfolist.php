<?php

namespace App\Filament\Resources\Invoices\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // القسم الأول: معلومات الفاتورة الأساسية
                Section::make('معلومات الفاتورة')
                    ->columnSpan(1)
                    ->schema([
                        Placeholder::make('id')
                            ->label('رقم الفاتورة الكلي:')
                            ->content(fn ($record) => $record ? "#{$record->id}" : '-'),

                        Placeholder::make('created_at')
                            ->label('تاريخ وتوقيت الإصدار:')
                            ->content(fn ($record) => $record?->created_at?->format('Y-m-d h:i A') ?? '-'),
                    ]),

                // القسم الثاني: بيانات الطلب والمبيعات المرتبطة (علاقة الـ One-to-One)
                Section::make('الطلب والمبيعات المرتبطة')
                    ->columnSpan(1)
                    ->schema([
                        Placeholder::make('order_id')
                            ->label('رقم الطلب (Order ID):')
                            ->content(fn ($record) => $record?->order ? "#{$record->order->id}" : 'لا يوجد طلب مرتبط'),

                        Placeholder::make('cashier')
                            ->label('الكاشير المسئول:')
                            ->content(fn ($record) => $record?->order?->employee?->name ?? '-'),

                        Placeholder::make('customer')
                            ->label('العميل:')
                            ->content(fn ($record) => $record?->order?->customer?->name ?? 'عميل نقدي'),

                        Placeholder::make('total_price')
                            ->label('إجمالي الصافي المستحق:')
                            ->content(fn ($record) => $record?->order ? number_format($record->order->total_price_cents / 100, 2) . ' ج.م' : '0.00 ج.م'),
                    ]),
            ])
            ->columns(2);

    }
}
