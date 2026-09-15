@props(['products'])

@if($products->count() > 0)
<section class="section-padding bg-scm-white border-b border-scm-gray-200">
  <div class="container-scm">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 md:mb-12 gap-4">
      <div>
        <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-400 font-semibold block mb-1">
          Hand-Picked Selects
        </span>
        <h2 class="text-2xl md:text-4xl font-black uppercase tracking-tight text-scm-black">
          Featured Garments
        </h2>
      </div>
      <div>
        <span class="text-xs uppercase tracking-[0.2em] text-scm-gray-500 font-mono">
          Autumn/Winter Selection
        </span>
      </div>
    </div>

    <!-- Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
      @foreach($products as $product)
        <x-product-card :product="$product" />
      @endforeach
    </div>
  </div>
</section>
@endif
