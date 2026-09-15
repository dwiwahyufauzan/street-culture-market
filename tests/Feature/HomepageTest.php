<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_successfully_with_data(): void
    {
        $category = Category::factory()->create(['name' => 'Hoodies & Sweats', 'slug' => 'hoodies-sweats']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'SCM Heavyweight Boxy Tee',
            'is_featured' => true,
            'is_active' => true,
        ]);
        $banner = Banner::factory()->create([
            'title' => 'AW26 STREET DROP',
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('home.index');
        $response->assertViewHasAll(['banners', 'newArrivals', 'featuredProducts', 'categories']);
        $response->assertSee('SCM Heavyweight Boxy Tee');
        $response->assertSee('AW26 STREET DROP');
    }

    public function test_cart_count_endpoint_returns_json(): void
    {
        $response = $this->getJson('/cart/count');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'items',
            'count',
            'total',
        ]);
    }

    public function test_catalog_page_filters_by_category(): void
    {
        $catTee = Category::factory()->create(['name' => 'T-Shirts', 'slug' => 't-shirts']);
        $catHoodie = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);

        $tee = Product::factory()->create(['category_id' => $catTee->id, 'name' => 'Vintage Tee', 'is_active' => true]);
        $hoodie = Product::factory()->create(['category_id' => $catHoodie->id, 'name' => 'Fleece Hoodie', 'is_active' => true]);

        $response = $this->get('/products?category=t-shirts');

        $response->assertStatus(200);
        $response->assertSee('Vintage Tee');
        $response->assertDontSee('Fleece Hoodie');
    }

    public function test_product_detail_page_renders_with_upsells_and_cross_sells(): void
    {
        $mainProduct = Product::factory()->create(['name' => 'Standard Tee', 'slug' => 'standard-tee', 'is_active' => true]);
        $upsellProduct = Product::factory()->create(['name' => 'Luxury Distressed Tee', 'is_active' => true]);
        $crossSellProduct = Product::factory()->create(['name' => 'Utility Cargo Pants', 'is_active' => true]);

        $mainProduct->upsellProducts()->attach($upsellProduct->id, ['sort_order' => 1]);
        $mainProduct->crossSells()->attach($crossSellProduct->id, ['sort_order' => 1]);

        $response = $this->get('/products/standard-tee');

        $response->assertStatus(200);
        $response->assertSee('Standard Tee');
        $response->assertSee('Luxury Distressed Tee');
        $response->assertSee('Utility Cargo Pants');
    }
}
