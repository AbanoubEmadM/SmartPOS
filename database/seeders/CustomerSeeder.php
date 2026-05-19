<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $names = [
            'Ahmed Mohsen',
            'Omar Elsayed',
            'Abanoub Emad',
            'Karas Emad',
        ];

        foreach ($names as $name) {
            Customer::factory()->create(['name' => $name]);
        }
    }
}
