# 04 — Fitur Homepage

## Tampilan Target (Referensi: canalize.asia)
- Full-width hero banner dengan slideshow
- Navigasi sticky minimalis hitam
- Section "New Arrivals" — grid produk 4 kolom
- Section "Collections" — grid kategori dengan hover overlay
- Section brand/story minimal
- Footer clean

---

## Route

```php
// routes/web.php
Route::get('/', [HomeController::class, 'index'])->name('home');
```

---

## Controller

```php
// app/Http/Controllers/HomeController.php
class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', true)
                         ->orderBy('sort_order')
                         ->get();

        $newArrivals = Product::with(['primaryImage', 'category'])
                              ->where('is_active', true)
                              ->latest()
                              ->take(8)
                              ->get();

        $featuredProducts = Product::with(['primaryImage'])
                                   ->where('is_featured', true)
                                   ->where('is_active', true)
                                   ->take(4)
                                   ->get();

        $categories = Category::where('is_active', true)
                               ->orderBy('sort_order')
                               ->take(6)
                               ->get();

        return view('home.index', compact(
            'banners', 'newArrivals', 'featuredProducts', 'categories'
        ));
    }
}
```

---

## View: `resources/views/home/index.blade.php`

### Struktur Sections:

```
1. <x-hero-banner :banners="$banners" />
2. <x-section-new-arrivals :products="$newArrivals" />
3. <x-section-collections :categories="$categories" />
4. <x-section-featured :products="$featuredProducts" />
5. <x-section-brand-story />
```

---

## Komponen Hero Banner (Alpine.js Slideshow)

```html
<!-- resources/views/components/hero-banner.blade.php -->
<div
  x-data="{
    current: 0,
    banners: {{ $banners->count() }},
    autoplay() {
      setInterval(() => {
        this.current = (this.current + 1) % this.banners
      }, 5000)
    }
  }"
  x-init="autoplay()"
  class="relative w-full h-[70vh] md:h-[90vh] overflow-hidden bg-black"
>
  @foreach($banners as $i => $banner)
  <div
    x-show="current === {{ $i }}"
    x-transition:enter="transition-opacity duration-700"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    class="absolute inset-0"
  >
    <img
      src="{{ Storage::url($banner->image) }}"
      alt="{{ $banner->title }}"
      class="w-full h-full object-cover"
    >
    <div class="absolute inset-0 bg-black/30 flex items-end p-8 md:p-16">
      <div class="text-white">
        @if($banner->title)
          <h1 class="text-5xl md:text-8xl font-bold uppercase tracking-tight leading-none">
            {{ $banner->title }}
          </h1>
        @endif
        @if($banner->subtitle)
          <p class="mt-4 text-lg md:text-xl">{{ $banner->subtitle }}</p>
        @endif
        @if($banner->link)
          <a href="{{ $banner->link }}"
             class="mt-6 inline-block border border-white text-white px-8 py-3 text-sm uppercase tracking-widest hover:bg-white hover:text-black transition-colors duration-300">
            Shop Now
          </a>
        @endif
      </div>
    </div>
  </div>
  @endforeach

  <!-- Dots indicator -->
  <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
    @foreach($banners as $i => $banner)
    <button
      @click="current = {{ $i }}"
      :class="current === {{ $i }} ? 'bg-white w-6' : 'bg-white/50 w-2'"
      class="h-2 rounded-full transition-all duration-300"
    ></button>
    @endforeach
  </div>
</div>
```

---

## Komponen Product Card

```html
<!-- resources/views/components/product-card.blade.php -->
@props(['product'])

<div class="group relative">
  <a href="{{ route('products.show', $product->slug) }}">
    <!-- Gambar produk -->
    <div class="relative aspect-[3/4] overflow-hidden bg-gray-100">
      <img
        src="{{ $product->primaryImage ? Storage::url($product->primaryImage->image_path) : asset('images/placeholder.jpg') }}"
        alt="{{ $product->name }}"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
        loading="lazy"
      >
      <!-- Badge Sale -->
      @if($product->sale_price)
      <span class="absolute top-3 left-3 bg-black text-white text-xs px-2 py-1 uppercase tracking-wider">
        Sale
      </span>
      @endif

      <!-- Quick Add (hover) -->
      <div class="absolute bottom-0 left-0 right-0 bg-black text-white text-center py-3 text-sm uppercase tracking-widest
                  translate-y-full group-hover:translate-y-0 transition-transform duration-300">
        Quick Add
      </div>
    </div>

    <!-- Info produk -->
    <div class="mt-3 space-y-1">
      <p class="text-xs text-gray-500 uppercase tracking-wider">{{ $product->category->name }}</p>
      <h3 class="text-sm font-medium text-black truncate">{{ $product->name }}</h3>
      <div class="flex items-center gap-2">
        @if($product->sale_price)
          <span class="text-sm font-semibold">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</span>
          <span class="text-sm text-gray-400 line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
        @else
          <span class="text-sm font-semibold">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
        @endif
      </div>
    </div>
  </a>
</div>
```

---

## Section New Arrivals

```html
<section class="px-4 md:px-8 py-16">
  <div class="max-w-screen-xl mx-auto">
    <div class="flex justify-between items-end mb-8">
      <h2 class="text-3xl md:text-4xl font-bold uppercase tracking-tight">New Arrivals</h2>
      <a href="{{ route('products.index') }}" class="text-sm underline underline-offset-4 hover:no-underline">
        View All
      </a>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
      @foreach($newArrivals as $product)
        <x-product-card :product="$product" />
      @endforeach
    </div>
  </div>
</section>
```

---

## Artisan Commands

```bash
php artisan make:controller HomeController
php artisan make:model Banner -m
```
