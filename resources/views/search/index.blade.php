<x-app-layout>
  <!-- Search Results Header -->
  <div class="bg-scm-white border-b border-scm-gray-200 py-10 md:py-16">
    <div class="container-scm section-padding !py-0">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-400 font-semibold block mb-2">
            Catalog Search
          </span>
          <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-scm-black">
            @if($query)
              Search: "{{ $query }}"
            @else
              Explore Archive
            @endif
          </h1>
        </div>
        <p class="text-xs uppercase tracking-widest text-scm-gray-500 font-mono">
          Found {{ $products->total() }} Garments
        </p>
      </div>

      <!-- In-Page Search Form -->
      <div class="mt-8 max-w-2xl">
        <form action="{{ route('search.index') }}" method="GET" class="relative">
          <input
            type="text"
            name="q"
            value="{{ $query }}"
            placeholder="SEARCH TEES, HOODIES, CARGO, JACKETS..."
            class="w-full border-b-2 border-scm-black py-3 pl-2 pr-28 text-sm uppercase tracking-wider font-semibold focus:outline-none placeholder:text-scm-gray-300"
          >
          <button
            type="submit"
            class="absolute right-0 top-1/2 -translate-y-1/2 bg-scm-black text-white px-5 py-2 text-xs uppercase tracking-widest hover:bg-neutral-800 transition-colors"
          >
            Search
          </button>
        </form>
      </div>
    </div>
  </div>

  <div class="container-scm section-padding">
    @if($query && $categories->isNotEmpty())
      <!-- Category Filter Pills -->
      <div class="flex flex-wrap items-center gap-2 mb-8 pb-4 border-b border-scm-gray-200">
        <a
          href="{{ request()->fullUrlWithQuery(['category' => null]) }}"
          class="text-xs uppercase tracking-widest font-semibold px-4 py-2 border transition-colors {{ !request('category') ? 'bg-scm-black text-white border-scm-black' : 'border-scm-gray-300 text-scm-black hover:border-scm-black' }}"
        >
          All Categories
        </a>
        @foreach($categories as $cat)
          <a
            href="{{ request()->fullUrlWithQuery(['category' => $cat->slug]) }}"
            class="text-xs uppercase tracking-widest font-semibold px-4 py-2 border transition-colors {{ request('category') === $cat->slug ? 'bg-scm-black text-white border-scm-black' : 'border-scm-gray-300 text-scm-black hover:border-scm-black' }}"
          >
            {{ $cat->name }}
          </a>
        @endforeach
      </div>

      <!-- Filter & Sort Toolbar -->
      <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 bg-scm-gray-50 border border-scm-gray-200">
        <!-- Size Filter Pills -->
        <div class="flex flex-wrap items-center gap-2">
          <span class="text-xs uppercase tracking-widest font-bold text-scm-black mr-2">Size:</span>
          <a
            href="{{ request()->fullUrlWithQuery(['size' => null]) }}"
            class="text-[11px] uppercase tracking-wider font-semibold px-3 py-1 border transition-colors {{ !request('size') ? 'bg-scm-black text-white border-scm-black' : 'bg-white border-scm-gray-300 text-scm-black hover:border-scm-black' }}"
          >
            All
          </a>
          @foreach($availableSizes as $size)
            <a
              href="{{ request()->fullUrlWithQuery(['size' => $size]) }}"
              class="text-[11px] uppercase tracking-wider font-semibold px-3 py-1 border transition-colors {{ request('size') === $size ? 'bg-scm-black text-white border-scm-black' : 'bg-white border-scm-gray-300 text-scm-black hover:border-scm-black' }}"
            >
              {{ $size }}
            </a>
          @endforeach
        </div>

        <!-- Sorting Select & Reset -->
        <div class="flex items-center gap-3">
          @if(request('category') || request('size') || request('sort'))
            <a
              href="{{ route('search.index', ['q' => $query]) }}"
              class="text-xs uppercase tracking-wider text-scm-gray-500 hover:text-scm-black underline font-mono"
            >
              Reset Filters
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
    @endif

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
            @if($query)
              No Garments Found for "{{ $query }}"
            @else
              Enter a search query above to explore our garment archive
            @endif
          </h3>
          <p class="text-xs text-scm-gray-500 uppercase tracking-widest max-w-sm mx-auto mb-6">
            Check your spelling, try broader search terms (e.g. "hoodie", "cargo", "vintage"), or explore our collections.
          </p>

          <div class="flex flex-wrap items-center justify-center gap-3">
            <a
              href="{{ route('products.index') }}"
              class="inline-block bg-scm-black text-white text-xs uppercase tracking-[0.2em] font-semibold px-6 py-3 hover:bg-neutral-800 transition-colors"
            >
              Explore All Collections &rarr;
            </a>
          </div>
        </div>
      @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-12">
      {{ $products->links() }}
    </div>
  </div>
</x-app-layout>
