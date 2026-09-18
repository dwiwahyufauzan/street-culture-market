<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_renders_without_query(): void
    {
        $response = $this->get(route('search.index'));

        $response->assertStatus(200);
        $response->assertViewIs('search.index');
        $response->assertSee('Explore Archive');
        $response->assertSee('Found 0 Garments');
    }

    public function test_search_finds_products_by_name(): void
    {
        $hoodie = Product::factory()->create([
            'name' => 'SCM Oversized Vintage Hoodie',
            'is_active' => true,
        ]);

        $tee = Product::factory()->create([
            'name' => 'Acid Wash Heavy Tee',
            'is_active' => true,
        ]);

        $response = $this->get(route('search.index', ['q' => 'Hoodie']));

        $response->assertStatus(200);
        $response->assertSee('SCM Oversized Vintage Hoodie');
        $response->assertDontSee('Acid Wash Heavy Tee');
    }

    public function test_search_finds_products_by_description(): void
    {
        $product = Product::factory()->create([
            'name' => 'Urban Tactical Parka',
            'description' => 'Engineered with durable waterproof ripstop fabric for extreme conditions.',
            'is_active' => true,
        ]);

        $response = $this->get(route('search.index', ['q' => 'ripstop']));

        $response->assertStatus(200);
        $response->assertSee('Urban Tactical Parka');
    }

    public function test_inactive_products_are_excluded_from_search(): void
    {
        $inactiveProduct = Product::factory()->create([
            'name' => 'Unreleased Prototype Cargo',
            'is_active' => false,
        ]);

        $response = $this->get(route('search.index', ['q' => 'Prototype']));

        $response->assertStatus(200);
        $response->assertDontSee('Unreleased Prototype Cargo');
        $response->assertSee('No Garments Found');
    }

    public function test_search_suggest_endpoint_returns_json_results(): void
    {
        $product = Product::factory()->create([
            'name' => 'Washed Flannel Overshirt',
            'price' => 450000,
            'sale_price' => null,
            'is_active' => true,
        ]);

        $response = $this->getJson(route('search.suggest', ['q' => 'Flannel']));

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'id' => $product->id,
            'name' => 'Washed Flannel Overshirt',
            'slug' => $product->slug,
            'price' => 450000.0,
            'formatted_price' => 'Rp 450.000',
        ]);
    }

    public function test_search_suggest_endpoint_returns_empty_when_query_is_too_short(): void
    {
        Product::factory()->create([
            'name' => 'Acid Wash Tee',
            'is_active' => true,
        ]);

        $response = $this->getJson(route('search.suggest', ['q' => 'A']));

        $response->assertStatus(200);
        $response->assertExactJson([]);
    }

    public function test_search_results_can_be_filtered_by_category(): void
    {
        $catHoodies = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);
        $catPants = Category::factory()->create(['name' => 'Pants', 'slug' => 'pants']);

        $hoodie = Product::factory()->create([
            'category_id' => $catHoodies->id,
            'name' => 'Washed Street Hoodie',
            'is_active' => true,
        ]);

        $pants = Product::factory()->create([
            'category_id' => $catPants->id,
            'name' => 'Washed Cargo Pants',
            'is_active' => true,
        ]);

        $response = $this->get(route('search.index', [
            'q' => 'Washed',
            'category' => 'hoodies',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Washed Street Hoodie');
        $response->assertDontSee('Washed Cargo Pants');
    }

    public function test_search_results_can_be_filtered_by_size(): void
    {
        $productM = Product::factory()->create([
            'name' => 'Tech Vest Medium',
            'is_active' => true,
        ]);
        ProductVariant::factory()->create([
            'product_id' => $productM->id,
            'size' => 'M',
            'stock' => 10,
        ]);

        $productXL = Product::factory()->create([
            'name' => 'Tech Vest XL',
            'is_active' => true,
        ]);
        ProductVariant::factory()->create([
            'product_id' => $productXL->id,
            'size' => 'XL',
            'stock' => 5,
        ]);

        $response = $this->get(route('search.index', [
            'q' => 'Tech Vest',
            'size' => 'XL',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Tech Vest XL');
        $response->assertDontSee('Tech Vest Medium');
    }

    public function test_search_results_can_be_sorted_by_price(): void
    {
        $cheap = Product::factory()->create([
            'name' => 'Graphic Cap A',
            'price' => 150000,
            'is_active' => true,
        ]);

        $expensive = Product::factory()->create([
            'name' => 'Graphic Cap B',
            'price' => 750000,
            'is_active' => true,
        ]);

        $responseAsc = $this->get(route('search.index', [
            'q' => 'Graphic Cap',
            'sort' => 'price_asc',
        ]));

        $responseAsc->assertStatus(200);
        $responseAsc->assertSeeInOrder(['Graphic Cap A', 'Graphic Cap B']);

        $responseDesc = $this->get(route('search.index', [
            'q' => 'Graphic Cap',
            'sort' => 'price_desc',
        ]));

        $responseDesc->assertStatus(200);
        $responseDesc->assertSeeInOrder(['Graphic Cap B', 'Graphic Cap A']);
    }
}
