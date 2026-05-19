<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $employees = Employee::all();
        $variants = ProductVariant::all();

        if ($customers->isEmpty() || $employees->isEmpty() || $variants->isEmpty()) {
            $this->command?->warn('OrderSeeder skipped: seed customers, employees, and product variants first.');

            return;
        }

        foreach (range(1, 8) as $index) {
            $order = Order::factory()->create([
                'payment_method' => $index % 2 === 0 ? 'card' : 'cash',
                'customer_id' => $customers->random()->id,
                'employee_id' => $employees->random()->id,
                'total_price_cents' => 0,
            ]);

            $lineItems = $variants->random(min(3, $variants->count()));
            $totalCents = 0;

            foreach ($lineItems as $variant) {
                $quantity = fake()->numberBetween(1, 3);
                $lineTotal = $variant->price_cents * $quantity;
                $totalCents += $lineTotal;

                OrderItem::factory()->create([
                    'order_id' => $order->id,
                    'variant_id' => $variant->id,
                    'product_name' => $variant->product_name,
                    'current_price_cents' => $variant->price_cents,
                    'quantity' => $quantity,
                ]);
            }

            $order->update(['total_price_cents' => $totalCents]);
        }
    }
}
