<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        Employee::factory()->create([
            'username' => 'cashier1',
            'password' => 'password',
        ]);

        Employee::factory()->create([
            'username' => 'cashier2',
            'password' => 'password',
        ]);

        Employee::factory(3)->create();
    }
}
