<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $price = fake()->randomFloat(2, 150000, 1500000);
        $hasDiscount = fake()->boolean(40);
        $salePrice = $hasDiscount ? round($price * fake()->randomFloat(2, 0.7, 0.9), -3) : null;

        return [
            'category_id' => Category::factory(),
            'name' => ucwords($name),
            'slug' => Str::slug($name).'-'.Str::random(5),
            'description' => fake()->paragraphs(3, true),
            'price' => round($price, -3),
            'sale_price' => $salePrice,
            'sku' => 'SCM-'.strtoupper(Str::random(6)),
            'weight' => fake()->numberBetween(200, 1200),
            'is_active' => true,
            'is_featured' => fake()->boolean(30),
            'meta_title' => ucwords($name).' — Street Culture Market',
            'meta_description' => fake()->sentence(),
        ];
    }
}
