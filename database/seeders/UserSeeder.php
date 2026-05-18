<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@smartpos.test',
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => 'password',
        ]);

        User::factory()->create([
            'name' => 'John Customer',
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        User::factory(7)->create();
    }
}
