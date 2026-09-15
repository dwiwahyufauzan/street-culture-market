<x-app-layout>
  <div class="container-scm section-padding">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">

      <!-- Left: Image Gallery (Aspect 3:4) -->
      <div class="lg:col-span-7 space-y-4">
        <div class="aspect-[3/4] bg-scm-gray-100 overflow-hidden border border-scm-gray-200 relative">
          @if($product->primaryImage && file_exists(public_path('storage/' . $product->primaryImage->image_path)))
            <img
              src="{{ asset('storage/' . $product->primaryImage->image_path) }}"
              alt="{{ $product->name }}"
              class="w-full h-full object-cover"
            >
          @else
            <div class="w-full h-full flex flex-col items-center justify-center p-12 bg-gradient-to-b from-scm-gray-50 to-scm-gray-200">
              <span class="text-4xl font-black tracking-[0.25em] text-scm-black/30 mb-2">SCM</span>
              <span class="text-xs uppercase tracking-widest text-scm-gray-400 font-mono">{{ $product->category?->name }}</span>
            </div>
          @endif

          @if($product->has_discount)
            <span class="absolute top-4 left-4 bg-scm-black text-white text-xs uppercase tracking-widest font-bold px-3 py-1.5">
              Sale Drop
            </span>
          @endif
        </div>
      </div>

      <!-- Right: Product Info & Buy Form -->
      <div class="lg:col-span-5 flex flex-col justify-between space-y-8"
           x-data="{ selectedSize: '{{ $product->variants->first()?->size ?? 'M' }}', qty: 1 }">
        <div class="space-y-4">
          <div class="flex items-center justify-between text-xs uppercase tracking-widest text-scm-gray-400 font-medium">
            <span>{{ $product->category?->name ?? 'Apparel' }}</span>
            @if($product->sku)
              <span class="font-mono">{{ $product->sku }}</span>
            @endif
          </div>

          <h1 class="text-2xl sm:text-4xl font-black uppercase tracking-tight text-scm-black leading-tight">
            {{ $product->name }}
          </h1>

          <!-- Price -->
          <div class="flex items-baseline gap-3 pt-2">
            @if($product->has_discount)
              <span class="text-2xl font-bold text-red-600">
                Rp {{ number_format($product->sale_price, 0, ',', '.') }}
              </span>
              <span class="text-sm text-scm-gray-400 line-through">
                Rp {{ number_format($product->price, 0, ',', '.') }}
              </span>
            @else
              <span class="text-2xl font-bold text-scm-black">
                Rp {{ number_format($product->price, 0, ',', '.') }}
              </span>
            @endif
          </div>

          <div class="border-t border-scm-gray-200 pt-4">
            <p class="text-sm text-scm-gray-600 leading-relaxed font-light">
              {{ $product->description }}
            </p>
          </div>

          <!-- Size Selector -->
          @if($product->variants->count() > 0)
            <div class="border-t border-scm-gray-200 pt-6 space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs uppercase tracking-[0.2em] font-bold text-scm-black">Select Size</span>
                <span class="text-[11px] uppercase tracking-wider text-scm-gray-400">Regular Boxy Fit</span>
              </div>
              <div class="flex flex-wrap gap-2.5">
                @foreach($product->variants as $variant)
                  <button
                    type="button"
                    @click="selectedSize = '{{ $variant->size }}'"
                    :class="selectedSize === '{{ $variant->size }}' ? 'bg-scm-black text-white border-scm-black' : 'bg-white text-scm-black border-scm-gray-300 hover:border-scm-black'"
                    class="border px-4 py-2 text-xs uppercase tracking-widest font-semibold transition-colors"
                  >
                    {{ $variant->size }}
                  </button>
                @endforeach
              </div>
            </div>
          @endif

          <!-- Add to Cart Form -->
          <form action="{{ route('cart.add') }}" method="POST" class="pt-6 space-y-4">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <input type="hidden" name="size" :value="selectedSize">
            <input type="hidden" name="quantity" :value="qty">

            <button
              type="submit"
              class="btn-primary w-full py-4 text-xs tracking-[0.25em]"
            >
              Add To Shopping Bag
            </button>
          </form>

          <!-- Specifications -->
          <div class="border-t border-scm-gray-200 pt-6 space-y-2 text-xs text-scm-gray-500 uppercase tracking-wider">
            @if($product->weight)
              <div class="flex justify-between py-1 border-b border-scm-gray-100">
                <span>Weight</span>
                <span class="font-mono text-scm-black">{{ $product->weight }} Grams</span>
              </div>
            @endif
            <div class="flex justify-between py-1 border-b border-scm-gray-100">
              <span>Authenticity</span>
              <span class="text-scm-black font-semibold">100% Guaranteed</span>
            </div>
            <div class="flex justify-between py-1 border-b border-scm-gray-100">
              <span>Delivery</span>
              <span class="text-scm-black">Nationwide Express Available</span>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- UPSELLING SECTION: UPGRADE YOUR CHOICE -->
    @if(isset($upsells) && $upsells->count() > 0)
      <div class="mt-24 pt-16 border-t-2 border-scm-black">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-2">
          <div>
            <span class="text-xs uppercase tracking-[0.3em] text-red-600 font-bold block mb-1">
              &bull; Recommended Upgrade
            </span>
            <h2 class="text-2xl md:text-3xl font-black uppercase tracking-tight text-scm-black">
              Upgrade To Premium Tier
            </h2>
            <p class="text-xs text-scm-gray-500 uppercase tracking-widest mt-1">
              Customers looking at this item also elevated their purchase to these higher-specification garments
            </p>
          </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
          @foreach($upsells as $upsell)
            <x-product-card :product="$upsell" />
          @endforeach
        </div>
      </div>
    @endif

    <!-- CROSS-SELLING SECTION: COMPLETE THE LOOK -->
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
              Curated complementary pieces designed to match this silhouette
            </p>
          </div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
          @foreach($crossSells as $crossSell)
            <x-product-card :product="$crossSell" />
          @endforeach
        </div>
      </div>
    @endif

  </div>
</x-app-layout>
