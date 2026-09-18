<x-app-layout>
    {{-- 1. Hero Slideshow Banner --}}
    <x-hero-banner :banners="$banners" />

    {{-- 2. Marquee Ticker — brand identity statement --}}
    <x-section-marquee />

    {{-- 3. New Arrivals Grid --}}
    <x-section-new-arrivals :products="$newArrivals" />

    {{-- 4. Collections / Categories Showcase --}}
    <x-section-collections :categories="$categories" />

    {{-- 5. Editorial Grid — drop highlights --}}
    <x-section-editorial-grid :products="$editorialProducts" />

    {{-- 6. Featured Curated Selection --}}
    <x-section-featured :products="$featuredProducts" />

    {{-- 7. Streetwear Brand Story & Ethos --}}
    <x-section-brand-story />
</x-app-layout>
