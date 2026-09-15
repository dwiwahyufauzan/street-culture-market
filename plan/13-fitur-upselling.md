# 13 — Fitur Upselling

## Definisi
**Upselling** adalah strategi merekomendasikan produk dengan **nilai, kualitas, atau harga yang lebih tinggi** dari produk yang sedang dilihat/dipilih pelanggan.

> Contoh: Pelanggan melihat Kaos Basic Rp 150.000 → sistem merekomendasikan Kaos Premium Rp 250.000

---

## Perbedaan Upselling vs Cross-selling

| | Upselling | Cross-selling |
|--|-----------|---------------|
| **Tujuan** | Upgrade ke produk lebih baik | Tambahkan produk pelengkap |
| **Contoh** | Kaos basic → Kaos premium | Kaos → Celana + Topi |
| **Posisi di UI** | Di atas "You Might Also Like" | Di bawah upselling |
| **Trigger** | Harga produk upsell > harga produk aktif | Dikonfigurasi manual oleh admin |

---

## Pendekatan Implementasi

Ada **2 pendekatan** yang bisa digabungkan:

### A. Rule-based (Manual — digunakan di skripsi ini)
Admin secara manual menentukan produk mana yang menjadi upsell dari produk lain.
- Sederhana, akurat, mudah dikontrol
- Cocok untuk brand kecil/menengah
- Tidak butuh algoritma ML

### B. Automatic (Price-based fallback)
Jika admin belum mengatur upsell, sistem otomatis merekomendasikan produk dengan:
- Kategori sama
- Harga lebih tinggi (10% - 100% di atas harga produk saat ini)
- Stok tersedia

---

## Database: Tabel `product_upsells`

```sql
CREATE TABLE product_upsells (
    id              BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id      BIGINT NOT NULL,   -- produk yang dilihat (trigger)
    upsell_id       BIGINT NOT NULL,   -- produk yang direkomendasikan (lebih mahal)
    sort_order      INT DEFAULT 0,     -- urutan tampil
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,

    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (upsell_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY uq_upsell (product_id, upsell_id)
);
```

### Migration

```bash
php artisan make:migration create_product_upsells_table
```

```php
// database/migrations/xxxx_create_product_upsells_table.php
Schema::create('product_upsells', function (Blueprint $table) {
    $table->id();
    $table->foreignId('product_id')->constrained()->cascadeOnDelete();
    $table->foreignId('upsell_id')->constrained('products')->cascadeOnDelete();
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();

    $table->unique(['product_id', 'upsell_id']);
});
```

---

## Model: `ProductUpsell`

```bash
php artisan make:model ProductUpsell -m
```

```php
// app/Models/ProductUpsell.php
class ProductUpsell extends Model
{
    protected $fillable = ['product_id', 'upsell_id', 'sort_order'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function upsellProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'upsell_id');
    }
}
```

### Relasi di `Product.php`

```php
// app/Models/Product.php

// Produk yang direkomendasikan sebagai upsell dari produk ini
public function upsells(): BelongsToMany
{
    return $this->belongsToMany(
        Product::class,
        'product_upsells',
        'product_id',
        'upsell_id'
    )->withPivot('sort_order')->orderByPivot('sort_order');
}

// Produk ini dijadikan upsell dari produk lain (inverse)
public function upsellOf(): BelongsToMany
{
    return $this->belongsToMany(
        Product::class,
        'product_upsells',
        'upsell_id',
        'product_id'
    );
}
```

---

## UpsellService

```php
// app/Services/UpsellService.php
<?php
namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class UpsellService
{
    /**
     * Mendapatkan produk upsell untuk suatu produk.
     * Jika tidak ada konfigurasi manual, gunakan fallback otomatis.
     */
    public function getUpsells(Product $product, int $limit = 3): Collection
    {
        // 1. Cek apakah ada upsell yang dikonfigurasi manual oleh admin
        $manualUpsells = $product->upsells()
                                 ->with('primaryImage')
                                 ->where('is_active', true)
                                 ->take($limit)
                                 ->get();

        if ($manualUpsells->count() > 0) {
            return $manualUpsells;
        }

        // 2. Fallback: cari produk di kategori sama dengan harga lebih tinggi
        return $this->getAutomaticUpsells($product, $limit);
    }

    /**
     * Fallback otomatis: produk sekategori dengan harga lebih tinggi.
     */
    private function getAutomaticUpsells(Product $product, int $limit): Collection
    {
        $currentPrice = $product->sale_price ?? $product->price;
        $minPrice = $currentPrice * 1.10; // minimal 10% lebih mahal
        $maxPrice = $currentPrice * 2.00; // maksimal 2x harga

        return Product::with('primaryImage')
                      ->where('category_id', $product->category_id)
                      ->where('id', '!=', $product->id)
                      ->where('is_active', true)
                      ->whereBetween('price', [$minPrice, $maxPrice])
                      ->whereHas('variants', fn($q) => $q->where('stock', '>', 0))
                      ->orderBy('price', 'asc')
                      ->take($limit)
                      ->get();
    }

    /**
     * Cek apakah produk B merupakan upsell yang valid dari produk A
     * (harganya lebih tinggi)
     */
    public function isValidUpsell(Product $base, Product $upsell): bool
    {
        $basePrice   = $base->sale_price ?? $base->price;
        $upsellPrice = $upsell->sale_price ?? $upsell->price;
        return $upsellPrice > $basePrice;
    }
}
```

---

## ProductController (Update)

```php
public function show(string $slug)
{
    $product = Product::with(['category', 'images', 'variants'])
                      ->where('slug', $slug)
                      ->where('is_active', true)
                      ->firstOrFail();

    // Upsells
    $upsells = app(UpsellService::class)->getUpsells($product, 3);

    // Cross-sells
    $crossSells = app(CrossSellService::class)->getCrossSells($product, 4);

    // Recently viewed
    $recentlyViewed = $this->getRecentlyViewed($product->id);

    return view('products.show', compact(
        'product', 'upsells', 'crossSells', 'recentlyViewed'
    ));
}
```

---

## View: Upsell Section di Product Detail

```html
<!-- Di resources/views/products/show.blade.php -->

@if($upsells->count() > 0)
<section class="mt-16 border-t border-gray-100 pt-12">
  <div class="max-w-screen-xl mx-auto px-4">

    <!-- Header upsell dengan konteks -->
    <div class="mb-8">
      <span class="text-xs uppercase tracking-widest text-gray-400 block mb-1">Upgrade Your Choice</span>
      <h2 class="text-2xl font-bold uppercase tracking-tight">You Might Prefer</h2>
      <p class="text-sm text-gray-500 mt-1">
        Produk berikut menawarkan kualitas atau nilai lebih tinggi dari
        <strong>{{ $product->name }}</strong>
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      @foreach($upsells as $upsell)
      <div class="group relative border border-gray-100 hover:border-black transition-colors duration-300">

        <!-- Badge "Better Value" -->
        <div class="absolute top-3 left-3 z-10">
          <span class="bg-black text-white text-xs px-3 py-1 uppercase tracking-wider">
            ↑ Upgrade
          </span>
        </div>

        <a href="{{ route('products.show', $upsell->slug) }}" class="block">
          <!-- Gambar -->
          <div class="aspect-[3/4] overflow-hidden bg-gray-50">
            <img
              src="{{ $upsell->primaryImage ? Storage::url($upsell->primaryImage->image_path) : asset('images/placeholder.jpg') }}"
              alt="{{ $upsell->name }}"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              loading="lazy"
            >
          </div>

          <!-- Info -->
          <div class="p-4">
            <h3 class="text-sm font-semibold uppercase tracking-wide">{{ $upsell->name }}</h3>

            <!-- Perbandingan harga -->
            <div class="mt-2 flex items-center justify-between">
              <div>
                @php $upsellPrice = $upsell->sale_price ?? $upsell->price; @endphp
                @php $currentPrice = $product->sale_price ?? $product->price; @endphp
                @php $diff = $upsellPrice - $currentPrice; @endphp
                <span class="text-base font-bold">Rp {{ number_format($upsellPrice, 0, ',', '.') }}</span>
              </div>
              @if($diff > 0)
              <span class="text-xs text-gray-500">
                +Rp {{ number_format($diff, 0, ',', '.') }}
              </span>
              @endif
            </div>

            <!-- Alasan upsell (opsional, bisa diisi admin) -->
            @if($upsell->pivot && $upsell->pivot->reason)
            <p class="mt-2 text-xs text-gray-500 italic">{{ $upsell->pivot->reason }}</p>
            @endif
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

## Admin: Konfigurasi Upsell di Filament

Di `ProductResource.php`, tambahkan tab **Upselling & Cross-selling**:

```php
// Di form ProductResource
Tabs\Tab::make('Upselling & Cross-selling')
    ->schema([
        Section::make('Upsell Products')
            ->description('Pilih produk yang akan direkomendasikan sebagai upgrade dari produk ini. Pastikan harga upsell > harga produk ini.')
            ->schema([
                Select::make('upsells')
                    ->label('Produk Upsell')
                    ->multiple()
                    ->relationship(
                        name: 'upsells',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('is_active', true)
                    )
                    ->searchable()
                    ->preload()
                    ->helperText('Pilih 1–3 produk dengan harga lebih tinggi dari produk ini'),
            ]),

        Section::make('Cross-sell Products')
            ->description('Pilih produk pelengkap yang direkomendasikan bersama produk ini.')
            ->schema([
                Select::make('crossSells')
                    ->label('Produk Cross-sell')
                    ->multiple()
                    ->relationship(
                        name: 'crossSells',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('is_active', true)
                    )
                    ->searchable()
                    ->preload()
                    ->helperText('Pilih 2–4 produk pelengkap (beda kategori dianjurkan)'),
            ]),
    ]),
```

---

## Artisan Commands

```bash
php artisan make:migration create_product_upsells_table
php artisan make:model ProductUpsell
mkdir -p app/Services
# Buat UpsellService.php
php artisan migrate
```
