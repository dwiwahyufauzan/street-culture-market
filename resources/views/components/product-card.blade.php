@props(['product'])

@php
    $primaryImg = $product->primaryImage;
    $hasRealImg = $primaryImg && $primaryImg->image_path && file_exists(public_path('storage/' . $primaryImg->image_path));
@endphp

<div class="group relative flex flex-col bg-white border border-scm-gray-200 hover:border-scm-black transition-colors duration-300">
  <a href="{{ route('products.show', $product->slug) }}" class="block relative aspect-[3/4] overflow-hidden bg-scm-gray-100">
    
    @if($hasRealImg)
      <img
        src="{{ asset('storage/' . $primaryImg->image_path) }}"
        alt="{{ $product->name }}"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
        loading="lazy"
      >
    @else
      <!-- Clean Minimalist Streetwear Graphic Placeholder -->
      <div class="w-full h-full flex flex-col items-center justify-center p-6 bg-gradient-to-b from-scm-gray-50 to-scm-gray-200 group-hover:scale-105 transition-transform duration-700">
        <div class="w-16 h-16 border-2 border-scm-black/20 flex items-center justify-center mb-3 group-hover:border-scm-black transition-colors">
          <span class="text-xs font-black tracking-widest text-scm-black">SCM</span>
        </div>
        <span class="text-[10px] uppercase tracking-[0.25em] text-scm-gray-500 text-center font-mono">
          {{ $product->category?->name ?? 'Streetwear' }}
        </span>
      </div>
    @endif

    <!-- Badges -->
    <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10">
      @if($product->has_discount)
        <span class="bg-scm-black text-white text-[10px] uppercase tracking-widest font-bold px-2 py-1">
          Sale
        </span>
      @endif

      @if($product->is_featured)
        <span class="bg-white/90 backdrop-blur-xs text-scm-black border border-scm-gray-200 text-[10px] uppercase tracking-widest font-bold px-2 py-1">
          Featured
        </span>
      @endif
    </div>

    <!-- Quick View / Add Action Overlay -->
    <div class="absolute bottom-0 inset-x-0 bg-scm-black text-white py-3 text-center text-xs uppercase tracking-[0.2em] font-medium translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-in-out">
      View Details &rarr;
    </div>
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
    </div>

    <!-- Price Section -->
    <div class="mt-3 flex items-baseline gap-2">
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
</div>
