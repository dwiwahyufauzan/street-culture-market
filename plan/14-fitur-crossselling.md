# 14 — Fitur Cross-selling

## Definisi
**Cross-selling** adalah strategi merekomendasikan **produk pelengkap/terkait** yang dapat melengkapi produk yang sedang dilihat pelanggan.

> Contoh: Pelanggan melihat Kaos → sistem merekomendasikan Celana, Topi, atau Aksesoris

---

## Perbedaan dengan Upselling

| Aspek | Cross-selling | Upselling |
|-------|--------------|-----------|
| Tujuan | Tambah produk | Upgrade produk |
| Kategori | Biasanya **beda** kategori | Biasanya **sama** kategori |
| Harga | Bisa lebih murah/sama/lebih mahal | Harus **lebih mahal** |
| Posisi UI | Di bawah upsell section | Di atas cross-sell |
| Label | "Complete the Look" / "Pair With" | "Upgrade Your Choice" |

---

## Database: Tabel `product_cross_sells`

```sql
CREATE TABLE product_cross_sells (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id      BIGINT NOT NULL,      -- produk yang dilihat (trigger)
    cross_sell_id   BIGINT NOT NULL,      -- produk yang direkomendasikan
    sort_order      INT DEFAULT 0,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (cross_sell_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uq_cross_sell (product_id, cross_sell_id)
);
```

### Migration

```bash
php artisan make:migration create_product_cross_sells_table
```

```php
Schema::create('product_cross_sells', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('cross_sell_id')->constrained('products')->cascadeOnDelete();
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['product_id', 'cross_sell_id']);
});
```

---

## Relasi di `Product.php`

```php
// app/Models/Product.php

// Produk pelengkap dari produk ini
public function crossSells(): BelongsToMany
{
    return $this->belongsToMany(
        Product::class,
        'product_cross_sells',
        'product_id',
        'cross_sell_id'
    )->withPivot('sort_order')->orderByPivot('sort_order');
}

// Produk ini adalah cross-sell dari produk lain
public function crossSellOf(): BelongsToMany
{
    return $this->belongsToMany(
        Product::class,
        'product_cross_sells',
        'cross_sell_id',
        'product_id'
    );
}
```

---

## CrossSellService

```php
// app/Services/CrossSellService.php
<?php
namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class CrossSellService
{
    /**
     * Mendapatkan produk cross-sell.
     * Prioritas: konfigurasi manual admin → fallback otomatis.
     */
    public function getCrossSells(Product $product, int $limit = 4): Collection
    {
        // 1. Cek cross-sell manual dari admin
        $manual = $product->crossSells()
                          ->with('primaryImage')
                          ->where('is_active', true)
                          ->take($limit)
                          ->get();

        if ($manual->count() > 0) {
            return $manual;
        }

        // 2. Fallback: produk dari kategori berbeda, harga dalam range serupa
        return $this->getAutomaticCrossSells($product, $limit);
    }

    /**
     * Fallback otomatis: produk dari kategori lain yang populer.
     */
    private function getAutomaticCrossSells(Product $product, int $limit): Collection
    {
        return Product::with('primaryImage')
                      ->where('category_id', '!=', $product->category_id)
                      ->where('id', '!=', $product->id)
                      ->where('is_active', true)
                      ->whereHas('variants', fn($q) => $q->where('stock', '>', 0))
                      ->inRandomOrder() // bisa diganti dengan most-ordered
                      ->take($limit)
                      ->get();
    }

    /**
     * Cross-sell kontekstual di cart:
     * Ambil cross-sell dari semua produk yang ada di cart.
     */
    public function getCrossSellsForCart(array $cartProductIds, int $limit = 3): Collection
    {
        return Product::with('primaryImage')
                      ->whereHas('crossSellOf', fn($q) => $q->whereIn('product_id', $cartProductIds))
                      ->whereNotIn('id', $cartProductIds)
                      ->where('is_active', true)
                      ->take($limit)
                      ->get();
    }
}
```

---

## View: Cross-sell Section di Product Detail

```html
<!-- Di resources/views/products/show.blade.php -->
<!-- Letakkan di BAWAH section upselling -->

@if($crossSells->count() > 0)
<section class="mt-12 pb-16">
  <div class="max-w-screen-xl mx-auto px-4">

    <div class="mb-8">
      <span class="text-xs uppercase tracking-widest text-gray-400 block mb-1">Complete the Look</span>
      <h2 class="text-2xl font-bold uppercase tracking-tight">Pair It With</h2>
      <p class="text-sm text-gray-500 mt-1">
        Produk yang cocok dipadukan dengan <strong>{{ $product->name }}</strong>
      </p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      @foreach($crossSells as $cs)
      <div class="group">
        <a href="{{ route('products.show', $cs->slug) }}" class="block">
          <div class="aspect-[3/4] overflow-hidden bg-gray-50 relative">
            <img
              src="{{ $cs->primaryImage ? Storage::url($cs->primaryImage->image_path) : asset('images/placeholder.jpg') }}"
              alt="{{ $cs->name }}"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              loading="lazy"
            >
            <!-- Quick add button on hover -->
            <div class="absolute inset-x-0 bottom-0 bg-black text-white text-center py-2 text-xs uppercase tracking-widest
                        translate-y-full group-hover:translate-y-0 transition-transform duration-300">
              Add to Cart
            </div>
          </div>
          <div class="mt-3">
            <p class="text-xs text-gray-400 uppercase tracking-wider">{{ $cs->category->name }}</p>
            <h3 class="text-sm font-medium mt-0.5 truncate">{{ $cs->name }}</h3>
            <p class="text-sm font-semibold mt-1">
              Rp {{ number_format($cs->sale_price ?? $cs->price, 0, ',', '.') }}
            </p>
          </div>
        </a>
      </div>
      @endforeach
    </div>

  </div>
</section>
@endif
```

---

## Cross-sell di Cart Page

Tampilkan rekomendasi di halaman cart untuk mendorong tambahan pembelian:

```html
<!-- resources/views/cart/index.blade.php -->

@php
$cartProductIds = collect($cart['items'])->pluck('product_id')->toArray();
$cartCrossSells = app(\App\Services\CrossSellService::class)->getCrossSellsForCart($cartProductIds, 3);
@endphp

@if($cartCrossSells->count() > 0)
<section class="mt-12 border-t border-gray-100 pt-10">
  <h3 class="text-lg font-bold uppercase tracking-tight mb-6">
    You Might Also Need
  </h3>
  <div class="grid grid-cols-3 gap-4">
    @foreach($cartCrossSells as $p)
    <div class="group flex gap-3 border border-gray-100 p-3 hover:border-black transition-colors">
      <div class="w-16 h-20 bg-gray-50 flex-shrink-0 overflow-hidden">
        <img src="{{ $p->primaryImage ? Storage::url($p->primaryImage->image_path) : asset('images/placeholder.jpg') }}"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-xs text-gray-400">{{ $p->category->name }}</p>
        <h4 class="text-sm font-medium truncate">{{ $p->name }}</h4>
        <p class="text-sm font-bold mt-1">Rp {{ number_format($p->sale_price ?? $p->price, 0, ',', '.') }}</p>
        <a href="{{ route('products.show', $p->slug) }}"
           class="text-xs underline underline-offset-2 hover:no-underline mt-1 inline-block">
          View →
        </a>
      </div>
    </div>
    @endforeach
  </div>
</section>
@endif
```

---

## Urutan Section di Product Detail Page

```
1. Foto produk + info + Add to Cart
2. ─────────────────────────────────
3. 🔼 UPSELLING: "Upgrade Your Choice"
   (3 produk dengan harga lebih tinggi)
4. ─────────────────────────────────
5. 🔄 CROSS-SELLING: "Complete the Look"
   (4 produk pelengkap)
6. ─────────────────────────────────
7. Recently Viewed
```

---

## Artisan Commands

```bash
php artisan make:migration create_product_cross_sells_table
# Buat CrossSellService.php di app/Services/
php artisan migrate
```
