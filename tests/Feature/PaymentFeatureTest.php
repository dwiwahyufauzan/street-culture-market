<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_payment_page_for_their_order(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'total' => 650000,
            'subtotal' => 625000,
            'shipping_cost' => 25000,
        ]);

        $product = Product::factory()->create(['name' => 'Washed Heavy T-Shirt']);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 625000,
            'quantity' => 1,
            'subtotal' => 625000,
        ]);

        $response = $this->actingAs($user)->get(route('checkout.payment', $order));

        $response->assertStatus(200);
        $response->assertViewIs('checkout.payment');
        $response->assertSee($order->order_number);
        $response->assertSee('Washed Heavy T-Shirt');
        $response->assertSee('Complete Your Payment');
        $response->assertSee('Pay Now');
    }

    public function test_guest_with_session_can_view_payment_page(): void
    {
        $order = Order::factory()->create([
            'user_id' => null,
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);

        $response = $this->withSession(['placed_order_id' => $order->id])
            ->get(route('checkout.payment', $order));

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
    }

    public function test_other_customer_cannot_view_order_payment_page(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $stranger = User::factory()->create(['role' => 'customer']);

        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'payment_status' => 'unpaid',
        ]);

        $response = $this->actingAs($stranger)->get(route('checkout.payment', $order));

        $response->assertStatus(403);
    }

    public function test_already_paid_order_redirects_to_checkout_success(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);

        $response = $this->actingAs($user)->get(route('checkout.payment', $order));

        $response->assertRedirect(route('checkout.success', $order));
        $response->assertSessionHas('info', 'This order has already been paid.');
    }

    public function test_midtrans_service_generates_snap_token(): void
    {
        $order = Order::factory()->create([
            'total' => 500000,
            'subtotal' => 475000,
            'shipping_cost' => 25000,
            'shipping_method' => 'JNE_REG',
        ]);

        $token = app(MidtransService::class)->createSnapToken($order);

        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }

    public function test_midtrans_webhook_signature_mismatch_returns_403(): void
    {
        $order = Order::factory()->create(['payment_status' => 'unpaid']);

        $payload = [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => (string) (int) $order->total,
            'signature_key' => 'invalid-signature-hash-string',
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('payment.notification'), $payload);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Invalid signature']);
    }

    public function test_midtrans_webhook_order_not_found_returns_404(): void
    {
        $orderId = 'NONEXISTENT-ORDER-999';
        $statusCode = '200';
        $grossAmount = '500000';
        $serverKey = config('services.midtrans.server_key');
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ];

        $response = $this->postJson(route('payment.notification'), $payload);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Order not found']);
    }

    public function test_midtrans_webhook_settlement_updates_order_to_paid_and_processing(): void
    {
        $order = Order::factory()->create([
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'total' => 750000,
        ]);

        $orderId = $order->order_number;
        $statusCode = '200';
        $grossAmount = (string) (int) $order->total;
        $serverKey = (string) config('services.midtrans.server_key');
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'fraud_status' => 'accept',
            'payment_type' => 'gopay',
            'transaction_id' => 'TRX-MIDTRANS-12345',
        ];

        $response = $this->postJson(route('payment.notification'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'ok']);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertEquals('gopay', $order->payment_method);
        $this->assertEquals('TRX-MIDTRANS-12345', $order->midtrans_order_id);
    }

    public function test_midtrans_webhook_challenge_keeps_order_pending(): void
    {
        $order = Order::factory()->create([
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'total' => 1200000,
        ]);

        $orderId = $order->order_number;
        $statusCode = '200';
        $grossAmount = (string) (int) $order->total;
        $serverKey = (string) config('services.midtrans.server_key');
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'capture',
            'fraud_status' => 'challenge',
            'payment_type' => 'credit_card',
        ];

        $response = $this->postJson(route('payment.notification'), $payload);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('unpaid', $order->payment_status);
        $this->assertEquals('pending', $order->status);
    }

    public function test_midtrans_webhook_cancel_or_expire_updates_order_to_cancelled(): void
    {
        $order = Order::factory()->create([
            'payment_status' => 'unpaid',
            'status' => 'pending',
            'total' => 450000,
        ]);

        $orderId = $order->order_number;
        $statusCode = '202';
        $grossAmount = (string) (int) $order->total;
        $serverKey = (string) config('services.midtrans.server_key');
        $signature = hash('sha512', $orderId.$statusCode.$grossAmount.$serverKey);

        $payload = [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
            'fraud_status' => 'accept',
        ];

        $response = $this->postJson(route('payment.notification'), $payload);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('unpaid', $order->payment_status);
        $this->assertEquals('cancelled', $order->status);
    }

    public function test_payment_finish_callback_redirects_to_checkout_success(): void
    {
        $order = Order::factory()->create();

        $response = $this->get(route('payment.finish', ['order_id' => $order->order_number]));

        $response->assertRedirect(route('checkout.success', $order));
    }

    public function test_payment_simulation_endpoint_in_testing_environment(): void
    {
        $order = Order::factory()->create([
            'payment_status' => 'unpaid',
            'status' => 'pending',
        ]);

        $response = $this->get(route('payment.simulate', $order));

        $response->assertRedirect(route('checkout.success', $order));

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
    }
}
