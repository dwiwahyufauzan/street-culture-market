<x-app-layout>
  <div class="bg-scm-white border-b border-scm-gray-200 py-10 md:py-16">
    <div class="container-scm section-padding !py-0">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-400 font-semibold block mb-2">
            Streetwear Catalog
          </span>
          <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-scm-black">
            All Collections
          </h1>
        </div>
        <p class="text-xs uppercase tracking-widest text-scm-gray-500 font-mono">
          Showing {{ $products->total() }} Products
        </p>
      </div>
    </div>
  </div>

  <div class="container-scm section-padding">
    <!-- Category pills filter -->
    <div class="flex flex-wrap items-center gap-2 mb-10 pb-4 border-b border-scm-gray-200">
      <a
        href="{{ route('products.index') }}"
        class="text-xs uppercase tracking-widest font-semibold px-4 py-2 border {{ !request('category') ? 'bg-scm-black text-white border-scm-black' : 'border-scm-gray-300 text-scm-black hover:border-scm-black' }}"
      >
        All
      </a>
      @foreach($categories as $cat)
        <a
          href="{{ route('products.index', ['category' => $cat->slug]) }}"
          class="text-xs uppercase tracking-widest font-semibold px-4 py-2 border {{ request('category') === $cat->slug ? 'bg-scm-black text-white border-scm-black' : 'border-scm-gray-300 text-scm-black hover:border-scm-black' }}"
        >
          {{ $cat->name }}
        </a>
      @endforeach
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
      @forelse($products as $product)
        <x-product-card :product="$product" />
      @empty
        <div class="col-span-full py-20 text-center text-scm-gray-400 uppercase tracking-widest text-sm">
          No garments matching your filter criteria.
        </div>
      @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-12">
      {{ $products->links() }}
    </div>
  </div>
</x-app-layout>
