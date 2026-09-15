<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'T-Shirts & Tops',
                'slug' => 't-shirts-tops',
                'description' => 'Heavyweight cotton graphic tees and minimalist essentials.',
                'image' => 'categories/tees.jpg',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Hoodies & Sweats',
                'slug' => 'hoodies-sweats',
                'description' => 'Oversized fleece hoodies and premium loopback crewnecks.',
                'image' => 'categories/hoodies.jpg',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Jackets & Outerwear',
                'slug' => 'jackets-outerwear',
                'description' => 'Tactical bombers, track jackets, and technical parkas.',
                'image' => 'categories/outerwear.jpg',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Pants & Cargo',
                'slug' => 'pants-cargo',
                'description' => 'Relaxed fit cargos, denim, and wide-leg work pants.',
                'image' => 'categories/pants.jpg',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Headwear & Caps',
                'slug' => 'headwear-caps',
                'description' => '5-panel caps, dad hats, and heavyweight beanies.',
                'image' => 'categories/caps.jpg',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Crossbody bags, belts, keychains, and street lifestyle goods.',
                'image' => 'categories/accessories.jpg',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $data) {
            Category::updateOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
