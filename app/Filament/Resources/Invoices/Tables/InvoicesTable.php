<?php

namespace App\Filament\Resources\Invoices\Tables;

use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. رقم الفاتورة
                TextColumn::make('id')
                    ->label('رقم الفاتورة')
                    ->sortable()
                    ->searchable()
                    ->prefix('#')
                    ->weight('bold'),

                // 2. رقم الطلب المربوط بها من علاقة الـ order
                TextColumn::make('order.id')
                    ->label('رقم الطلب المرتبط')
                    ->sortable()
                    ->searchable()
                    ->placeholder('بدون طلب')
                    ->prefix('#'),

                // 3. اسم الكاشير اللي قفل البيعة
                TextColumn::make('order.employee.name')
                    ->label('الكاشير')
                    ->searchable()
                    ->default('-'),

                // 4. إجمالي الفاتورة مأخوذ من جدول الطلبات
                TextColumn::make('order.total_price_cents')
                    ->label('إجمالي الفاتورة')
                    ->money('EGP')
                    ->state(fn ($record) => $record->order ? $record->order->total_price_cents / 100 : 0)
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->color('success'),

                // 5. تاريخ الفاتورة
                TextColumn::make('created_at')
                    ->label('تاريخ الفاتورة')
                    ->dateTime('Y-m-d h:i A')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make()->label('عرض التفاصيل'),
                Action::make('download_pdf')
                    ->label('تحميل PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Invoice $record) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', ['invoice' => $record]);

                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, "invoice-{$record->id}.pdf");
                    }),
            ])
            ->bulkActions([
                // خالي لحماية الفواتير من الحذف الجماعي الخطأ
            ]);
    }
}
