@props(['categories'])

<section class="section-padding bg-white border-b border-scm-gray-200">
  <div class="container-scm">
    <!-- Header -->
    <div class="text-center max-w-2xl mx-auto mb-12 md:mb-16">
      <span class="text-xs uppercase tracking-[0.35em] text-scm-gray-400 font-bold block mb-2">
        Curated Divisions
      </span>
      <h2 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-scm-black">
        Capsule Collections
      </h2>
      <p class="mt-3 text-xs md:text-sm text-scm-gray-500 uppercase tracking-widest">
        Architectural cuts, heavyweight textiles, and functional utility
      </p>
    </div>

    <!-- Category Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      @foreach($categories as $category)
        <a href="{{ route('products.index', ['category' => $category->slug]) }}"
          class="group relative h-80 overflow-hidden bg-scm-black flex flex-col justify-end p-8 border border-scm-gray-200 hover:border-scm-black transition-colors">
          <!-- Background image or gradient -->
          <div class="absolute inset-0 bg-gradient-to-t from-scm-black via-scm-black/60 to-transparent z-10"></div>

          <div
            class="absolute inset-0 bg-scm-gray-900 group-hover:scale-105 transition-transform duration-700 ease-out flex items-center justify-center">
            <span class="text-6xl font-black text-white/5 uppercase select-none tracking-widest">
              {{ substr($category->name, 0, 3) }}
            </span>
          </div>

          <!-- Content -->
          <div class="relative z-20 space-y-2 text-white">
            <span class="text-[10px] uppercase tracking-[0.3em] text-scm-gray-400 font-mono">
              SCM / 0{{ $loop->iteration }}
            </span>
            <h3
              class="text-xl md:text-2xl font-black uppercase tracking-wider group-hover:translate-x-1 transition-transform duration-300">
              {{ $category->name }}
            </h3>
            <p class="text-xs text-scm-gray-400 line-clamp-1 font-light">
              {{ $category->description }}
            </p>
            <div class="pt-2">
              <span
                class="text-[11px] uppercase tracking-[0.25em] font-bold text-white group-hover:underline underline-offset-4 inline-flex items-center gap-1">
                Explore Category &rarr;
              </span>
            </div>
          </div>
        </a>
      @endforeach
    </div>
  </div>
</section>