<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'product_name' => fake()->words(2, true),
            'current_price_cents' => fake()->numberBetween(1990, 17900),
            'quantity' => fake()->numberBetween(1, 3),
            'order_id' => Order::factory(),
            'variant_id' => ProductVariant::factory(),
        ];
    }
}
