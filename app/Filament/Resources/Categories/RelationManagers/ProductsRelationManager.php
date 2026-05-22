<?php

namespace App\Filament\Resources\Categories\RelationManagers;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Products\ProductResource;
use App\Support\MediaPath;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $relatedResource = ProductResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product_name')
            ->columns([
                ImageColumn::make('product_img')
                    ->state(fn ($record): ?string => MediaPath::url($record->product_img))
                    ->checkFileExistence(false)
                    ->circular()
                    ->size(80),
                TextColumn::make('product_name'),
                TextColumn::make('product_desc'),
            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
