<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_and_products_relationship(): void
    {
        $category = Category::factory()->create(['name' => 'T-Shirts']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Heavyweight Tee',
            'price' => 200000,
            'sale_price' => 180000,
        ]);

        $this->assertEquals('T-Shirts', $product->category->name);
        $this->assertTrue($category->products->contains($product));
        $this->assertEquals(180000, $product->effective_price);
        $this->assertTrue($product->has_discount);
    }

    public function test_product_variants_and_stock_aggregation(): void
    {
        $product = Product::factory()->create();
        ProductVariant::factory()->create(['product_id' => $product->id, 'size' => 'M', 'stock' => 10]);
        ProductVariant::factory()->create(['product_id' => $product->id, 'size' => 'L', 'stock' => 15]);

        $this->assertCount(2, $product->variants);
        $this->assertEquals(25, $product->total_stock);
    }

    public function test_upsell_relationships(): void
    {
        $basicTee = Product::factory()->create(['name' => 'Basic Tee', 'price' => 150000]);
        $premiumTee = Product::factory()->create(['name' => 'Premium Acid Wash Tee', 'price' => 300000]);

        $basicTee->upsellProducts()->attach($premiumTee->id, ['sort_order' => 1]);

        $this->assertCount(1, $basicTee->upsellProducts);
        $this->assertEquals('Premium Acid Wash Tee', $basicTee->upsellProducts->first()->name);
        $this->assertTrue($basicTee->upsellProducts->first()->price > $basicTee->price);
    }

    public function test_cross_sell_relationships(): void
    {
        $tee = Product::factory()->create(['name' => 'Graphic Tee']);
        $cargo = Product::factory()->create(['name' => 'Cargo Pants']);
        $cap = Product::factory()->create(['name' => '5-Panel Cap']);

        $tee->crossSells()->attach([
            $cargo->id => ['sort_order' => 1],
            $cap->id => ['sort_order' => 2],
        ]);

        $this->assertCount(2, $tee->crossSells);
        $this->assertEquals('Cargo Pants', $tee->crossSells->first()->name);
    }

    public function test_order_and_order_items_relationship(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'price' => 200000,
            'quantity' => 2,
            'subtotal' => 400000,
        ]);

        $this->assertEquals($user->id, $order->user->id);
        $this->assertCount(1, $order->items);
        $this->assertEquals(400000, $order->items->first()->subtotal);
    }

    public function test_user_roles_and_panel_access(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $ownerUser = User::factory()->create(['role' => 'owner']);
        $customerUser = User::factory()->create(['role' => 'customer']);

        $adminPanel = (new Panel)->id('admin');
        $ownerPanel = (new Panel)->id('owner');

        // Admin can access admin panel but not owner panel
        $this->assertTrue($adminUser->isAdmin());
        $this->assertTrue($adminUser->canAccessPanel($adminPanel));
        $this->assertFalse($adminUser->canAccessPanel($ownerPanel));

        // Owner can access both admin panel and owner panel
        $this->assertTrue($ownerUser->isOwner());
        $this->assertTrue($ownerUser->canAccessPanel($adminPanel));
        $this->assertTrue($ownerUser->canAccessPanel($ownerPanel));

        // Customer cannot access either panel
        $this->assertTrue($customerUser->isCustomer());
        $this->assertFalse($customerUser->canAccessPanel($adminPanel));
        $this->assertFalse($customerUser->canAccessPanel($ownerPanel));
    }
}
