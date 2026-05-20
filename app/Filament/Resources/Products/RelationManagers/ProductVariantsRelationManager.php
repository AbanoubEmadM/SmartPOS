<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\RestoreAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;

class ProductVariantsRelationManager extends RelationManager
{
    // اسم العلاقة المبرمجة في موديل Product
    protected static string $relationship = 'variants';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('sku')
            ->columns([
                TextColumn::make('sku')->label('الباركود')->searchable(),
                TextColumn::make('color')->label('اللون'),
                TextColumn::make('size')->label('المقاس'),
                TextColumn::make('price_cents')
                    ->label('السعر')
                    ->money('EGP')
                    ->state(fn ($record) => $record->price_cents / 100),
                TextColumn::make('stock')
                    ->label('المخزون')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => $state === 0 ? ' نفد من المخزن (Out of Stock)' : $state)

                    ->color(function (int $state, $record) {
                        if ($state === 0) {
                            return 'danger';
                        }

                        if ($state <= $record->low_stock_threshold) {
                            return 'warning';
                        }

                        return 'success';
                    })

                    ->weight(fn (int $state) => $state === 0 ? 'bold' : 'normal'),

            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('إضافة مقاس/لون جديد')
                    ->form([
                        Forms\Components\TextInput::make('sku')
                            ->label('الباركود / SKU')
                            ->required(),

                        Forms\Components\TextInput::make('color')
                            ->label('اللون')
                            ->required(),

                        Forms\Components\TextInput::make('size')
                            ->label('المقاس')
                            ->required(),

                        Forms\Components\TextInput::make('price_cents')
                            ->label('السعر (بالقرش)')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('stock')
                            ->label('الكمية في المخزن')
                            ->numeric()
                            ->required()
                            ->default(0),
                    ]),
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('sku')
                            ->label('الباركود / SKU')
                            ->unique(table: 'product_variants', ignoreRecord: true)
                            ->required(),

                        Forms\Components\TextInput::make('color')
                            ->label('اللون')
                            ->required(),

                        Forms\Components\TextInput::make('size')
                            ->label('المقاس')
                            ->required(),

                        Forms\Components\TextInput::make('price_cents')
                            ->label('السعر (بالجنيه)')
                            ->numeric()
                            ->minValue(0.01)
                            ->required()
                            ->formatStateUsing(fn (?int $state): ?float => $state ? $state / 100 : null)
                            ->dehydrateStateUsing(fn (?float $state): ?int => $state ? (int) ($state * 100) : null),
                        Forms\Components\TextInput::make('stock')
                            ->label('الكمية في المخزن')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('low_stock_threshold')
                            ->label('حد إنذار نقص المخزون')
                            ->numeric()
                            ->required()
                            ->default(5) // القيمة الافتراضية للإنذار
                            ->minValue(0),
                    ]),
                \Filament\Actions\DeleteAction::make(),
                RestoreAction::make(),

            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
