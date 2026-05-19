<?php

namespace App\Filament\Resources\ProductVariants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductVariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('product_name')
                    ->required(),
                TextInput::make('price_cents')
                    ->required()
                    ->numeric(),
                TextInput::make('stock')
                    ->required()
                    ->numeric(),
                TextInput::make('color')
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU'),
                TextInput::make('size')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'id')
                    ->required(),
            ]);
    }
}
