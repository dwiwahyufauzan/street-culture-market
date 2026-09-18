<x-app-layout>
  <!-- Wishlist Header -->
  <div class="bg-scm-white border-b border-scm-gray-200 py-10 md:py-14">
    <div class="container-scm section-padding !py-0">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-xs uppercase tracking-[0.25em] text-scm-gray-400 font-semibold mb-2">
            <a href="{{ route('home') }}" class="hover:text-scm-black transition-colors">Home</a>
            <span>/</span>
            <span class="text-scm-black">Saved Items</span>
          </div>
          <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tight text-scm-black">
            My Wishlist
          </h1>
        </div>
        <p class="text-xs uppercase tracking-widest text-scm-gray-500 font-mono">
          {{ $wishlists->total() }} Garments Saved
        </p>
      </div>
    </div>
  </div>

  <div class="container-scm section-padding">
    @if($wishlists->count() > 0)
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
        @foreach($wishlists as $item)
          @php
            $product = $item->product;
            $primaryImg = $product->primaryImage;
            $hasRealImg = $primaryImg && $primaryImg->image_path && file_exists(public_path('storage/' . $primaryImg->image_path));
          @endphp

          <div class="group relative flex flex-col bg-white border border-scm-gray-200 hover:border-scm-black transition-colors duration-300">
            <!-- Product Image -->
            <a href="{{ route('products.show', $product->slug) }}" class="block relative aspect-[3/4] overflow-hidden bg-scm-gray-100">
              @if($hasRealImg)
                <img
                  src="{{ asset('storage/' . $primaryImg->image_path) }}"
                  alt="{{ $product->name }}"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                  loading="lazy"
                >
              @else
                <div class="w-full h-full flex flex-col items-center justify-center p-6 bg-gradient-to-b from-scm-gray-50 to-scm-gray-200 group-hover:scale-105 transition-transform duration-700">
                  <div class="w-16 h-16 border-2 border-scm-black/20 flex items-center justify-center mb-3 group-hover:border-scm-black transition-colors">
                    <span class="text-xs font-black tracking-widest text-scm-black">SCM</span>
                  </div>
                  <span class="text-[10px] uppercase tracking-[0.25em] text-scm-gray-500 text-center font-mono">
                    {{ $product->category?->name ?? 'Streetwear' }}
                  </span>
                </div>
              @endif

              <!-- Remove Wishlist Button -->
              <form
                action="{{ route('wishlist.toggle', $product->id) }}"
                method="POST"
                class="absolute top-3 right-3 z-10"
              >
                @csrf
                <button
                  type="submit"
                  class="w-8 h-8 bg-white/90 backdrop-blur-xs text-scm-black border border-scm-gray-200 flex items-center justify-center hover:bg-scm-black hover:text-white transition-colors"
                  title="Remove from wishlist"
                >
                  <svg class="w-4 h-4 fill-current text-red-600" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                  </svg>
                </button>
              </form>

              @if($product->has_discount)
                <span class="absolute top-3 left-3 bg-scm-black text-white text-[10px] uppercase tracking-widest font-bold px-2 py-1 z-10">
                  Sale
                </span>
              @endif
            </a>

            <!-- Product Meta -->
            <div class="p-4 flex-1 flex flex-col justify-between">
              <div>
                <div class="flex items-center justify-between text-[11px] uppercase tracking-widest text-scm-gray-400 font-medium">
                  <span>{{ $product->category?->name ?? 'Apparel' }}</span>
                  @if($product->sku)
                    <span class="font-mono text-[10px]">{{ $product->sku }}</span>
                  @endif
                </div>

                <h3 class="mt-1.5 text-xs md:text-sm font-semibold uppercase tracking-wider text-scm-black line-clamp-1 group-hover:text-scm-gray-600 transition-colors">
                  <a href="{{ route('products.show', $product->slug) }}">
                    {{ $product->name }}
                  </a>
                </h3>

                <div class="mt-2 flex items-baseline gap-2">
                  @if($product->has_discount)
                    <span class="text-xs md:text-sm font-bold text-red-600">
                      Rp {{ number_format($product->sale_price, 0, ',', '.') }}
                    </span>
                    <span class="text-[11px] text-scm-gray-400 line-through">
                      Rp {{ number_format($product->price, 0, ',', '.') }}
                    </span>
                  @else
                    <span class="text-xs md:text-sm font-bold text-scm-black">
                      Rp {{ number_format($product->price, 0, ',', '.') }}
                    </span>
                  @endif
                </div>
              </div>

              <!-- Action Link -->
              <div class="mt-4 pt-3 border-t border-scm-gray-100">
                <a
                  href="{{ route('products.show', $product->slug) }}"
                  class="block w-full text-center bg-scm-black text-white py-2 text-[11px] uppercase tracking-[0.2em] font-semibold hover:bg-scm-gray-800 transition-colors"
                >
                  Select Size & Bag &rarr;
                </a>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <!-- Pagination -->
      <div class="mt-12">
        {{ $wishlists->links() }}
      </div>
    @else
      <!-- Empty Wishlist State -->
      <div class="py-24 px-4 text-center max-w-md mx-auto">
        <div class="w-16 h-16 mx-auto mb-6 border border-scm-gray-300 flex items-center justify-center">
          <svg class="w-7 h-7 text-scm-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
        </div>
        <h2 class="text-xl font-black uppercase tracking-tight text-scm-black mb-2">
          Your Wishlist Is Empty
        </h2>
        <p class="text-xs text-scm-gray-500 uppercase tracking-widest leading-relaxed mb-8">
          Save your favorite streetwear essentials and limited drops here to purchase them later.
        </p>
        <a
          href="{{ route('products.index') }}"
          class="inline-block bg-scm-black text-white text-xs uppercase tracking-[0.25em] font-semibold px-8 py-4 hover:bg-scm-gray-800 transition-colors"
        >
          Explore Catalog
        </a>
      </div>
    @endif
  </div>
</x-app-layout>
