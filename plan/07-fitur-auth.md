# 07 — Fitur Auth & User Account

## Overview
Menggunakan **Laravel Breeze** (sudah terinstall) untuk scaffolding auth dasar, kemudian dikustomisasi dengan tampilan Street Culture Market.

---

## Routes (Breeze Auto-generate)

```php
// Sudah dibuat otomatis oleh Breeze
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');

// User account (tambah sendiri)
Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/orders/{order}', [AccountController::class, 'orderDetail'])->name('account.orders.show');
    Route::get('/account/wishlist', [AccountController::class, 'wishlist'])->name('account.wishlist');
    Route::post('/wishlist/toggle/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
});
```

---

## Kustomisasi Views Breeze

Semua view Breeze ada di `resources/views/auth/`. Ganti dengan desain SCM:

### Login Page
```html
<!-- resources/views/auth/login.blade.php -->
<x-guest-layout>
  <div class="min-h-screen flex items-center justify-center bg-white">
    <div class="w-full max-w-sm px-6 py-12">
      <!-- Logo -->
      <a href="/" class="block text-center mb-12">
        <span class="text-2xl font-bold uppercase tracking-[0.3em]">SCM</span>
      </a>

      <h1 class="text-2xl font-bold uppercase tracking-tight mb-8">Sign In</h1>

      <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <div>
          <label class="block text-xs font-medium uppercase tracking-wider mb-2">Email</label>
          <input type="email" name="email" required
                 class="w-full border border-gray-300 px-4 py-3 text-sm focus:border-black focus:outline-none transition-colors"
                 placeholder="your@email.com">
        </div>
        <div>
          <label class="block text-xs font-medium uppercase tracking-wider mb-2">Password</label>
          <input type="password" name="password" required
                 class="w-full border border-gray-300 px-4 py-3 text-sm focus:border-black focus:outline-none transition-colors">
        </div>
        <div class="flex justify-between items-center text-xs">
          <label class="flex items-center gap-2">
            <input type="checkbox" name="remember" class="rounded border-gray-300">
            Remember me
          </label>
          <a href="{{ route('password.request') }}" class="text-gray-500 hover:text-black">Forgot password?</a>
        </div>
        <button type="submit"
                class="w-full bg-black text-white py-4 text-sm uppercase tracking-widest font-medium hover:bg-gray-900 transition-colors">
          Sign In
        </button>
        <p class="text-center text-sm text-gray-500">
          Don't have an account?
          <a href="{{ route('register') }}" class="text-black font-medium underline">Create one</a>
        </p>
      </form>
    </div>
  </div>
</x-guest-layout>
```

---

## AccountController

```php
class AccountController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $recentOrders = $user->orders()->latest()->take(3)->get();
        return view('account.index', compact('user', 'recentOrders'));
    }

    public function orders()
    {
        $orders = auth()->user()->orders()
                        ->with('items')
                        ->latest()
                        ->paginate(10);
        return view('account.orders', compact('orders'));
    }

    public function orderDetail(Order $order)
    {
        $this->authorize('view', $order);
        $order->load('items.product');
        return view('account.order-detail', compact('order'));
    }

    public function wishlist()
    {
        $wishlist = auth()->user()->wishlists()
                          ->with(['product.primaryImage'])
                          ->get();
        return view('account.wishlist', compact('wishlist'));
    }
}
```

---

## WishlistController

```php
class WishlistController extends Controller
{
    public function toggle(Product $product)
    {
        $user = auth()->user();
        $wishlist = $user->wishlists()->where('product_id', $product->id)->first();

        if ($wishlist) {
            $wishlist->delete();
            $inWishlist = false;
        } else {
            $user->wishlists()->create(['product_id' => $product->id]);
            $inWishlist = true;
        }

        return response()->json([
            'in_wishlist' => $inWishlist,
            'message' => $inWishlist ? 'Added to wishlist' : 'Removed from wishlist',
        ]);
    }
}
```

---

## User Model Relations

```php
// app/Models/User.php
public function orders(): HasMany
{
    return $this->hasMany(Order::class);
}

public function wishlists(): HasMany
{
    return $this->hasMany(Wishlist::class);
}
```

---

## User Account Dashboard View

```html
<!-- resources/views/account/index.blade.php -->
<x-app-layout>
  <div class="max-w-screen-xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

      <!-- Sidebar -->
      <aside class="space-y-1">
        <h2 class="text-xs font-semibold uppercase tracking-widest text-gray-500 mb-4">My Account</h2>
        <a href="{{ route('account.index') }}"
           class="block px-4 py-3 text-sm hover:bg-gray-100 {{ request()->routeIs('account.index') ? 'bg-gray-100 font-medium' : '' }}">
          Overview
        </a>
        <a href="{{ route('account.orders') }}"
           class="block px-4 py-3 text-sm hover:bg-gray-100">
          Orders
        </a>
        <a href="{{ route('account.wishlist') }}"
           class="block px-4 py-3 text-sm hover:bg-gray-100">
          Wishlist
        </a>
        <a href="{{ route('profile.edit') }}"
           class="block px-4 py-3 text-sm hover:bg-gray-100">
          Profile Settings
        </a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="block w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50">
            Sign Out
          </button>
        </form>
      </aside>

      <!-- Content -->
      <div class="md:col-span-3">
        <h1 class="text-2xl font-bold uppercase tracking-tight mb-8">Welcome, {{ auth()->user()->name }}</h1>

        <!-- Recent Orders -->
        <div>
          <h3 class="text-sm font-semibold uppercase tracking-wider mb-4">Recent Orders</h3>
          @forelse($recentOrders as $order)
          <div class="border border-gray-200 p-4 mb-3 flex justify-between items-center">
            <div>
              <p class="text-sm font-medium">{{ $order->order_number }}</p>
              <p class="text-xs text-gray-500 mt-1">{{ $order->created_at->format('d M Y') }}</p>
            </div>
            <div class="text-right">
              <span class="inline-block px-3 py-1 text-xs uppercase tracking-wider
                {{ $order->status === 'delivered' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                {{ $order->status }}
              </span>
              <p class="text-sm font-semibold mt-1">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
            </div>
          </div>
          @empty
          <p class="text-sm text-gray-500">No orders yet.</p>
          @endforelse
        </div>
      </div>

    </div>
  </div>
</x-app-layout>
```

---

## Artisan Commands

```bash
php artisan make:controller AccountController
php artisan make:controller WishlistController
php artisan make:model Wishlist -m
php artisan make:migration add_fields_to_users_table  # tambah phone, address, dll
```
