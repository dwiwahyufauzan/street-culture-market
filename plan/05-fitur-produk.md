# 05 — Fitur Produk (Listing & Detail)

## Routes

```php
// routes/web.php
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/collections/{slug}', [CategoryController::class, 'show'])->name('categories.show');
```

---

## Product Listing (`/products`)

### Controller
```php
public function index(Request $request)
{
    $query = Product::with(['primaryImage', 'category'])
                    ->where('is_active', true);

    // Filter by category
    if ($request->category) {
        $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
    }

    // Filter by size
    if ($request->size) {
        $query->whereHas('variants', fn($q) => $q->where('size', $request->size)->where('stock', '>', 0));
    }

    // Sort
    match($request->sort) {
        'price_asc'  => $query->orderBy('price', 'asc'),
        'price_desc' => $query->orderBy('price', 'desc'),
        'oldest'     => $query->oldest(),
        default      => $query->latest(),
    };

    $products = $query->paginate(16);
    $categories = Category::where('is_active', true)->get();

    return view('products.index', compact('products', 'categories'));
}
```

### View Features
- Grid 2 kolom (mobile) / 4 kolom (desktop)
- Filter sidebar: kategori, ukuran
- Sort dropdown: Terbaru, Harga ↑, Harga ↓
- Infinite scroll atau pagination
- Skeleton loading (CSS)

---

## Product Detail (`/products/{slug}`)

### Controller
```php
public function show(string $slug)
{
    $product = Product::with(['category', 'images', 'variants'])
                      ->where('slug', $slug)
                      ->where('is_active', true)
                      ->firstOrFail();

    // Group variants by size
    $sizes = $product->variants->pluck('size')->unique()->values();
    $colors = $product->variants->pluck('color')->filter()->unique()->values();

    // Recently viewed (session)
    $recentlyViewed = $this->getRecentlyViewed($product->id);

    return view('products.show', compact('product', 'sizes', 'colors', 'recentlyViewed'));
}

private function getRecentlyViewed(int $currentId): Collection
{
    $viewed = session('recently_viewed', []);

    // Tambah current product
    $viewed = array_filter($viewed, fn($id) => $id !== $currentId);
    array_unshift($viewed, $currentId);
    $viewed = array_slice($viewed, 0, 8);
    session(['recently_viewed' => $viewed]);

    return Product::with('primaryImage')
                  ->whereIn('id', array_diff($viewed, [$currentId]))
                  ->take(4)
                  ->get();
}
```

### View: Product Detail
```html
<div class="max-w-screen-xl mx-auto px-4 py-8" x-data="productDetail()">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-16">

    <!-- Gallery -->
    <div class="space-y-3">
      <div class="aspect-square overflow-hidden bg-gray-100">
        <img :src="selectedImage" class="w-full h-full object-cover">
      </div>
      <div class="grid grid-cols-4 gap-2">
        @foreach($product->images as $image)
        <button @click="selectedImage = '{{ Storage::url($image->image_path) }}'"
                class="aspect-square overflow-hidden bg-gray-100 ring-1"
                :class="selectedImage === '{{ Storage::url($image->image_path) }}' ? 'ring-black' : 'ring-transparent'">
          <img src="{{ Storage::url($image->image_path) }}" class="w-full h-full object-cover">
        </button>
        @endforeach
      </div>
    </div>

    <!-- Product Info -->
    <div class="flex flex-col gap-6">
      <div>
        <p class="text-sm text-gray-500 uppercase tracking-wider mb-2">{{ $product->category->name }}</p>
        <h1 class="text-3xl font-bold uppercase tracking-tight">{{ $product->name }}</h1>
        <div class="mt-3 flex items-center gap-3">
          @if($product->sale_price)
            <span class="text-2xl font-semibold">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</span>
            <span class="text-lg text-gray-400 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
          @else
            <span class="text-2xl font-semibold">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
          @endif
        </div>
      </div>

      <!-- Size Picker -->
      <div>
        <div class="flex justify-between mb-3">
          <span class="text-sm font-medium uppercase tracking-wider">Size: <span x-text="selectedSize || 'Select'"></span></span>
          <button class="text-sm underline">Size Guide</button>
        </div>
        <div class="grid grid-cols-6 gap-2">
          @foreach($sizes as $size)
          <button
            @click="selectedSize = '{{ $size }}'"
            :class="selectedSize === '{{ $size }}' ? 'bg-black text-white border-black' : 'bg-white text-black border-gray-300'"
            class="border py-2 text-sm font-medium transition-colors duration-200 hover:border-black"
          >{{ $size }}</button>
          @endforeach
        </div>
      </div>

      <!-- Quantity -->
      <div class="flex items-center gap-4">
        <span class="text-sm font-medium uppercase tracking-wider">Qty:</span>
        <div class="flex border border-gray-300">
          <button @click="qty = Math.max(1, qty - 1)"
                  class="px-4 py-2 text-lg hover:bg-gray-100">−</button>
          <span x-text="qty" class="px-4 py-2 min-w-[3rem] text-center"></span>
          <button @click="qty++"
                  class="px-4 py-2 text-lg hover:bg-gray-100">+</button>
        </div>
      </div>

      <!-- Add to Cart -->
      <button
        @click="addToCart({{ $product->id }})"
        :disabled="!selectedSize"
        class="w-full bg-black text-white py-4 text-sm uppercase tracking-widest font-medium
               hover:bg-gray-900 disabled:bg-gray-300 disabled:cursor-not-allowed
               transition-colors duration-200"
      >
        Add to Cart
      </button>

      <!-- Wishlist -->
      <button
        @click="toggleWishlist({{ $product->id }})"
        class="w-full border border-black py-4 text-sm uppercase tracking-widest font-medium
               hover:bg-black hover:text-white transition-colors duration-200"
      >
        ♡ Add to Wishlist
      </button>

      <!-- Description (accordion) -->
      <div x-data="{ open: false }">
        <button @click="open = !open"
                class="w-full flex justify-between items-center py-4 border-t border-gray-200 text-sm font-medium uppercase tracking-wider">
          Description
          <span x-text="open ? '−' : '+'"></span>
        </button>
        <div x-show="open" x-collapse class="pb-4 text-sm text-gray-600 leading-relaxed">
          {!! nl2br(e($product->description)) !!}
        </div>
      </div>
    </div>
  </div>

  <!-- Recently Viewed -->
  @if($recentlyViewed->count() > 0)
  <section class="mt-20">
    <h2 class="text-2xl font-bold uppercase tracking-tight mb-8">Recently Viewed</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      @foreach($recentlyViewed as $p)
        <x-product-card :product="$p" />
      @endforeach
    </div>
  </section>
  @endif
</div>

<script>
function productDetail() {
  return {
    selectedImage: '{{ $product->images->where("is_primary", true)->first() ? Storage::url($product->images->where("is_primary", true)->first()->image_path) : "" }}',
    selectedSize: null,
    qty: 1,

    addToCart(productId) {
      if (!this.selectedSize) {
        alert('Please select a size');
        return;
      }
      fetch('/cart/add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
        body: JSON.stringify({ product_id: productId, size: this.selectedSize, quantity: this.qty })
      })
      .then(r => r.json())
      .then(data => {
        window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
      });
    },

    toggleWishlist(productId) {
      fetch('/wishlist/toggle/' + productId, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
      })
      .then(r => r.json())
      .then(data => console.log(data));
    }
  }
}
</script>
```

---

## Artisan Commands

```bash
php artisan make:controller ProductController --resource
php artisan make:controller CategoryController --resource
php artisan make:model Product -m
php artisan make:model Category -m
php artisan make:model ProductVariant -m
php artisan make:model ProductImage -m
```
