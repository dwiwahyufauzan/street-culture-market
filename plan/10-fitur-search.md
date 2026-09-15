# 10 — Fitur Search & Filter

## Overview
Menggunakan **Laravel Scout** (sudah terinstall) dengan driver **database** (SQLite-compatible) untuk development, dan bisa upgrade ke Algolia/Meilisearch untuk production.

---

## Setup Scout

```bash
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
```

Di `.env`, gunakan database driver:
```env
SCOUT_DRIVER=database
```

---

## Setup Model Product untuk Scout

```php
// app/Models/Product.php
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable;

    public function toSearchableArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'category'    => $this->category?->name,
        ];
    }

    // Hanya index produk yang aktif
    public function shouldBeSearchable(): bool
    {
        return $this->is_active;
    }
}
```

---

## Routes

```php
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/search/suggest', [SearchController::class, 'suggest'])->name('search.suggest');
```

---

## SearchController

```php
class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return view('search.index', ['products' => collect(), 'query' => '']);
        }

        $products = Product::search($query)
                            ->where('is_active', 1)
                            ->paginate(16);

        return view('search.index', compact('products', 'query'));
    }

    public function suggest(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = Product::search($query)
                               ->where('is_active', 1)
                               ->take(5)
                               ->get()
                               ->map(fn($p) => [
                                   'id'    => $p->id,
                                   'name'  => $p->name,
                                   'slug'  => $p->slug,
                                   'price' => $p->sale_price ?? $p->price,
                                   'image' => $p->primaryImage?->image_path,
                               ]);

        return response()->json($suggestions);
    }
}
```

---

## Live Search (Navbar)

```html
<!-- Di dalam komponen navbar -->
<div x-data="liveSearch()" class="relative">
  <input
    type="text"
    x-model.debounce.300ms="query"
    @focus="open = true"
    @click.outside="open = false"
    placeholder="Search products..."
    class="w-full md:w-80 border-b border-gray-300 focus:border-black outline-none py-2 px-0 text-sm bg-transparent transition-colors"
  >

  <!-- Suggestions dropdown -->
  <div
    x-show="open && suggestions.length > 0"
    x-transition
    class="absolute top-full left-0 right-0 bg-white border border-gray-200 shadow-lg z-50 mt-1"
  >
    <template x-for="item in suggestions" :key="item.id">
      <a :href="'/products/' + item.slug"
         class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
        <div class="w-10 h-12 bg-gray-100 flex-shrink-0">
          <img :src="item.image ? '/storage/' + item.image : '/images/placeholder.jpg'"
               class="w-full h-full object-cover">
        </div>
        <div>
          <p class="text-sm font-medium" x-text="item.name"></p>
          <p class="text-xs text-gray-500" x-text="formatPrice(item.price)"></p>
        </div>
      </a>
    </template>

    <!-- View all results -->
    <a :href="'/search?q=' + query"
       class="block px-4 py-3 text-center text-xs text-gray-500 border-t hover:bg-gray-50">
       View all results for "<span x-text="query"></span>"
    </a>
  </div>
</div>

<script>
function liveSearch() {
  return {
    query: '',
    suggestions: [],
    open: false,

    formatPrice(price) {
      return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
    },

    init() {
      this.$watch('query', async (val) => {
        if (val.length < 2) { this.suggestions = []; return; }
        const res = await fetch('/search/suggest?q=' + encodeURIComponent(val));
        this.suggestions = await res.json();
      });
    }
  }
}
</script>
```

---

## Filter Produk (Product Listing)

```html
<!-- Sidebar filter di products/index.blade.php -->
<aside class="space-y-8">

  <!-- Filter by Category -->
  <div>
    <h3 class="text-xs font-semibold uppercase tracking-widest mb-3">Category</h3>
    @foreach($categories as $cat)
    <label class="flex items-center gap-2 py-1 text-sm cursor-pointer">
      <input type="radio" name="category" value="{{ $cat->slug }}"
             {{ request('category') === $cat->slug ? 'checked' : '' }}
             class="accent-black">
      {{ $cat->name }}
    </label>
    @endforeach
  </div>

  <!-- Filter by Size -->
  <div>
    <h3 class="text-xs font-semibold uppercase tracking-widest mb-3">Size</h3>
    <div class="grid grid-cols-3 gap-2">
      @foreach(['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $sz)
      <label>
        <input type="radio" name="size" value="{{ $sz }}" class="peer hidden"
               {{ request('size') === $sz ? 'checked' : '' }}>
        <span class="block border text-center py-2 text-sm cursor-pointer
                     peer-checked:bg-black peer-checked:text-white hover:border-black transition-colors">
          {{ $sz }}
        </span>
      </label>
      @endforeach
    </div>
  </div>

  <!-- Sort -->
  <div>
    <h3 class="text-xs font-semibold uppercase tracking-widest mb-3">Sort By</h3>
    <select name="sort" class="w-full border border-gray-300 py-2 px-3 text-sm focus:border-black outline-none">
      <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Newest</option>
      <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
      <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
    </select>
  </div>

  <button type="submit"
          class="w-full bg-black text-white py-3 text-xs uppercase tracking-widest">
    Apply Filters
  </button>

  @if(request()->hasAny(['category', 'size', 'sort']))
  <a href="{{ route('products.index') }}"
     class="block text-center text-xs text-gray-500 underline">
    Clear Filters
  </a>
  @endif

</aside>
```

---

## Import Data ke Scout Index

```bash
# Index semua produk yang ada
php artisan scout:import "App\Models\Product"

# Flush index (hapus semua)
php artisan scout:flush "App\Models\Product"
```

---

## Artisan Commands

```bash
php artisan make:controller SearchController
php artisan scout:import "App\\Models\\Product"
```
