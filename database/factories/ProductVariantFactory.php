<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        $priceCents = fake()->numberBetween(199, 9999);

        return [
            'product_name' => fake()->words(2, true),
            'price_cents' => $priceCents,
            'stock' => fake()->numberBetween(0, 100),
            'color' => fake()->safeColorName(),
            'sku' => strtoupper(fake()->bothify('???-####')),
            'size' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL']),
            'product_id' => Product::factory(),
        ];
    }
}
