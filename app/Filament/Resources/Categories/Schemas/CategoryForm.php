<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم التصنيف')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->live(onBlur: true) // بيراقب الخانة أول ما الماوس يخرج منها
                    ->afterStateUpdated(fn (string $operation, $state, Set $set) =>
                    $operation === 'create' ? $set('slug', Str::slug($state)) : null
                    ),

                TextInput::make('slug')
                    ->label('الرابط الدلالي (Slug)')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->disabled() // بنخليه مقفول عشان يتولد أوتوماتيك ومحدش يبوظه
                    ->dehydrated(), // عشان يتبعت للداتابيز حتى وهو disabled

                Toggle::make('is_active')
                    ->label('حالة التفعيل')
                    ->required()
                    ->default(true)
                    ->onColor('success')
                    ->offColor('danger'),
            ]);
    }
}
