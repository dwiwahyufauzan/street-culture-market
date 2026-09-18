<x-app-layout>
  @php
    $primaryImg = $product->primaryImage;
    $primaryImgUrl = ($primaryImg && $primaryImg->image_path && file_exists(public_path('storage/' . $primaryImg->image_path)))
      ? asset('storage/' . $primaryImg->image_path)
      : null;
  @endphp

  <!-- Breadcrumb -->
  <div class="border-b border-scm-gray-200 bg-scm-white py-3.5">
    <div class="container-scm section-padding !py-0">
      <nav class="flex items-center gap-2 text-[11px] uppercase tracking-[0.2em] text-scm-gray-400 font-medium">
        <a href="{{ route('home') }}" class="hover:text-scm-black transition-colors">Home</a>
        <span>/</span>
        <a href="{{ route('products.index') }}" class="hover:text-scm-black transition-colors">Catalog</a>
        @if($product->category)
          <span>/</span>
          <a href="{{ route('categories.show', $product->category->slug) }}" class="hover:text-scm-black transition-colors">{{ $product->category->name }}</a>
        @endif
        <span>/</span>
        <span class="text-scm-black truncate max-w-[200px] sm:max-w-none">{{ $product->name }}</span>
      </nav>
    </div>
  </div>

  <div
    class="container-scm section-padding"
    x-data="productDetail({
      initialImage: '{{ $primaryImgUrl ?? '' }}',
      firstSize: '{{ $product->variants->first()?->size ?? '' }}',
      productId: {{ $product->id }},
      initialWishlisted: {{ $isWishlisted ? 'true' : 'false' }}
    })"
  >
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16">

      <!-- Left: Image Gallery (Aspect 3:4 Monochromatic Frame) -->
      <div class="lg:col-span-7 space-y-4">
        <!-- Main Display Image -->
        <div class="aspect-[3/4] bg-scm-gray-100 overflow-hidden border border-scm-gray-200 relative">
          <template x-if="selectedImage">
            <img
              :src="selectedImage"
              alt="{{ $product->name }}"
              class="w-full h-full object-cover transition-opacity duration-300"
            >
          </template>

          <template x-if="!selectedImage">
            <div class="w-full h-full flex flex-col items-center justify-center p-12 bg-gradient-to-b from-scm-gray-50 to-scm-gray-200">
              <div class="w-20 h-20 border-2 border-scm-black/20 flex items-center justify-center mb-4">
                <span class="text-sm font-black tracking-widest text-scm-black">SCM</span>
              </div>
              <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-500 font-mono">{{ $product->category?->name ?? 'Streetwear' }}</span>
            </div>
          </template>

          <!-- Badges -->
          <div class="absolute top-4 left-4 flex flex-col gap-2 z-10">
            @if($product->has_discount)
              <span class="bg-scm-black text-white text-xs uppercase tracking-widest font-bold px-3 py-1.5">
                Sale Drop
              </span>
            @endif
            @if($product->is_featured)
              <span class="bg-white/95 backdrop-blur-xs text-scm-black border border-scm-gray-200 text-xs uppercase tracking-widest font-bold px-3 py-1.5">
                Featured
              </span>
            @endif
          </div>
        </div>

        <!-- Image Thumbnails Strip -->
        @if($product->images->count() > 1)
          <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
            @foreach($product->images as $img)
              @php
                $thumbnailUrl = ($img->image_path && file_exists(public_path('storage/' . $img->image_path)))
                  ? asset('storage/' . $img->image_path)
                  : null;
              @endphp
              @if($thumbnailUrl)
                <button
                  type="button"
                  @click="selectedImage = '{{ $thumbnailUrl }}'"
                  class="aspect-square overflow-hidden bg-scm-gray-100 border transition-all duration-200"
                  :class="selectedImage === '{{ $thumbnailUrl }}' ? 'border-scm-black ring-1 ring-scm-black' : 'border-scm-gray-200 hover:border-scm-gray-400'"
                >
                  <img src="{{ $thumbnailUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                </button>
              @endif
            @endforeach
          </div>
        @endif
      </div>

      <!-- Right: Product Info & Buy Form -->
      <div class="lg:col-span-5 flex flex-col justify-between space-y-8">
        <div class="space-y-6">
          <!-- Meta tags -->
          <div class="flex items-center justify-between text-xs uppercase tracking-widest text-scm-gray-400 font-medium">
            <span>{{ $product->category?->name ?? 'Collection' }}</span>
            @if($product->sku)
              <span class="font-mono text-scm-gray-500">SKU: {{ $product->sku }}</span>
            @endif
          </div>

          <!-- Product Title -->
          <h1 class="text-3xl sm:text-4xl font-black uppercase tracking-tight text-scm-black leading-tight">
            {{ $product->name }}
          </h1>

          <!-- Price Display -->
          <div class="flex items-baseline gap-3 pt-1">
            @if($product->has_discount)
              <span class="text-2xl sm:text-3xl font-bold text-red-600">
                Rp {{ number_format($product->sale_price, 0, ',', '.') }}
              </span>
              <span class="text-base text-scm-gray-400 line-through">
                Rp {{ number_format($product->price, 0, ',', '.') }}
              </span>
              <span class="text-xs font-bold uppercase tracking-wider text-red-600 bg-red-50 border border-red-200 px-2 py-0.5 ml-1">
                Save Rp {{ number_format($product->price - $product->sale_price, 0, ',', '.') }}
              </span>
            @else
              <span class="text-2xl sm:text-3xl font-bold text-scm-black">
                Rp {{ number_format($product->price, 0, ',', '.') }}
              </span>
            @endif
          </div>

          <!-- Brief Intro / Excerpt -->
          <div class="border-t border-scm-gray-200 pt-5">
            <p class="text-xs sm:text-sm text-scm-gray-600 leading-relaxed font-light">
              {{ $product->description }}
            </p>
          </div>

          <!-- Size Selector -->
          @if($sizes->isNotEmpty())
            <div class="border-t border-scm-gray-200 pt-6 space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-[0.2em] font-bold text-scm-black">
                  Select Size: <span class="font-mono text-scm-gray-500" x-text="selectedSize || 'Required'"></span>
                </span>
                <span class="text-[11px] uppercase tracking-wider text-scm-gray-400 font-mono">
                  Streetwear Boxy Fit
                </span>
              </div>

              <div class="flex flex-wrap gap-2.5">
                @foreach($sizes as $size)
                  @php
                    $variant = $product->variants->where('size', $size)->first();
                    $inStock = $variant ? $variant->stock > 0 : true;
                  @endphp
                  <button
                    type="button"
                    @click="selectedSize = '{{ $size }}'"
                    :disabled="{{ $inStock ? 'false' : 'true' }}"
                    :class="selectedSize === '{{ $size }}' ? 'bg-scm-black text-white border-scm-black' : '{{ $inStock ? 'bg-white text-scm-black border-scm-gray-300 hover:border-scm-black' : 'bg-scm-gray-100 text-scm-gray-400 border-scm-gray-200 cursor-not-allowed line-through' }}'"
                    class="border px-4 py-2.5 text-xs uppercase tracking-widest font-semibold transition-colors duration-150"
                  >
                    {{ $size }}
                  </button>
                @endforeach
              </div>
            </div>
          @endif

          <!-- Quantity Stepper -->
          <div class="flex items-center gap-4 pt-2">
            <span class="text-xs uppercase tracking-[0.2em] font-bold text-scm-black">Quantity:</span>
            <div class="flex items-center border border-scm-gray-300">
              <button
                type="button"
                @click="qty = Math.max(1, qty - 1)"
                class="px-3.5 py-1.5 text-xs hover:bg-scm-gray-100 transition-colors text-scm-black font-semibold"
              >
                &minus;
              </button>
              <span class="px-4 py-1.5 text-xs font-mono font-bold text-scm-black min-w-[2.5rem] text-center" x-text="qty"></span>
              <button
                type="button"
                @click="qty++"
                class="px-3.5 py-1.5 text-xs hover:bg-scm-gray-100 transition-colors text-scm-black font-semibold"
              >
                +
              </button>
            </div>
          </div>

          <!-- Add to Cart & Wishlist Actions -->
          <div class="space-y-3 pt-4">
            <!-- Add to Shopping Bag Button -->
            <button
              type="button"
              @click="addToBag()"
              :disabled="loadingCart || !selectedSize"
              class="w-full bg-scm-black text-white py-4 text-xs uppercase tracking-[0.25em] font-bold hover:bg-scm-gray-800 disabled:bg-scm-gray-300 disabled:cursor-not-allowed transition-colors duration-200 flex items-center justify-center gap-2"
            >
              <template x-if="loadingCart">
                <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
              </template>
              <span x-text="loadingCart ? 'Adding To Bag...' : (selectedSize ? 'Add To Shopping Bag' : 'Select A Size First')"></span>
            </button>

            <!-- Wishlist Toggle Button -->
            <button
              type="button"
              @click="toggleWishlist()"
              :disabled="loadingWishlist"
              class="w-full border border-scm-black bg-white text-scm-black py-3.5 text-xs uppercase tracking-[0.2em] font-semibold hover:bg-scm-gray-50 transition-colors duration-200 flex items-center justify-center gap-2"
            >
              <svg
                class="w-4 h-4 transition-colors"
                :class="isWishlisted ? 'text-red-600 fill-current' : 'text-scm-black fill-none stroke-current'"
                viewBox="0 0 24 24"
                stroke-width="1.8"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
              </svg>
              <span x-text="isWishlisted ? 'Saved in Wishlist' : 'Add To Wishlist'"></span>
            </button>
          </div>

          <!-- Accordion Information Tabs -->
          <div class="border-t border-scm-gray-200 pt-6 divide-y divide-scm-gray-200" x-data="{ activeTab: 'description' }">
            <!-- Tab 1: Description -->
            <div class="py-3">
              <button
                type="button"
                @click="activeTab = activeTab === 'description' ? null : 'description'"
                class="w-full flex items-center justify-between text-xs font-bold uppercase tracking-[0.2em] text-scm-black"
              >
                <span>Garment Description</span>
                <span x-text="activeTab === 'description' ? '&minus;' : '+'" class="text-sm font-mono"></span>
              </button>
              <div x-show="activeTab === 'description'" x-collapse class="pt-3 text-xs text-scm-gray-600 leading-relaxed font-light">
                {!! nl2br(e($product->description)) !!}
              </div>
            </div>

            <!-- Tab 2: Sizing & Specifications -->
            <div class="py-3">
              <button
                type="button"
                @click="activeTab = activeTab === 'specs' ? null : 'specs'"
                class="w-full flex items-center justify-between text-xs font-bold uppercase tracking-[0.2em] text-scm-black"
              >
                <span>Specifications & Fit</span>
                <span x-text="activeTab === 'specs' ? '&minus;' : '+'" class="text-sm font-mono"></span>
              </button>
              <div x-show="activeTab === 'specs'" x-collapse class="pt-3 text-xs text-scm-gray-600 space-y-2">
                @if($product->weight)
                  <div class="flex justify-between py-1 border-b border-scm-gray-100">
                    <span class="uppercase tracking-wider">Garment Weight</span>
                    <span class="font-mono text-scm-black font-semibold">{{ $product->weight }} Grams</span>
                  </div>
                @endif
                <div class="flex justify-between py-1 border-b border-scm-gray-100">
                  <span class="uppercase tracking-wider">Silhouette</span>
                  <span class="text-scm-black font-semibold">Oversized Drop-Shoulder</span>
                </div>
                <div class="flex justify-between py-1 border-b border-scm-gray-100">
                  <span class="uppercase tracking-wider">Origin</span>
                  <span class="text-scm-black">Handcrafted in Indonesia</span>
                </div>
                <div class="flex justify-between py-1 border-b border-scm-gray-100">
                  <span class="uppercase tracking-wider">Care Instructions</span>
                  <span class="text-scm-black">Cold wash inside-out &bull; Do not iron on print</span>
                </div>
              </div>
            </div>

            <!-- Tab 3: Shipping & Authenticity -->
            <div class="py-3">
              <button
                type="button"
                @click="activeTab = activeTab === 'shipping' ? null : 'shipping'"
                class="w-full flex items-center justify-between text-xs font-bold uppercase tracking-[0.2em] text-scm-black"
              >
                <span>Shipping & Domestic Express</span>
                <span x-text="activeTab === 'shipping' ? '&minus;' : '+'" class="text-sm font-mono"></span>
              </button>
              <div x-show="activeTab === 'shipping'" x-collapse class="pt-3 text-xs text-scm-gray-600 space-y-2">
                <p>
                  Orders placed before 15:00 WIB are dispatched the same business day via JNE, SiCepat, or J&amp;T Express.
                </p>
                <p>
                  100% Genuine Street Culture Market authenticity guaranteed with every purchase.
                </p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- UPSELLING SECTION: UPGRADE YOUR PURCHASE (Thesis Core Novelty) -->
    @if(isset($upsells) && $upsells->count() > 0)
      <div class="mt-24 pt-16 border-t-2 border-scm-black">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-2">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span class="inline-block w-2 h-2 bg-red-600 rounded-full"></span>
              <span class="text-xs uppercase tracking-[0.3em] text-red-600 font-bold">
                Recommended Tier Upgrade
              </span>
            </div>
            <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tight text-scm-black">
              Upgrade To Premium Tier
            </h2>
            <p class="text-xs text-scm-gray-500 uppercase tracking-widest mt-1 max-w-2xl">
              Elevate your silhouette with heavier 280-330 GSM cotton, reinforced double stitching, and premium metal hardware.
            </p>
          </div>
          <a
            href="{{ route('products.index', ['sort' => 'price_desc']) }}"
            class="text-xs uppercase tracking-widest font-semibold text-scm-black hover:text-scm-gray-600 transition-colors flex items-center gap-1"
          >
            Explore Top Tier &rarr;
          </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
          @foreach($upsells as $upsell)
            <x-product-card :product="$upsell" />
          @endforeach
        </div>
      </div>
    @endif

    <!-- CROSS-SELLING SECTION: COMPLETE THE LOOK (Style Coordinates) -->
    @if(isset($crossSells) && $crossSells->count() > 0)
      <div class="mt-20 pt-16 border-t border-scm-gray-200">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-2">
          <div>
            <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-400 font-bold block mb-1">
              Style Coordinates
            </span>
            <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tight text-scm-black">
              Complete The Look
            </h2>
            <p class="text-xs text-scm-gray-500 uppercase tracking-widest mt-1">
              Curated complementary pieces designed to match and complete this silhouette.
            </p>
          </div>
          <a
            href="{{ route('products.index') }}"
            class="text-xs uppercase tracking-widest font-semibold text-scm-black hover:text-scm-gray-600 transition-colors flex items-center gap-1"
          >
            View All Pieces &rarr;
          </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
          @foreach($crossSells as $crossSell)
            <x-product-card :product="$crossSell" />
          @endforeach
        </div>
      </div>
    @endif

    <!-- RECENTLY VIEWED SECTION -->
    @if(isset($recentlyViewed) && $recentlyViewed->count() > 0)
      <div class="mt-20 pt-16 border-t border-scm-gray-200">
        <div class="flex items-center justify-between mb-8">
          <div>
            <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-400 font-bold block mb-1">
              Browsing History
            </span>
            <h2 class="text-xl md:text-2xl font-black uppercase tracking-tight text-scm-black">
              Recently Viewed
            </h2>
          </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
          @foreach($recentlyViewed as $recent)
            <x-product-card :product="$recent" />
          @endforeach
        </div>
      </div>
    @endif

  </div>

  <!-- Alpine.js Product Detail Logic -->
  <script>
    function productDetail(config) {
      return {
        selectedImage: config.initialImage || '',
        selectedSize: config.firstSize || null,
        qty: 1,
        isWishlisted: config.initialWishlisted || false,
        loadingCart: false,
        loadingWishlist: false,

        async addToBag() {
          if (!this.selectedSize) {
            window.dispatchEvent(new CustomEvent('toast', {
              detail: { message: 'Please select a size first', type: 'error' }
            }));
            return;
          }

          this.loadingCart = true;
          try {
            const res = await fetch('{{ route('cart.add') }}', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
              },
              body: JSON.stringify({
                product_id: config.productId,
                size: this.selectedSize,
                quantity: this.qty
              })
            });

            if (!res.ok) {
              const err = await res.json();
              throw new Error(err.message || 'Failed to add item to bag');
            }

            const data = await res.json();
            window.dispatchEvent(new CustomEvent('cart-updated', { detail: data.cart }));
            window.dispatchEvent(new CustomEvent('open-cart'));
            window.dispatchEvent(new CustomEvent('toast', {
              detail: { message: 'Item added to shopping bag', type: 'success' }
            }));
          } catch (e) {
            window.dispatchEvent(new CustomEvent('toast', {
              detail: { message: e.message || 'Error adding item', type: 'error' }
            }));
          } finally {
            this.loadingCart = false;
          }
        },

        async toggleWishlist() {
          this.loadingWishlist = true;
          try {
            const res = await fetch(`/wishlist/toggle/${config.productId}`, {
              method: 'POST',
              headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
              }
            });

            if (res.status === 401) {
              window.location.href = '{{ route('login') }}';
              return;
            }

            const data = await res.json();
            this.isWishlisted = data.is_wishlisted;
            window.dispatchEvent(new CustomEvent('toast', {
              detail: { message: data.message, type: 'success' }
            }));
          } catch (e) {
            window.dispatchEvent(new CustomEvent('toast', {
              detail: { message: 'Failed to update wishlist', type: 'error' }
            }));
          } finally {
            this.loadingWishlist = false;
          }
        }
      }
    }
  </script>
</x-app-layout>
