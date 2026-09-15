@php
    $cartCount = app(\App\Services\CartService::class)->count();
@endphp

<nav
  x-data="{ mobileOpen: false, scrolled: false, searchOpen: false }"
  @scroll.window="scrolled = window.scrollY > 20"
  :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-sm' : 'bg-white'"
  class="sticky top-0 z-30 transition-all duration-300 border-b border-scm-gray-200"
>
  <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
    <div class="flex items-center justify-between h-16 md:h-20">

      <!-- Logo -->
      <a href="{{ route('home') }}" class="flex items-center gap-2 group">
        <span class="text-xl md:text-2xl font-black uppercase tracking-[0.25em] text-scm-black group-hover:opacity-75 transition-opacity">
          SCM
        </span>
        <span class="hidden lg:inline-block text-xs uppercase tracking-widest text-scm-gray-500 border-l border-scm-gray-300 pl-2">
          Street Culture Market
        </span>
      </a>

      <!-- Desktop Navigation -->
      <div class="hidden md:flex items-center gap-8">
        <a href="{{ route('products.index') }}"
           class="text-xs uppercase tracking-[0.2em] font-medium text-scm-black hover:text-scm-gray-500 transition-colors">
          Catalog
        </a>
        <a href="{{ route('products.index', ['sort' => 'latest']) }}"
           class="text-xs uppercase tracking-[0.2em] font-medium text-scm-black hover:text-scm-gray-500 transition-colors">
          New Arrivals
        </a>
        <a href="{{ route('products.index', ['sale' => '1']) }}"
           class="text-xs uppercase tracking-[0.2em] font-medium text-red-600 hover:text-red-700 transition-colors">
          Sale
        </a>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-5">
        <!-- Search Trigger -->
        <button
          @click="$dispatch('open-search')"
          type="button"
          class="text-scm-black hover:text-scm-gray-500 transition-colors p-1"
          aria-label="Search"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </button>

        <!-- Account / Admin Link -->
        @auth
          @if(auth()->user()->isAdmin() || auth()->user()->isOwner())
            <a href="/admin" class="text-xs uppercase tracking-wider bg-scm-black text-white px-3 py-1.5 rounded-none hover:bg-scm-gray-800 transition-colors">
              Admin Panel
            </a>
          @else
            <a href="{{ route('dashboard') }}" class="text-scm-black hover:text-scm-gray-500 transition-colors p-1" aria-label="My Account">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </a>
          @endif
        @else
          <a href="{{ route('login') }}" class="hidden sm:inline-block text-xs uppercase tracking-[0.15em] font-medium text-scm-black hover:text-scm-gray-500 transition-colors">
            Login
          </a>
        @endauth

        <!-- Cart Drawer Button -->
        <button
          @click="$dispatch('open-cart')"
          type="button"
          class="relative text-scm-black hover:text-scm-gray-500 transition-colors p-1"
          aria-label="Shopping Cart"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
          </svg>
          <span
            x-data="{ count: {{ $cartCount }} }"
            @cart-updated.window="count = $event.detail.count"
            x-show="count > 0"
            x-text="count"
            class="absolute -top-1.5 -right-1.5 bg-scm-black text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center"
          >
            {{ $cartCount }}
          </span>
        </button>

        <!-- Mobile Menu Hamburger -->
        <button
          @click="mobileOpen = !mobileOpen"
          type="button"
          class="md:hidden text-scm-black hover:text-scm-gray-500 p-1"
          aria-label="Toggle Menu"
        >
          <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
          <svg x-show="mobileOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Collapsible Menu -->
    <div
      x-show="mobileOpen"
      x-collapse
      class="md:hidden border-t border-scm-gray-200 py-4 space-y-3 bg-white"
    >
      <a href="{{ route('products.index') }}" class="block text-xs uppercase tracking-[0.2em] font-medium py-2 text-scm-black">
        Catalog
      </a>
      <a href="{{ route('products.index', ['sort' => 'latest']) }}" class="block text-xs uppercase tracking-[0.2em] font-medium py-2 text-scm-black">
        New Arrivals
      </a>
      <a href="{{ route('products.index', ['sale' => '1']) }}" class="block text-xs uppercase tracking-[0.2em] font-medium py-2 text-red-600">
        Sale Drops
      </a>
      @guest
        <div class="pt-2 border-t border-scm-gray-100 flex gap-4">
          <a href="{{ route('login') }}" class="text-xs uppercase tracking-wider font-semibold py-2">Login</a>
          <a href="{{ route('register') }}" class="text-xs uppercase tracking-wider font-semibold py-2 text-scm-gray-600">Register</a>
        </div>
      @endguest
    </div>
  </div>
</nav>
