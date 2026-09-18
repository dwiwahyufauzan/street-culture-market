<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_customer_cannot_access_admin_panel(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Catalog');
        $response->assertSee('Orders');
    }

    public function test_owner_user_can_access_admin_dashboard(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_product_resource_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['name' => 'SCM Tech Cargo', 'is_active' => true]);

        $response = $this->actingAs($admin)->get('/admin/products');

        $response->assertStatus(200);
        $response->assertSee('SCM Tech Cargo');
    }

    public function test_admin_can_view_category_resource_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create(['name' => 'Heavyweight Hoodies']);

        $response = $this->actingAs($admin)->get('/admin/categories');

        $response->assertStatus(200);
        $response->assertSee('Heavyweight Hoodies');
    }

    public function test_admin_can_view_order_resource_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create(['customer_name' => 'Bambang Pamungkas']);

        $response = $this->actingAs($admin)->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee('Bambang Pamungkas');
    }

    public function test_admin_can_view_banner_resource_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/banners');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_user_resource_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee($admin->email);
    }
}
