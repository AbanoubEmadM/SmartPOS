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
        $variant = ProductVariant::factory()->create();
        $quantity = fake()->numberBetween(1, 3);

        return [
            'product_name' => $variant->product_name,
            'current_price_cents' => $variant->price_cents,
            'quantity' => $quantity,
            'order_id' => Order::factory(),
            'variant_id' => $variant->id,
        ];
    }
}
