<x-app-layout>
    <!-- 1. Hero Slideshow Banner -->
    <x-hero-banner :banners="$banners" />

    <!-- 2. New Arrivals Grid -->
    <x-section-new-arrivals :products="$newArrivals" />

    <!-- 3. Collections / Categories Showcase -->
    <x-section-collections :categories="$categories" />

    <!-- 4. Featured Curated Selection -->
    <x-section-featured :products="$featuredProducts" />

    <!-- 5. Streetwear Brand Story & Ethos -->
    <x-section-brand-story />
</x-app-layout>
