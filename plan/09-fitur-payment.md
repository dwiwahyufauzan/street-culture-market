# 09 — Fitur Payment (Midtrans)

## Overview
Integrasi **Midtrans** sebagai payment gateway Indonesia. Mendukung: GoPay, OVO, DANA, Transfer Bank, Virtual Account, QRIS, kartu kredit.

Midtrans sudah terinstall: `midtrans/midtrans-php v2.6.2`

---

## Konfigurasi .env

```env
MIDTRANS_SERVER_KEY=SB-Mid-server-xxxxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=SB-Mid-client-xxxxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SNAP_URL=https://app.sandbox.midtrans.com/snap/snap.js
```

**Cara dapat key**: Daftar di [dashboard.sandbox.midtrans.com](https://dashboard.sandbox.midtrans.com)

---

## Config File

Tambahkan ke `config/services.php`:

```php
'midtrans' => [
    'server_key'    => env('MIDTRANS_SERVER_KEY'),
    'client_key'    => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'snap_url'      => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js'),
],
```

---

## MidtransService

```php
// app/Services/MidtransService.php
<?php
namespace App\Services;

use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized  = true;
        Config::$is3ds        = true;
    }

    public function createSnapToken(Order $order): string
    {
        $params = [
            'transaction_details' => [
                'order_id'     => $order->order_number,
                'gross_amount' => (int) $order->total,
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'email'      => $order->customer_email,
                'phone'      => $order->customer_phone,
            ],
            'shipping_address' => [
                'first_name' => $order->customer_name,
                'phone'      => $order->customer_phone,
                'address'    => $order->shipping_address,
                'city'       => $order->shipping_city,
                'postal_code'=> $order->shipping_postal,
                'country_code' => 'IDN',
            ],
            'item_details' => $order->items->map(fn($item) => [
                'id'       => $item->product_id,
                'price'    => (int) $item->price,
                'quantity' => $item->quantity,
                'name'     => substr($item->product_name, 0, 50),
            ])->toArray(),
            'callbacks' => [
                'finish' => route('checkout.success', $order),
            ],
        ];

        return Snap::getSnapToken($params);
    }
}
```

---

## CheckoutController (Update dengan Midtrans)

```php
public function store(Request $request)
{
    $validated = $request->validate([...]);

    $order = DB::transaction(function () use ($request) {
        // Buat order
        $order = Order::create([...]);

        // Buat order items
        foreach ($this->cart->all() as $item) {
            OrderItem::create([...]);
        }

        // Generate Midtrans Snap Token
        $snapToken = app(MidtransService::class)->createSnapToken($order);
        $order->update(['midtrans_snap_token' => $snapToken]);

        $this->cart->clear();
        return $order;
    });

    return view('checkout.payment', compact('order'));
}
```

---

## View: Halaman Payment (`checkout/payment.blade.php`)

```html
<x-app-layout>
  <div class="max-w-lg mx-auto px-4 py-16 text-center">
    <h1 class="text-2xl font-bold uppercase tracking-tight mb-4">Complete Your Payment</h1>
    <p class="text-gray-600 mb-2">Order: <strong>{{ $order->order_number }}</strong></p>
    <p class="text-3xl font-bold mb-8">Rp {{ number_format($order->total, 0, ',', '.') }}</p>

    <button id="pay-button"
            class="w-full bg-black text-white py-4 text-sm uppercase tracking-widest font-medium hover:bg-gray-900 transition-colors">
      Pay Now
    </button>
  </div>

  <!-- Midtrans Snap.js -->
  <script src="{{ config('services.midtrans.snap_url') }}"
          data-client-key="{{ config('services.midtrans.client_key') }}"></script>
  <script>
    document.getElementById('pay-button').addEventListener('click', function() {
      snap.pay('{{ $order->midtrans_snap_token }}', {
        onSuccess: function(result) {
          window.location.href = '/payment/notification';
        },
        onPending: function(result) {
          alert('Waiting for payment...');
        },
        onError: function(result) {
          alert('Payment failed!');
        },
        onClose: function() {
          alert('Payment popup closed');
        }
      });
    });
  </script>
</x-app-layout>
```

---

## Payment Notification Webhook

```php
// routes/web.php
Route::post('/payment/notification', [PaymentController::class, 'notification'])
     ->name('payment.notification');

// app/Http/Controllers/PaymentController.php
public function notification(Request $request)
{
    // ⚠️ KEAMANAN KRITIS: Verifikasi signature sebelum proses apapun
    // Tanpa ini, siapapun bisa kirim request palsu dan manipulasi status order
    $orderId     = $request->order_id;
    $statusCode  = $request->status_code;
    $grossAmount = $request->gross_amount;
    $serverKey   = config('services.midtrans.server_key');

    $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

    if ($request->signature_key !== $expectedSignature) {
        \Log::warning('Midtrans webhook signature mismatch', ['order_id' => $orderId]);
        return response()->json(['message' => 'Invalid signature'], 403);
    }

    // Signature valid — proses notifikasi
    $order = Order::where('order_number', $orderId)->firstOrFail();
    $transactionStatus = $request->transaction_status;
    $fraudStatus = $request->fraud_status;

    if (in_array($transactionStatus, ['capture', 'settlement'])) {
        if ($fraudStatus !== 'deny') {
            $order->update(['payment_status' => 'paid', 'status' => 'processing']);
        }
    } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
        $order->update(['payment_status' => 'unpaid', 'status' => 'cancelled']);
    }

    return response()->json(['status' => 'ok']);
}

// ⚠️ PENTING: Kecualikan route ini dari CSRF verification
// app/Http/Middleware/VerifyCsrfToken.php
// protected $except = ['payment/notification'];
```

---

## Testing Kartu Kredit Sandbox

```
Nomor: 4811 1111 1111 1114
Expired: 01/25
CVV: 123
OTP: 112233
```

---

## Artisan Commands

```bash
php artisan make:controller PaymentController
mkdir -p app/Services
# Buat MidtransService.php manual
```
