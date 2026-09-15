<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCrossSell;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductCrossSell>
 */
class ProductCrossSellFactory extends Factory
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
            'cross_sell_id' => Product::factory(),
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}
