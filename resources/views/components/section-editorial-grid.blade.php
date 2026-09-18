{{--
  Section Editorial Grid
  Asimetris 2-kolom layout untuk highlight produk / koleksi utama.
  Terinspirasi Canalize.asia editorial look.
--}}
@props(['products'])

@if($products->count() >= 2)
<section class="section-padding bg-scm-gray-50 border-b border-scm-gray-200">
  <div class="container-scm">

    {{-- Section Header --}}
    <div class="flex items-end justify-between mb-10 md:mb-14">
      <div>
        <span class="label-overline block mb-2">Editorial Selects</span>
        <h2 class="heading-section">Drop Highlights</h2>
      </div>
      <a href="{{ route('products.index', ['sort' => 'latest']) }}"
         class="hidden md:inline-flex items-center gap-2 link-underline pb-1">
        View All &rarr;
      </a>
    </div>

    {{-- Asymmetric Editorial Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-6">

      {{-- Left: Large Hero Product (spans 7 cols) --}}
      @php $hero = $products->first(); @endphp
      @php $heroImg = $hero->primaryImage; @endphp
      @php $heroHasImg = $heroImg && $heroImg->image_path && file_exists(public_path('storage/' . $heroImg->image_path)); @endphp

      <a href="{{ route('products.show', $hero->slug) }}"
         class="group md:col-span-7 relative aspect-[3/4] md:aspect-auto md:h-[640px] overflow-hidden bg-scm-black block">

        {{-- Image / Placeholder --}}
        @if($heroHasImg)
          <img
            src="{{ asset('storage/' . $heroImg->image_path) }}"
            alt="{{ $hero->name }}"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out opacity-90"
            loading="lazy"
          >
        @else
          <div class="w-full h-full bg-gradient-to-br from-scm-gray-900 to-scm-black flex items-center justify-center group-hover:scale-105 transition-transform duration-700">
            <span class="text-[20vw] md:text-[12vw] font-black text-white/5 uppercase tracking-widest">SCM</span>
          </div>
        @endif

        {{-- Bottom Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent flex flex-col justify-end p-6 md:p-8">
          <div class="space-y-2">
            <span class="label-overline text-scm-gray-400">
              {{ $hero->category?->name ?? 'Featured Drop' }}
            </span>
            <h3 class="text-2xl md:text-3xl font-black uppercase tracking-tight text-white leading-tight">
              {{ $hero->name }}
            </h3>
            <div class="flex items-center gap-3 pt-2">
              @if($hero->has_discount)
                <span class="text-white font-bold text-sm">Rp {{ number_format($hero->sale_price, 0, ',', '.') }}</span>
                <span class="text-scm-gray-400 text-xs line-through">Rp {{ number_format($hero->price, 0, ',', '.') }}</span>
              @else
                <span class="text-white font-bold text-sm">Rp {{ number_format($hero->price, 0, ',', '.') }}</span>
              @endif
            </div>
            <div class="pt-2">
              <span class="inline-block text-xs uppercase tracking-[0.25em] font-bold text-white group-hover:underline underline-offset-4">
                Shop Now &rarr;
              </span>
            </div>
          </div>
        </div>

        {{-- Sale badge --}}
        @if($hero->has_discount)
          <div class="absolute top-4 left-4">
            <span class="badge-sale">Sale</span>
          </div>
        @endif
      </a>

      {{-- Right: Stacked 2 small products (spans 5 cols) --}}
      <div class="md:col-span-5 grid grid-rows-2 gap-4 md:gap-6 md:h-[640px]">
        @foreach($products->skip(1)->take(2) as $product)
          @php $img = $product->primaryImage; @endphp
          @php $hasImg = $img && $img->image_path && file_exists(public_path('storage/' . $img->image_path)); @endphp

          <a href="{{ route('products.show', $product->slug) }}"
             class="group relative overflow-hidden bg-scm-gray-900 flex-1 block">

            @if($hasImg)
              <img
                src="{{ asset('storage/' . $img->image_path) }}"
                alt="{{ $product->name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out opacity-85"
                loading="lazy"
              >
            @else
              <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-scm-gray-800 to-scm-gray-900 group-hover:scale-105 transition-transform duration-700">
                <span class="text-6xl font-black text-white/5 uppercase tracking-widest">
                  {{ substr($product->name, 0, 3) }}
                </span>
              </div>
            @endif

            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent flex flex-col justify-end p-5">
              <span class="label-overline text-scm-gray-400 mb-1">{{ $product->category?->name ?? 'Apparel' }}</span>
              <h3 class="text-lg font-black uppercase tracking-wider text-white leading-tight">
                {{ $product->name }}
              </h3>
              <div class="mt-1.5 flex items-center gap-2">
                @if($product->has_discount)
                  <span class="text-white font-bold text-sm">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</span>
                  <span class="text-scm-gray-400 text-xs line-through">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                @else
                  <span class="text-white font-bold text-sm">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                @endif
              </div>
            </div>

            @if($product->has_discount)
              <div class="absolute top-3 left-3">
                <span class="badge-sale">Sale</span>
              </div>
            @endif
          </a>
        @endforeach
      </div>
    </div>

    {{-- Mobile CTA --}}
    <div class="mt-8 text-center md:hidden">
      <a href="{{ route('products.index', ['sort' => 'latest']) }}" class="btn-outline text-xs px-6">
        View All Drops &rarr;
      </a>
    </div>

  </div>
</section>
@endif
