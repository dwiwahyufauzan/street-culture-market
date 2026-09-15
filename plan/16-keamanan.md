# 16 — Keamanan Sistem (Security)

## Prinsip Keamanan yang Diterapkan

Laravel sudah menyediakan banyak proteksi bawaan, namun tetap ada hal-hal yang harus diimplementasikan secara eksplisit. Dokumen ini merangkum **semua lapisan keamanan** yang wajib ada di sistem ini.

---

## ✅ Keamanan Bawaan Laravel (Otomatis)

| Mekanisme | Cara Kerja |
|-----------|------------|
| **CSRF Protection** | Token `@csrf` di setiap form POST/PUT/DELETE |
| **SQL Injection** | Eloquent & Query Builder pakai prepared statements |
| **XSS di Blade** | `{{ $var }}` auto-escape HTML — aman secara default |
| **Password Hashing** | Bcrypt via `Hash::make()` — Breeze sudah handle |
| **Session HttpOnly** | Cookie session tidak bisa diakses JavaScript |

---

## 🔴 Keamanan yang WAJIB Diimplementasikan Manual

### 1. Validasi Harga dari Database saat Checkout

**Masalah**: Harga disimpan di session — bisa dimanipulasi user lewat DevTools atau request palsu.

```php
// ❌ JANGAN — percaya harga dari session
$total = collect($this->cart->all())->sum(fn($i) => $i['price'] * $i['quantity']);

// ✅ HARUS — selalu ambil harga fresh dari database
// app/Services/CartService.php
public function calculateTotal(): float
{
    $total = 0;
    foreach ($this->all() as $item) {
        $product = Product::find($item['product_id']);
        if (!$product) continue;
        $price = $product->sale_price ?? $product->price;
        $total += $price * $item['quantity'];
    }
    return $total;
}
```

```php
// app/Http/Controllers/CheckoutController.php
public function store(Request $request)
{
    // Hitung total dari DB, bukan dari session
    $total = $this->cart->calculateTotal();

    // Validasi stok sebelum create order
    foreach ($this->cart->all() as $item) {
        $variant = ProductVariant::where('product_id', $item['product_id'])
                                  ->where('size', $item['size'])
                                  ->first();
        if (!$variant || $variant->stock < $item['quantity']) {
            return back()->withErrors(['stock' => "Stok {$item['name']} ({$item['size']}) tidak mencukupi."]);
        }
    }

    DB::transaction(function () use ($request, $total) {
        $order = Order::create(['total' => $total, ...]);
        // Kurangi stok
        foreach ($this->cart->all() as $item) {
            ProductVariant::where('product_id', $item['product_id'])
                           ->where('size', $item['size'])
                           ->decrement('stock', $item['quantity']);
        }
        $this->cart->clear();
    });
}
```

---

### 2. Verifikasi Signature Webhook Midtrans

**Masalah**: Siapapun bisa kirim request palsu ke endpoint `/payment/notification`.

```php
// ❌ JANGAN — langsung percaya notifikasi tanpa verifikasi
$notif = new \Midtrans\Notification();

// ✅ HARUS — verifikasi signature key terlebih dahulu
// app/Http/Controllers/PaymentController.php
public function notification(Request $request)
{
    // Verifikasi signature
    $orderId     = $request->order_id;
    $statusCode  = $request->status_code;
    $grossAmount = $request->gross_amount;
    $serverKey   = config('services.midtrans.server_key');

    $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

    if ($request->signature_key !== $expectedSignature) {
        return response()->json(['message' => 'Invalid signature'], 403);
    }

    // Baru proses notifikasi
    $order = Order::where('order_number', $orderId)->firstOrFail();

    match($request->transaction_status) {
        'capture', 'settlement' => $order->update(['payment_status' => 'paid', 'status' => 'processing']),
        'deny', 'expire', 'cancel' => $order->update(['payment_status' => 'unpaid', 'status' => 'cancelled']),
        default => null,
    };

    return response()->json(['status' => 'ok']);
}
```

```php
// routes/web.php — Kecualikan dari CSRF verification
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'payment/notification', // Midtrans tidak kirim CSRF token
];
```

---

### 3. Laravel Policies untuk Authorization

**Masalah**: Tanpa Policy, User A bisa akses order milik User B dengan tebak ID.

```bash
php artisan make:policy OrderPolicy --model=Order
php artisan make:policy ProductPolicy --model=Product
```

```php
// app/Policies/OrderPolicy.php
class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        // Customer hanya bisa lihat order miliknya sendiri
        if ($user->hasRole('customer')) {
            return $order->user_id === $user->id;
        }
        // Admin & Owner bisa lihat semua order
        return $user->hasRole('admin') || $user->hasRole('owner');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasRole('admin') || $user->hasRole('owner');
    }
}
```

```php
// app/Http/Controllers/AccountController.php
public function orderDetail(Order $order)
{
    $this->authorize('view', $order); // ← wajib ada di setiap akses order
    $order->load('items.product');
    return view('account.order-detail', compact('order'));
}
```

```php
// app/Providers/AuthServiceProvider.php
protected $policies = [
    Order::class   => OrderPolicy::class,
    Product::class => ProductPolicy::class,
];
```

---

### 4. Validasi & Keamanan Upload Gambar

**Masalah**: Upload file tanpa validasi bisa digunakan untuk upload file berbahaya.

```php
// app/Http/Controllers/Admin/ProductController.php (atau di Filament Resource)
$request->validate([
    'images.*' => [
        'required',
        'image',                          // harus file gambar
        'mimes:jpeg,jpg,png,webp',        // hanya format ini
        'max:2048',                       // max 2MB per file
        'dimensions:min_width=100,min_height=100', // ukuran minimum
    ],
]);

// Simpan dengan nama acak (jangan gunakan nama asli dari user!)
foreach ($request->file('images') as $file) {
    $filename = Str::uuid() . '.' . $file->extension();
    $path = $file->storeAs('products', $filename, 'public');

    // Resize & optimasi dengan Intervention Image
    $image = Image::read(Storage::disk('public')->path($path));
    $image->scale(width: 1200)->save(); // resize ke max 1200px
}
```

---

### 5. Rate Limiting

**Masalah**: Endpoint login, cart, dan search bisa di-flood/brute-force.

```php
// app/Providers/RouteServiceProvider.php
protected function configureRateLimiting(): void
{
    // Breeze sudah handle rate limit untuk login (5x/menit)

    // Cart API — max 30 request per menit per IP
    RateLimiter::for('cart', function (Request $request) {
        return Limit::perMinute(30)->by($request->ip());
    });

    // Search — max 20 request per menit per IP
    RateLimiter::for('search', function (Request $request) {
        return Limit::perMinute(20)->by($request->ip());
    });
}

// routes/web.php
Route::middleware('throttle:cart')->group(function () {
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::patch('/cart/{rowId}', [CartController::class, 'update']);
    Route::delete('/cart/{rowId}', [CartController::class, 'remove']);
});

Route::middleware('throttle:search')->group(function () {
    Route::get('/search', [SearchController::class, 'index']);
    Route::get('/search/suggest', [SearchController::class, 'suggest']);
});
```

---

### 6. Mass Assignment Protection

**Masalah**: Model tanpa `$fillable` yang ketat bisa diisi field berbahaya oleh user.

```php
// ✅ Selalu definisikan $fillable secara eksplisit di semua model

// app/Models/Product.php
protected $fillable = [
    'category_id', 'name', 'slug', 'description',
    'price', 'sale_price', 'sku', 'weight',
    'is_active', 'is_featured',
    'meta_title', 'meta_description',
];
// Jangan pernah: protected $guarded = [];

// app/Models/Order.php
protected $fillable = [
    'user_id', 'order_number', 'status', 'payment_status',
    'customer_name', 'customer_email', 'customer_phone',
    'shipping_address', 'shipping_city', 'shipping_province',
    'shipping_postal', 'shipping_method', 'shipping_cost',
    'subtotal', 'discount_amount', 'total', 'notes',
    'midtrans_order_id', 'midtrans_snap_token',
];

// app/Models/User.php — jangan izinkan user set role sendiri!
protected $fillable = ['name', 'email', 'password', 'phone'];
// 'role' atau 'is_admin' TIDAK boleh ada di fillable
```

---

### 7. HTTPS Enforcement di Production

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    if (app()->environment('production')) {
        URL::forceScheme('https');
        \Illuminate\Http\Request::setTrustedProxies(
            ['*'], \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR
        );
    }
}
```

---

### 8. Security Headers

```php
// app/Http/Middleware/SecurityHeaders.php
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // HSTS — paksa HTTPS di browser
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
```

```php
// bootstrap/app.php — daftarkan middleware
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

---

### 9. Proteksi Admin Panel

Tambahkan IP whitelist opsional dan pastikan Filament `canAccessPanel()` sudah ketat:

```php
// app/Models/User.php
public function canAccessPanel(Panel $panel): bool
{
    // Harus: email verified + role sesuai
    return match($panel->getId()) {
        'admin' => $this->hasVerifiedEmail() && ($this->hasRole('admin') || $this->hasRole('owner')),
        'owner' => $this->hasVerifiedEmail() && $this->hasRole('owner'),
        default => false,
    };
}
```

---

### 10. Validasi Input yang Ketat

```php
// Semua controller harus validasi input sebelum proses
// Contoh: CartController
public function add(Request $request)
{
    $validated = $request->validate([
        'product_id' => ['required', 'integer', 'exists:products,id'],
        'size'       => ['required', 'string', 'max:10', 'regex:/^(XS|S|M|L|XL|XXL)$/'],
        'quantity'   => ['required', 'integer', 'min:1', 'max:10'],
    ]);

    // Verifikasi produk aktif
    $product = Product::where('id', $validated['product_id'])
                       ->where('is_active', true)
                       ->firstOrFail();

    // Verifikasi stok tersedia
    $variant = $product->variants()
                        ->where('size', $validated['size'])
                        ->where('stock', '>', 0)
                        ->firstOrFail();

    $summary = $this->cart->add($product->id, $validated['size'], $validated['quantity']);
    return response()->json($summary);
}
```

---

## Checklist Keamanan Sebelum Deploy

- [ ] `APP_DEBUG=false` di production
- [ ] `APP_KEY` tidak sama dengan development
- [ ] Webhook Midtrans verifikasi signature
- [ ] Semua model punya `$fillable` eksplisit
- [ ] Semua route yang perlu punya Policy
- [ ] Rate limiting aktif di login, cart, search
- [ ] File upload hanya terima image, max 2MB, nama acak
- [ ] HTTPS diforce di production
- [ ] Security headers middleware aktif
- [ ] Tidak ada credential di kode (semua di `.env`)
- [ ] `.env` masuk `.gitignore` ✅ (default Laravel)
- [ ] `storage/` dan `bootstrap/cache/` tidak bisa diakses web
- [ ] Run `php artisan key:generate` di production

---

## Artisan Commands

```bash
php artisan make:policy OrderPolicy --model=Order
php artisan make:policy ProductPolicy --model=Product
php artisan make:middleware SecurityHeaders
```
