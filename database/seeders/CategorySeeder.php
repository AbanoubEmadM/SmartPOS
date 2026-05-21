<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            "Men's Shoes",
            "Women's Shoes",
            "Kids' Shoes",
            'Athletic & Running',
            'Boots',
            'Sandals & Slides',
        ];

        foreach ($categories as $name) {
            Category::factory()->create(['name' => $name]);
        }
    }
}
