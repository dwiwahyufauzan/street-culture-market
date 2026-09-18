<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_account_hub_to_login(): void
    {
        $response = $this->get(route('account.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_customer_can_view_account_overview(): void
    {
        $user = User::factory()->create([
            'name' => 'Fauzan Streetwear',
            'role' => 'customer',
        ]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'total' => 450000,
        ]);

        $response = $this->actingAs($user)->get(route('account.index'));

        $response->assertStatus(200);
        $response->assertViewIs('account.index');
        $response->assertSee('Fauzan Streetwear');
        $response->assertSee($order->order_number);
    }

    public function test_account_orders_page_renders_paginated_orders(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create(['name' => 'Acid Wash Tee', 'is_active' => true]);

        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 350000,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Acid Wash Tee',
            'size' => 'L',
            'quantity' => 1,
            'price' => 350000,
            'subtotal' => 350000,
        ]);

        $response = $this->actingAs($user)->get(route('account.orders'));

        $response->assertStatus(200);
        $response->assertViewIs('account.orders');
        $response->assertSee($order->order_number);
        $response->assertSee('Acid Wash Tee');
    }

    public function test_customer_can_view_their_own_order_detail(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'total' => 750000,
        ]);

        $response = $this->actingAs($user)->get(route('account.orders.show', $order));

        $response->assertStatus(200);
        $response->assertViewIs('account.order-detail');
        $response->assertSee($order->order_number);
        $response->assertSee('Order Invoice');
    }

    public function test_customer_cannot_view_another_users_order_detail(): void
    {
        $userA = User::factory()->create(['role' => 'customer']);
        $userB = User::factory()->create(['role' => 'customer']);

        $orderOfB = Order::factory()->create([
            'user_id' => $userB->id,
        ]);

        $response = $this->actingAs($userA)->get(route('account.orders.show', $orderOfB));

        $response->assertStatus(403);
    }

    public function test_account_wishlist_redirects_to_wishlist_index(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->get(route('account.wishlist'));

        $response->assertRedirect(route('wishlist.index'));
    }

    public function test_login_page_renders_with_scm_streetwear_branding(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
        $response->assertSee('SCM');
        $response->assertSee('Sign In');
        $response->assertSee('Email Address');
    }

    public function test_customer_can_register_with_profile_fields(): void
    {
        $payload = [
            'name' => 'Bintang Pratama',
            'email' => 'bintang@scm.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081234567890',
            'city' => 'Bandung',
        ];

        $response = $this->post(route('register'), $payload);

        $this->assertAuthenticated();
        $user = User::where('email', 'bintang@scm.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals('Bintang Pratama', $user->name);
        $this->assertEquals('customer', $user->role);
        $this->assertEquals('081234567890', $user->phone);
        $this->assertEquals('Bandung', $user->city);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_customer_can_update_profile_address_and_phone(): void
    {
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'orig@scm.test',
        ]);

        $payload = [
            'name' => 'Updated Name',
            'email' => 'orig@scm.test',
            'phone' => '081987654321',
            'address' => 'Jl. Dago Asri No. 45',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40135',
        ];

        $response = $this->actingAs($user)->patch(route('profile.update'), $payload);

        $response->assertRedirect(route('profile.edit'));
        $user->refresh();

        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('081987654321', $user->phone);
        $this->assertEquals('Jl. Dago Asri No. 45', $user->address);
        $this->assertEquals('Bandung', $user->city);
        $this->assertEquals('40135', $user->postal_code);
    }
}
