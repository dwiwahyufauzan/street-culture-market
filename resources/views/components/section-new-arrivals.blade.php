@props(['products'])

<section class="section-padding bg-scm-white border-b border-scm-gray-200">
  <div class="container-scm">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 md:mb-12 gap-4">
      <div>
        <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-400 font-semibold block mb-1">
          Recent Arrivals
        </span>
        <h2 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-scm-black">
          New Releases
        </h2>
      </div>
      <div>
        <a
          href="{{ route('products.index', ['sort' => 'latest']) }}"
          class="text-xs uppercase tracking-[0.25em] font-bold text-scm-black border-b-2 border-scm-black pb-1 hover:text-scm-gray-500 hover:border-scm-gray-500 transition-colors inline-flex items-center gap-2"
        >
          View Full Catalog &rarr;
        </a>
      </div>
    </div>

    <!-- Product Grid: 2 columns mobile, 4 columns desktop -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
      @forelse($products as $product)
        <x-product-card :product="$product" />
      @empty
        <div class="col-span-full py-16 text-center text-scm-gray-400 uppercase tracking-widest text-xs">
          No new arrivals listed yet.
        </div>
      @endforelse
    </div>
  </div>
</section>
