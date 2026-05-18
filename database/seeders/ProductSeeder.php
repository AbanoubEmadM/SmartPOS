<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Beverages' => [
                [
                    'product_name' => 'Cola',
                    'product_desc' => 'Classic carbonated soft drink',
                    'variants' => [
                        ['product_name' => 'Cola 330ml', 'price_cents' => 150, 'size' => '330ml', 'color' => 'Red', 'stock' => 120],
                        ['product_name' => 'Cola 500ml', 'price_cents' => 200, 'size' => '500ml', 'color' => 'Red', 'stock' => 80],
                    ],
                ],
                [
                    'product_name' => 'Mineral Water',
                    'product_desc' => 'Still mineral water',
                    'variants' => [
                        ['product_name' => 'Water 500ml', 'price_cents' => 100, 'size' => '500ml', 'color' => 'Clear', 'stock' => 200],
                    ],
                ],
            ],
            'Snacks' => [
                [
                    'product_name' => 'Potato Chips',
                    'product_desc' => 'Crispy salted potato chips',
                    'variants' => [
                        ['product_name' => 'Chips Original', 'price_cents' => 250, 'size' => 'M', 'color' => 'Yellow', 'stock' => 60],
                        ['product_name' => 'Chips BBQ', 'price_cents' => 250, 'size' => 'M', 'color' => 'Brown', 'stock' => 45],
                    ],
                ],
            ],
            'Groceries' => [
                [
                    'product_name' => 'Pasta',
                    'product_desc' => 'Durum wheat spaghetti',
                    'variants' => [
                        ['product_name' => 'Spaghetti 500g', 'price_cents' => 180, 'size' => '500g', 'color' => 'Beige', 'stock' => 90],
                    ],
                ],
                [
                    'product_name' => 'Rice',
                    'product_desc' => 'Long grain white rice',
                    'variants' => [
                        ['product_name' => 'Rice 1kg', 'price_cents' => 320, 'size' => '1kg', 'color' => 'White', 'stock' => 70],
                        ['product_name' => 'Rice 5kg', 'price_cents' => 1400, 'size' => '5kg', 'color' => 'White', 'stock' => 25],
                    ],
                ],
            ],
        ];

        foreach ($catalog as $categoryName => $products) {
            $category = Category::query()->where('name', $categoryName)->first();

            if (! $category) {
                continue;
            }

            foreach ($products as $productData) {
                $product = Product::factory()->create([
                    'product_name' => $productData['product_name'],
                    'product_desc' => $productData['product_desc'],
                    'product_img' => 'products/'.str($productData['product_name'])->slug().'.jpg',
                    'category_id' => $category->id,
                    'is_available' => true,
                ]);

                foreach ($productData['variants'] as $index => $variantData) {
                    ProductVariant::factory()->create([
                        ...$variantData,
                        'sku' => strtoupper(str($productData['product_name'])->slug()).'-'.($index + 1),
                        'product_id' => $product->id,
                    ]);
                }
            }
        }

        Product::factory(5)
            ->has(ProductVariant::factory()->count(2), 'variants')
            ->create();
    }
}
