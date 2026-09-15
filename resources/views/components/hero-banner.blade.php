@props(['banners'])

@php
    $bannerCount = $banners->count();
@endphp

<div
  x-data="{
    current: 0,
    total: {{ $bannerCount > 0 ? $bannerCount : 1 }},
    timer: null,
    startAuto() {
      if (this.total > 1) {
        this.timer = setInterval(() => {
          this.current = (this.current + 1) % this.total;
        }, 6000);
      }
    },
    stopAuto() {
      clearInterval(this.timer);
    }
  }"
  x-init="startAuto()"
  @mouseenter="stopAuto()"
  @mouseleave="startAuto()"
  class="relative w-full h-[75vh] md:h-[90vh] overflow-hidden bg-scm-black text-white"
>
  @if($bannerCount > 0)
    @foreach($banners as $index => $banner)
      <div
        x-show="current === {{ $index }}"
        x-transition:enter="transition-opacity duration-1000 ease-in-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-700 ease-in-out"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0"
      >
        <!-- Background Banner -->
        <div class="absolute inset-0 bg-scm-black">
          @if($banner->image && file_exists(public_path('storage/' . $banner->image)))
            <img
              src="{{ asset('storage/' . $banner->image) }}"
              alt="{{ $banner->title }}"
              class="w-full h-full object-cover opacity-80 scale-105 transition-transform duration-10000"
            >
          @else
            <!-- Editorial Monochromatic Pattern Placeholder -->
            <div class="w-full h-full bg-radial from-scm-gray-800 via-scm-black to-black flex items-center justify-center relative overflow-hidden">
              <div class="absolute inset-0 bg-[linear-gradient(to_right,#1f1f1f_1px,transparent_1px),linear-gradient(to_bottom,#1f1f1f_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_50%,#000_70%,transparent_100%)] opacity-40"></div>
              <span class="text-[20vw] font-black text-white/5 uppercase select-none tracking-widest absolute">
                SCM
              </span>
            </div>
          @endif
        </div>

        <!-- High-Impact Vignette & Typography Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent flex flex-col justify-end p-6 md:p-16 lg:p-24">
          <div class="max-w-4xl space-y-4">
            <span class="inline-block text-xs md:text-sm font-semibold uppercase tracking-[0.35em] text-scm-gray-300 border-b border-scm-gray-600 pb-1">
              {{ $banner->subtitle ?? 'Collection Release' }}
            </span>
            <h1 class="text-4xl sm:text-6xl md:text-8xl font-black uppercase tracking-tight leading-none text-white drop-shadow-sm">
              {{ $banner->title }}
            </h1>
            <div class="pt-4">
              <a
                href="{{ $banner->link ?: route('products.index') }}"
                class="inline-block bg-white text-scm-black px-8 py-4 text-xs md:text-sm uppercase tracking-[0.25em] font-bold hover:bg-scm-gray-200 transition-colors shadow-lg"
              >
                Shop The Capsule &rarr;
              </a>
            </div>
          </div>
        </div>
      </div>
    @endforeach

    <!-- Pagination Dots Indicator -->
    @if($bannerCount > 1)
      <div class="absolute bottom-8 right-6 md:right-16 z-20 flex items-center gap-3">
        @foreach($banners as $index => $banner)
          <button
            @click="current = {{ $index }}"
            :class="current === {{ $index }} ? 'w-10 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
            class="h-1.5 transition-all duration-300 cursor-pointer"
            aria-label="Slide {{ $index + 1 }}"
          ></button>
        @endforeach
      </div>
    @endif

  @else
    <!-- Fallback if no banner in DB -->
    <div class="absolute inset-0 flex flex-col justify-end p-6 md:p-16 lg:p-24 bg-scm-black">
      <div class="max-w-3xl space-y-4">
        <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-400">Autumn / Winter Drop</span>
        <h1 class="text-5xl md:text-8xl font-black uppercase tracking-tight text-white">
          Tactical Monochrome
        </h1>
        <div class="pt-4">
          <a href="{{ route('products.index') }}" class="btn-primary bg-white text-black hover:bg-gray-200">
            Explore Collection
          </a>
        </div>
      </div>
    </div>
  @endif
</div>
