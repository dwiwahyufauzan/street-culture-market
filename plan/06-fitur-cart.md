# 06 — Fitur Cart & Checkout

## Arsitektur Cart
Cart disimpan di **PHP Session** — tidak membutuhkan package tambahan. Lebih ringan dan tidak tergantung versi Laravel.

---

## Routes

```php
// routes/web.php

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/{rowId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{rowId}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');

// Checkout
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
```

---

## CartService (app/Services/CartService.php)

```php
<?php
namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Str;

class CartService
{
    private string $key = 'shopping_cart';

    public function all(): array
    {
        return session($this->key, []);
    }

    public function add(int $productId, string $size, int $quantity = 1): array
    {
        $cart = $this->all();
        $product = Product::with('primaryImage')->findOrFail($productId);
        $variant = $product->variants()->where('size', $size)->first();

        $rowId = Str::uuid()->toString();

        // Cek apakah sudah ada item dengan produk + size yang sama
        foreach ($cart as $key => $item) {
            if ($item['product_id'] === $productId && $item['size'] === $size) {
                $cart[$key]['quantity'] += $quantity;
                session([$this->key => $cart]);
                return $this->summary();
            }
        }

        $cart[$rowId] = [
            'row_id'     => $rowId,
            'product_id' => $productId,
            'name'       => $product->name,
            'size'       => $size,
            'quantity'   => $quantity,
            'price'      => $product->sale_price ?? $product->price,
            'image'      => $product->primaryImage?->image_path,
        ];

        session([$this->key => $cart]);
        return $this->summary();
    }

    public function update(string $rowId, int $quantity): array
    {
        $cart = $this->all();
        if (isset($cart[$rowId])) {
            if ($quantity <= 0) {
                unset($cart[$rowId]);
            } else {
                $cart[$rowId]['quantity'] = $quantity;
            }
            session([$this->key => $cart]);
        }
        return $this->summary();
    }

    public function remove(string $rowId): array
    {
        $cart = $this->all();
        unset($cart[$rowId]);
        session([$this->key => $cart]);
        return $this->summary();
    }

    public function total(): float
    {
        // ⚠️ KEAMANAN: Selalu hitung dari database, BUKAN dari harga di session
        // Mencegah manipulasi harga oleh user via DevTools/request palsu
        $total = 0;
        foreach ($this->all() as $item) {
            $product = \App\Models\Product::find($item['product_id']);
            if (!$product) continue;
            $price = $product->sale_price ?? $product->price;
            $total += $price * $item['quantity'];
        }
        return $total;
    }

    public function count(): int
    {
        return collect($this->all())->sum('quantity');
    }

    public function summary(): array
    {
        return [
            'items' => array_values($this->all()),
            'count' => $this->count(),
            'total' => $this->total(),
        ];
    }

    public function clear(): void
    {
        session()->forget($this->key);
    }
}
```

---

## CartController

```php
class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        return view('cart.index', ['cart' => $this->cart->summary()]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'size'       => 'required|string',
            'quantity'   => 'integer|min:1|max:10',
        ]);

        $summary = $this->cart->add(
            $request->product_id,
            $request->size,
            $request->get('quantity', 1)
        );

        return response()->json($summary);
    }

    public function update(Request $request, string $rowId)
    {
        $summary = $this->cart->update($rowId, $request->quantity);
        return response()->json($summary);
    }

    public function remove(string $rowId)
    {
        $summary = $this->cart->remove($rowId);
        return response()->json($summary);
    }

    public function count()
    {
        return response()->json(['count' => $this->cart->count()]);
    }
}
```

---

## Cart Drawer (Alpine.js)

```html
<!-- resources/views/components/cart-drawer.blade.php -->
<div
  x-data="cartDrawer()"
  x-on:cart-updated.window="refresh($event.detail)"
  x-on:open-cart.window="open = true"
>
  <!-- Overlay -->
  <div
    x-show="open"
    x-transition:enter="transition-opacity duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="open = false"
    class="fixed inset-0 bg-black/50 z-40"
  ></div>

  <!-- Drawer -->
  <div
    x-show="open"
    x-transition:enter="transition-transform duration-300"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition-transform duration-300"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full"
    class="fixed right-0 top-0 bottom-0 w-full max-w-md bg-white z-50 flex flex-col shadow-2xl"
  >
    <!-- Header -->
    <div class="flex justify-between items-center px-6 py-5 border-b">
      <h2 class="text-base font-semibold uppercase tracking-widest">Your Cart (<span x-text="count"></span>)</h2>
      <button @click="open = false" class="text-2xl leading-none">&times;</button>
    </div>

    <!-- Items -->
    <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
      <template x-for="item in items" :key="item.row_id">
        <div class="flex gap-4">
          <div class="w-20 h-24 bg-gray-100 flex-shrink-0">
            <img :src="item.image ? '/storage/' + item.image : '/images/placeholder.jpg'"
                 :alt="item.name" class="w-full h-full object-cover">
          </div>
          <div class="flex-1 min-w-0">
            <h3 class="text-sm font-medium truncate" x-text="item.name"></h3>
            <p class="text-xs text-gray-500 mt-1">Size: <span x-text="item.size"></span></p>
            <p class="text-sm font-semibold mt-1" x-text="formatPrice(item.price)"></p>
            <div class="flex items-center gap-2 mt-2">
              <div class="flex border border-gray-300 text-xs">
                <button @click="updateQty(item.row_id, item.quantity - 1)"
                        class="px-2 py-1 hover:bg-gray-100">−</button>
                <span x-text="item.quantity" class="px-3 py-1 border-x border-gray-300"></span>
                <button @click="updateQty(item.row_id, item.quantity + 1)"
                        class="px-2 py-1 hover:bg-gray-100">+</button>
              </div>
              <button @click="removeItem(item.row_id)"
                      class="text-xs text-gray-400 hover:text-red-600 underline ml-2">Remove</button>
            </div>
          </div>
        </div>
      </template>

      <div x-show="items.length === 0" class="text-center py-16 text-gray-500">
        <p class="text-5xl mb-4">🛍️</p>
        <p class="text-sm uppercase tracking-wider">Your cart is empty</p>
      </div>
    </div>

    <!-- Footer -->
    <div class="border-t px-6 py-5 space-y-4">
      <div class="flex justify-between text-sm font-semibold">
        <span class="uppercase tracking-wider">Subtotal</span>
        <span x-text="formatPrice(total)"></span>
      </div>
      <a href="/checkout"
         class="block w-full bg-black text-white text-center py-4 text-sm uppercase tracking-widest font-medium hover:bg-gray-900 transition-colors"
         :class="items.length === 0 ? 'pointer-events-none opacity-50' : ''">
        Checkout
      </a>
      <a href="/cart" class="block text-center text-sm underline underline-offset-4">View Cart</a>
    </div>
  </div>
</div>

<script>
function cartDrawer() {
  return {
    open: false,
    items: @json(app(\App\Services\CartService::class)->all() |> array_values($$)),
    count: {{ app(\App\Services\CartService::class)->count() }},
    total: {{ app(\App\Services\CartService::class)->total() }},

    refresh(data) {
      this.items = data.items;
      this.count = data.count;
      this.total = data.total;
      this.open = true;
    },

    formatPrice(price) {
      return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
    },

    updateQty(rowId, qty) {
      fetch('/cart/' + rowId, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ quantity: qty })
      }).then(r => r.json()).then(data => this.refresh(data));
    },

    removeItem(rowId) {
      fetch('/cart/' + rowId, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
      }).then(r => r.json()).then(data => this.refresh(data));
    }
  }
}
</script>
```

---

## CheckoutController

```php
class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        if ($this->cart->count() === 0) {
            return redirect()->route('cart.index');
        }
        return view('checkout.index', ['cart' => $this->cart->summary()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'phone'   => 'required|string|max:20',
            'address' => 'required|string',
            'city'    => 'required|string|max:100',
            'province'=> 'required|string|max:100',
            'postal'  => 'required|string|max:10',
        ]);

        DB::transaction(function () use ($request) {
            $order = Order::create([
                'order_number'   => 'SCM-' . date('Y') . '-' . str_pad(Order::count() + 1, 5, '0', STR_PAD_LEFT),
                'user_id'        => auth()->id(),
                'status'         => 'pending',
                'payment_status' => 'unpaid',
                'customer_name'  => $request->name,
                'customer_email' => $request->email,
                'customer_phone' => $request->phone,
                'shipping_address' => $request->address,
                'shipping_city'    => $request->city,
                'shipping_province'=> $request->province,
                'shipping_postal'  => $request->postal,
                'subtotal' => $this->cart->total(),
                'total'    => $this->cart->total(),
            ]);

            foreach ($this->cart->all() as $item) {
                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $item['product_id'],
                    'product_name'=> $item['name'],
                    'size'        => $item['size'],
                    'quantity'    => $item['quantity'],
                    'price'       => $item['price'],
                    'subtotal'    => $item['price'] * $item['quantity'],
                ]);
            }

            $this->cart->clear();
            return redirect()->route('checkout.success', $order);
        });
    }
}
```

---

## Artisan Commands

```bash
php artisan make:controller CartController
php artisan make:controller CheckoutController
mkdir -p app/Services
# Buat CartService.php manual
```
