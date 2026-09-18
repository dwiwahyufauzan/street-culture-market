<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_listing_renders_with_active_products(): void
    {
        $category = Category::factory()->create(['name' => 'Outerwear', 'slug' => 'outerwear']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Tactical Bomber Jacket',
            'is_active' => true,
        ]);
        $inactiveProduct = Product::factory()->create([
            'name' => 'Hidden Draft Jacket',
            'is_active' => false,
        ]);

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertSee('Tactical Bomber Jacket');
        $response->assertDontSee('Hidden Draft Jacket');
    }

    public function test_catalog_filters_by_category(): void
    {
        $catTee = Category::factory()->create(['name' => 'Tees', 'slug' => 'tees']);
        $catHoodie = Category::factory()->create(['name' => 'Hoodies', 'slug' => 'hoodies']);

        $tee = Product::factory()->create(['category_id' => $catTee->id, 'name' => 'Acid Wash Tee', 'is_active' => true]);
        $hoodie = Product::factory()->create(['category_id' => $catHoodie->id, 'name' => 'Zip-Up Hoodie', 'is_active' => true]);

        $response = $this->get(route('products.index', ['category' => 'tees']));

        $response->assertStatus(200);
        $response->assertSee('Acid Wash Tee');
        $response->assertDontSee('Zip-Up Hoodie');
    }

    public function test_catalog_filters_by_size(): void
    {
        $productM = Product::factory()->create(['name' => 'Tee Size M Only', 'is_active' => true]);
        ProductVariant::factory()->create(['product_id' => $productM->id, 'size' => 'M', 'stock' => 10]);

        $productXL = Product::factory()->create(['name' => 'Tee Size XL Only', 'is_active' => true]);
        ProductVariant::factory()->create(['product_id' => $productXL->id, 'size' => 'XL', 'stock' => 5]);

        $response = $this->get(route('products.index', ['size' => 'XL']));

        $response->assertStatus(200);
        $response->assertSee('Tee Size XL Only');
        $response->assertDontSee('Tee Size M Only');
    }

    public function test_catalog_filters_by_sale(): void
    {
        $saleProduct = Product::factory()->create([
            'name' => 'Discounted Cargo Pants',
            'price' => 500000,
            'sale_price' => 350000,
            'is_active' => true,
        ]);
        $regularProduct = Product::factory()->create([
            'name' => 'Regular Cargo Pants',
            'price' => 500000,
            'sale_price' => null,
            'is_active' => true,
        ]);

        $response = $this->get(route('products.index', ['sale' => '1']));

        $response->assertStatus(200);
        $response->assertSee('Discounted Cargo Pants');
        $response->assertDontSee('Regular Cargo Pants');
    }

    public function test_catalog_sorts_by_price(): void
    {
        $cheap = Product::factory()->create(['name' => 'Beanie Cap', 'price' => 150000, 'is_active' => true]);
        $expensive = Product::factory()->create(['name' => 'Leather Jacket', 'price' => 1200000, 'is_active' => true]);

        $responseAsc = $this->get(route('products.index', ['sort' => 'price_asc']));
        $responseAsc->assertStatus(200);
        $responseAsc->assertSeeInOrder(['Beanie Cap', 'Leather Jacket']);

        $responseDesc = $this->get(route('products.index', ['sort' => 'price_desc']));
        $responseDesc->assertStatus(200);
        $responseDesc->assertSeeInOrder(['Leather Jacket', 'Beanie Cap']);
    }

    public function test_catalog_searches_by_keyword(): void
    {
        $match = Product::factory()->create(['name' => 'Distressed Denim', 'is_active' => true]);
        $noMatch = Product::factory()->create(['name' => 'Canvas Tote Bag', 'is_active' => true]);

        $response = $this->get(route('products.index', ['q' => 'Denim']));

        $response->assertStatus(200);
        $response->assertSee('Distressed Denim');
        $response->assertDontSee('Canvas Tote Bag');
    }

    public function test_collection_page_renders_with_category_products(): void
    {
        $category = Category::factory()->create(['name' => 'Accessories', 'slug' => 'accessories']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Chain Necklace',
            'is_active' => true,
        ]);

        $response = $this->get(route('categories.show', 'accessories'));

        $response->assertStatus(200);
        $response->assertViewIs('categories.show');
        $response->assertSee('Accessories');
        $response->assertSee('Chain Necklace');
    }

    public function test_collection_page_returns_404_for_non_existing_slug(): void
    {
        $response = $this->get(route('categories.show', 'non-existing-collection'));

        $response->assertStatus(404);
    }

    public function test_product_detail_page_renders_with_variants_and_session_tracking(): void
    {
        $category = Category::factory()->create(['name' => 'Pants']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Heavy Parachute Cargo',
            'slug' => 'heavy-parachute-cargo',
            'price' => 650000,
            'is_active' => true,
        ]);

        ProductVariant::factory()->create(['product_id' => $product->id, 'size' => 'M', 'stock' => 8]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'size' => 'L', 'stock' => 12]);

        $response = $this->get(route('products.show', 'heavy-parachute-cargo'));

        $response->assertStatus(200);
        $response->assertViewIs('products.show');
        $response->assertSee('Heavy Parachute Cargo');
        $response->assertSee('M');
        $response->assertSee('L');
        $this->assertEquals([$product->id], session('recently_viewed'));
    }

    public function test_wishlist_toggle_requires_authentication_for_guest(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        // Guest JSON request
        $responseJson = $this->postJson(route('wishlist.toggle', $product->id));
        $responseJson->assertStatus(401);

        // Guest standard request
        $response = $this->post(route('wishlist.toggle', $product->id));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_customer_can_toggle_wishlist(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['is_active' => true]);

        // Add to wishlist
        $responseAdd = $this->actingAs($user)->postJson(route('wishlist.toggle', $product->id));
        $responseAdd->assertStatus(200);
        $responseAdd->assertJson([
            'status' => 'added',
            'is_wishlisted' => true,
            'count' => 1,
        ]);
        $this->assertDatabaseHas('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        // Remove from wishlist (toggle again)
        $responseRemove = $this->actingAs($user)->postJson(route('wishlist.toggle', $product->id));
        $responseRemove->assertStatus(200);
        $responseRemove->assertJson([
            'status' => 'removed',
            'is_wishlisted' => false,
            'count' => 0,
        ]);
        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_authenticated_customer_can_view_wishlist_index(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['name' => 'Grail Leather Vest', 'is_active' => true]);

        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->get(route('wishlist.index'));

        $response->assertStatus(200);
        $response->assertViewIs('wishlist.index');
        $response->assertSee('My Wishlist');
        $response->assertSee('Grail Leather Vest');
    }
}
