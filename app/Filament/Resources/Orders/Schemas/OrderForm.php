<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('total_price_cents')
                    ->label('السعر (بالجنيه)')
                    ->numeric()
                    ->minValue(0.01)
                    ->required()
                    ->formatStateUsing(fn (?int $state): ?float => $state ? $state / 100 : null)
                    ->dehydrateStateUsing(fn (?float $state): ?int => $state ? (int) ($state * 100) : null),
                Select::make('payment_method')
                    ->options(['cash' => 'Cash', 'card' => 'Card'])
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('employee_id')
                    ->relationship('employee', 'name')
                    ->required(),
            ]);
    }
}
