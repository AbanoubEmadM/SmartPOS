<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable; // 👈 مهم جداً لعمل الـ Login
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable implements FilamentUser // 👈 نخليه يطبق حماية Filament
{
    use HasFactory, Notifiable;

    protected $table = 'employees';

    // يفضل تستخدم fillable أو سيب الـ guarded فاضي عشان لارافل 11
    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // السماح بدخول لوحة تحكم فلامنت
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->role === 'admin';
        }
        return true;
    }
        // العلاقة الخاصة بالطلبات
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'employee_id');
    }
}
