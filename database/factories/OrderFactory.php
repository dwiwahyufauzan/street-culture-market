<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 200000, 2000000);
        $shippingCost = fake()->randomElement([15000, 25000, 35000]);
        $discount = fake()->randomElement([0, 20000, 50000]);
        $total = $subtotal + $shippingCost - $discount;

        return [
            'user_id' => User::factory(),
            'order_number' => Order::generateOrderNumber(),
            'status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered']),
            'payment_status' => fake()->randomElement(['unpaid', 'paid']),
            'payment_method' => fake()->randomElement(['gopay', 'bca_va', 'qris', 'bank_transfer']),
            'midtrans_order_id' => 'MID-'.fake()->uuid(),
            'midtrans_snap_token' => fake()->sha256(),
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => fake()->phoneNumber(),
            'shipping_address' => fake()->streetAddress(),
            'shipping_city' => fake()->city(),
            'shipping_province' => 'DKI Jakarta',
            'shipping_postal' => fake()->postcode(),
            'shipping_method' => 'JNE Reguler',
            'shipping_cost' => $shippingCost,
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'total' => $total,
            'notes' => fake()->sentence(),
        ];
    }
}
