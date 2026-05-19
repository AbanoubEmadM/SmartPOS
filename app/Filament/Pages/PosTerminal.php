<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PosTerminal extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-computer-desktop';

    protected static ?string $navigationLabel = 'شاشة البيع (POS)';

    protected static ?string $title = 'نقطة البيع';

    // ✅ الصح هنا تكون عادية مش static عشان تطابق الكلاس الأب
    protected string $view = 'filament.pages.pos-terminal';

    public static function canView(): bool
    {
        /** @var \App\Models\Employee $user */
        $user = Auth::user();
        return $user && in_array($user->role, ['admin', 'cashier']);
    }
}
