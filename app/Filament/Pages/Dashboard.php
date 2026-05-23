<?php

namespace App\Filament\Pages;

use App\App\Filament\Widgets\StatsOverview;
use Filament\Pages\Page;
use Filament\Widgets\AccountWidget;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-home';
    protected function getHeaderWidgets(): array
    {
        return [
            StatsOverview::class
        ];
    }
}
