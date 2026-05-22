<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable implements FilamentUser {
    /** @use HasFactory<\Database\Factories\EmployeeFactory> */
    use HasFactory, Notifiable;

    protected $table = 'employees';
    protected $guarded= ['employee_id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function orders(): HasMany{
        return $this->hasMany(Order::class, 'employee_id');
    }
}
