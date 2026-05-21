<?php

namespace App\Filament\Resources\Products\Tables;

use App\Support\MediaPath;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('product_img')
                    ->state(fn ($record): ?string => MediaPath::url($record->product_img))
                    ->checkFileExistence(false)
                    ->circular()
                    ->size(80),
                TextColumn::make('product_name')
                    ->searchable(),
                TextColumn::make('product_desc')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->searchable(),
                IconColumn::make('is_available')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('التصنيف (Category)')
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable(),
                TrashedFilter::make()
                    ->label('المهملات (Soft Deleted)'),
                TernaryFilter::make('is_available')
                    ->label('حالة الإتاحة بالمتجر')
                    ->placeholder('كل المنتجات')
                    ->trueLabel('المتاحة فقط')
                    ->falseLabel('المخفية/غير المتاحة'),
            ])
            ->recordActions([
                EditAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
