<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'total_price_cents' => fake()->numberBetween(10000, 900000),
            'payment_method' => fake()->randomElement(['cash', 'card']),
            'customer_id' => User::factory(),
            'employee_id' => Employee::factory(),
        ];
    }
}
