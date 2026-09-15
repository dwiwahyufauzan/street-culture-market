<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductUpsell;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductUpsell>
 */
class ProductUpsellFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'upsell_id' => Product::factory(),
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}
