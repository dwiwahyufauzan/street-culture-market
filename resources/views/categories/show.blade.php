<x-app-layout>
  <!-- Collection Header -->
  <div class="bg-scm-white border-b border-scm-gray-200 py-10 md:py-16">
    <div class="container-scm section-padding !py-0">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-xs uppercase tracking-[0.25em] text-scm-gray-400 font-semibold mb-2">
            <a href="{{ route('home') }}" class="hover:text-scm-black transition-colors">Home</a>
            <span>/</span>
            <a href="{{ route('products.index') }}" class="hover:text-scm-black transition-colors">Collections</a>
            <span>/</span>
            <span class="text-scm-black">{{ $category->name }}</span>
          </div>
          <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-scm-black">
            {{ $category->name }}
          </h1>
          @if($category->description)
            <p class="text-xs md:text-sm text-scm-gray-600 mt-2 max-w-2xl font-light leading-relaxed">
              {{ $category->description }}
            </p>
          @endif
        </div>
        <p class="text-xs uppercase tracking-widest text-scm-gray-500 font-mono">
          Showing {{ $products->total() }} Products
        </p>
      </div>
    </div>
  </div>

  <div class="container-scm section-padding">
    <!-- Category Collections Navigation -->
    <div class="flex flex-wrap items-center gap-2 mb-8 pb-4 border-b border-scm-gray-200">
      <a
        href="{{ route('products.index') }}"
        class="text-xs uppercase tracking-widest font-semibold px-4 py-2 border border-scm-gray-300 text-scm-black hover:border-scm-black transition-colors"
      >
        All Collections
      </a>
      @foreach($categories as $cat)
        <a
          href="{{ route('categories.show', $cat->slug) }}"
          class="text-xs uppercase tracking-widest font-semibold px-4 py-2 border transition-colors {{ $category->slug === $cat->slug ? 'bg-scm-black text-white border-scm-black' : 'border-scm-gray-300 text-scm-black hover:border-scm-black' }}"
        >
          {{ $cat->name }}
        </a>
      @endforeach
    </div>

    <!-- Filter & Sort Bar -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 bg-scm-gray-50 border border-scm-gray-200">
      <!-- Size Filter Pills -->
      <div class="flex flex-wrap items-center gap-2">
        <span class="text-xs uppercase tracking-widest font-bold text-scm-black mr-2">Size:</span>
        <a
          href="{{ request()->fullUrlWithQuery(['size' => null]) }}"
          class="text-[11px] uppercase tracking-wider font-semibold px-3 py-1 border transition-colors {{ !request('size') ? 'bg-scm-black text-white border-scm-black' : 'bg-white border-scm-gray-300 text-scm-black hover:border-scm-black' }}"
        >
          All Sizes
        </a>
        @foreach($availableSizes as $size)
          <a
            href="{{ request()->fullUrlWithQuery(['size' => $size]) }}"
            class="text-[11px] uppercase tracking-wider font-semibold px-3 py-1 border transition-colors {{ request('size') === $size ? 'bg-scm-black text-white border-scm-black' : 'bg-white border-scm-gray-300 text-scm-black hover:border-scm-black' }}"
          >
            {{ $size }}
          </a>
        @endforeach

        <!-- Sale Toggle Filter -->
        <a
          href="{{ request('sale') ? request()->fullUrlWithQuery(['sale' => null]) : request()->fullUrlWithQuery(['sale' => '1']) }}"
          class="text-[11px] uppercase tracking-wider font-semibold px-3 py-1 border transition-colors ml-2 {{ request('sale') ? 'bg-red-600 text-white border-red-600' : 'bg-white border-scm-gray-300 text-red-600 hover:border-red-600' }}"
        >
          Sale Only
        </a>
      </div>

      <!-- Sorting Select & Reset -->
      <div class="flex items-center gap-3">
        @if(request('size') || request('sale') || request('sort'))
          <a
            href="{{ route('categories.show', $category->slug) }}"
            class="text-xs uppercase tracking-wider text-scm-gray-500 hover:text-scm-black underline font-mono"
          >
            Clear Filters
          </a>
        @endif

        <div class="flex items-center gap-2">
          <label for="sort" class="text-xs uppercase tracking-widest font-bold text-scm-black">Sort:</label>
          <select
            id="sort"
            onchange="location = this.value;"
            class="bg-white text-xs uppercase tracking-wider font-medium border border-scm-gray-300 px-3 py-1.5 focus:border-scm-black focus:ring-0"
          >
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}" {{ request('sort', 'latest') === 'latest' ? 'selected' : '' }}>Latest Releases</option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_asc']) }}" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'price_desc']) }}" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
            <option value="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
      @forelse($products as $product)
        <x-product-card :product="$product" />
      @empty
        <div class="col-span-full py-20 px-4 text-center border border-dashed border-scm-gray-300 bg-scm-gray-50">
          <div class="w-12 h-12 mx-auto mb-4 border border-scm-gray-400 flex items-center justify-center">
            <span class="text-xs font-mono font-bold text-scm-gray-400">0</span>
          </div>
          <h3 class="text-sm font-bold uppercase tracking-wider text-scm-black mb-1">
            No Garments Available
          </h3>
          <p class="text-xs text-scm-gray-500 uppercase tracking-widest max-w-sm mx-auto mb-6">
            There are currently no active products matching your selected criteria in this collection.
          </p>
          <a
            href="{{ route('products.index') }}"
            class="inline-block bg-scm-black text-white text-xs uppercase tracking-[0.2em] font-semibold px-6 py-3 hover:bg-scm-gray-800 transition-colors"
          >
            Explore All Catalog
          </a>
        </div>
      @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-12">
      {{ $products->links() }}
    </div>
  </div>
</x-app-layout>
