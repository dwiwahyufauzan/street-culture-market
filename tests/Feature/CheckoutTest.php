<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app(CartService::class)->clear();
    }

    public function test_empty_cart_redirects_from_checkout_to_cart_index(): void
    {
        $response = $this->get(route('checkout.index'));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_checkout_screen_renders_with_cart_items(): void
    {
        $category = Category::factory()->create(['name' => 'Tees']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'SCM Raw Cut Tee',
            'price' => 350000,
            'sale_price' => null,
            'is_active' => true,
        ]);
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'size' => 'L',
            'stock' => 10,
        ]);

        // Add to cart
        $cart = app(CartService::class);
        $cart->add($product->id, 'L', 1);

        $response = $this->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertViewIs('checkout.index');
        $response->assertSee('SCM Raw Cut Tee');
        $response->assertSee('JNE Express (Regular)');
        $response->assertSee('SiCepat BEST (Next Day)');
    }

    public function test_authenticated_customer_checkout_pre_fills_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Aditya Streetwear',
            'email' => 'aditya@streetculture.test',
            'phone' => '081299998888',
            'address' => 'Jl. Senopati No. 88',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
        ]);

        $product = Product::factory()->create([
            'price' => 450000,
            'sale_price' => null,
            'is_active' => true,
        ]);
        app(CartService::class)->add($product->id, 'M', 1);

        $response = $this->actingAs($user)->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('Aditya Streetwear');
        $response->assertSee('aditya@streetculture.test');
        $response->assertSee('Jl. Senopati No. 88');
    }

    public function test_guest_can_place_order_successfully(): void
    {
        $product = Product::factory()->create([
            'name' => 'Heavyweight Vintage Hoodie',
            'price' => 650000,
            'sale_price' => null,
            'is_active' => true,
        ]);
        ProductVariant::factory()->create([
            'product_id' => $product->id,
            'size' => 'XL',
            'stock' => 5,
        ]);

        $cart = app(CartService::class);
        $cart->add($product->id, 'XL', 2);

        $payload = [
            'customer_name' => 'Budi Santoso',
            'customer_email' => 'budi@gmail.com',
            'customer_phone' => '081345678901',
            'shipping_address' => 'Jl. Kemang Raya No. 12',
            'shipping_city' => 'Jakarta Selatan',
            'shipping_province' => 'DKI Jakarta',
            'shipping_postal' => '12730',
            'shipping_method' => 'JNE_REG',
            'notes' => 'Please leave with reception.',
        ];

        $response = $this->post(route('checkout.store'), $payload);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertNull($order->user_id);
        $this->assertEquals('Budi Santoso', $order->customer_name);
        $this->assertEquals('budi@gmail.com', $order->customer_email);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals('unpaid', $order->payment_status);
        $this->assertEquals(1300000, $order->subtotal);
        // Over 1.000.000 means free shipping:
        $this->assertEquals(0, $order->shipping_cost);
        $this->assertEquals(1300000, $order->total);

        // Check order items
        $this->assertCount(1, $order->items);
        $item = $order->items->first();
        $this->assertEquals('Heavyweight Vintage Hoodie', $item->product_name);
        $this->assertEquals('XL', $item->size);
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals(1300000, $item->subtotal);

        // Cart must be empty after order
        $this->assertEquals(0, $cart->count());

        $this->assertNotNull($order->midtrans_snap_token);
        $response->assertRedirect(route('checkout.payment', $order));
    }

    public function test_authenticated_customer_order_is_associated_with_user_id(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create([
            'price' => 250000,
            'sale_price' => null,
            'is_active' => true,
        ]);
        app(CartService::class)->add($product->id, 'S', 1);

        $payload = [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => '08123456789',
            'shipping_address' => 'Jl. Gatot Subroto',
            'shipping_city' => 'Bandung',
            'shipping_province' => 'Jawa Barat',
            'shipping_postal' => '40123',
            'shipping_method' => 'JNE_REG',
        ];

        $response = $this->actingAs($user)->post(route('checkout.store'), $payload);

        $order = Order::first();
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals(25000, $order->shipping_cost);
        $this->assertEquals(275000, $order->total);
        $this->assertNotNull($order->midtrans_snap_token);

        $response->assertRedirect(route('checkout.payment', $order));
    }

    public function test_checkout_validation_errors_when_fields_missing(): void
    {
        $product = Product::factory()->create([
            'price' => 300000,
            'sale_price' => null,
            'is_active' => true,
        ]);
        app(CartService::class)->add($product->id, 'M', 1);

        $response = $this->post(route('checkout.store'), []);

        $response->assertSessionHasErrors([
            'customer_name',
            'customer_email',
            'customer_phone',
            'shipping_address',
            'shipping_city',
            'shipping_province',
            'shipping_postal',
            'shipping_method',
        ]);
    }

    public function test_checkout_success_page_renders_order_details(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'customer_name' => 'Fauzan Streetwear',
            'customer_email' => 'fauzan@streetculture.test',
            'total' => 500000,
        ]);

        $response = $this->actingAs($user)->get(route('checkout.success', $order));

        $response->assertStatus(200);
        $response->assertViewIs('checkout.success');
        $response->assertSee($order->order_number);
        $response->assertSee('Fauzan Streetwear');
        $response->assertSee('Thank You For Your Order');
    }
}
