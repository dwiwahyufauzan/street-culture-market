# 11 — Design System (Tailwind + Visual Guide)

## Filosofi Desain
Terinspirasi **Canalize.asia** — minimalis streetwear:
- Hitam & putih dominan
- Bold typography uppercase
- Banyak whitespace
- Animasi halus, tidak berlebihan
- Mobile-first

---

## Tailwind Config (`tailwind.config.js`)

```js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'scm': {
                    black:  '#0a0a0a',
                    white:  '#fafafa',
                    gray: {
                        50:  '#f9f9f9',
                        100: '#f3f3f3',
                        200: '#e5e5e5',
                        300: '#d4d4d4',
                        400: '#a3a3a3',
                        500: '#737373',
                        600: '#525252',
                        700: '#404040',
                        800: '#262626',
                        900: '#171717',
                    }
                }
            },
            letterSpacing: {
                'widest-2': '0.25em',
                'widest-3': '0.35em',
            },
            transitionDuration: {
                '400': '400ms',
            },
            animation: {
                'fade-in': 'fadeIn 0.5s ease-in-out',
                'slide-up': 'slideUp 0.4s ease-out',
                'fly-to-cart': 'flyToCart 0.6s ease-in-out',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                slideUp: {
                    '0%': { transform: 'translateY(20px)', opacity: '0' },
                    '100%': { transform: 'translateY(0)', opacity: '1' },
                },
                flyToCart: {
                    '0%': { transform: 'scale(1)', opacity: '1' },
                    '50%': { transform: 'scale(0.5) translate(100px, -100px)', opacity: '0.5' },
                    '100%': { transform: 'scale(0)', opacity: '0' },
                },
            },
        },
    },
    plugins: [
        forms,
        typography,
    ],
};
```

---

## CSS Global (`resources/css/app.css`)

```css
@import 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap';

@tailwind base;
@tailwind components;
@tailwind utilities;

/* Base styles */
@layer base {
    html {
        scroll-behavior: smooth;
    }

    body {
        @apply font-sans text-scm-black bg-scm-white antialiased;
    }

    h1, h2, h3, h4, h5, h6 {
        @apply font-bold uppercase tracking-tight;
    }

    /* Scrollbar minimal */
    ::-webkit-scrollbar {
        @apply w-1;
    }
    ::-webkit-scrollbar-track {
        @apply bg-scm-gray-100;
    }
    ::-webkit-scrollbar-thumb {
        @apply bg-scm-gray-400;
    }
}

/* Komponen reusable */
@layer components {
    /* Tombol utama */
    .btn-primary {
        @apply bg-scm-black text-white py-3 px-8 text-sm uppercase tracking-widest font-medium
               hover:bg-scm-gray-800 active:bg-scm-gray-900
               transition-colors duration-200 cursor-pointer;
    }

    /* Tombol outline */
    .btn-outline {
        @apply border border-scm-black text-scm-black py-3 px-8 text-sm uppercase tracking-widest font-medium
               hover:bg-scm-black hover:text-white
               transition-colors duration-200 cursor-pointer;
    }

    /* Input field */
    .input-field {
        @apply w-full border border-scm-gray-300 px-4 py-3 text-sm
               focus:border-scm-black focus:outline-none
               transition-colors duration-200;
    }

    /* Badge label */
    .badge {
        @apply inline-block px-3 py-1 text-xs uppercase tracking-wider font-medium;
    }

    /* Section padding standard */
    .section-padding {
        @apply px-4 md:px-8 lg:px-16 py-12 md:py-20;
    }

    /* Container standard */
    .container-scm {
        @apply max-w-screen-2xl mx-auto;
    }
}
```

---

## Layout Master (`resources/views/layouts/app.blade.php`)

```html
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}

    <title>{{ config('app.name', 'Street Culture Market') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="font-sans antialiased bg-scm-white text-scm-black">

    <!-- Announcement Bar -->
    <div class="bg-black text-white text-center py-2 text-xs uppercase tracking-widest">
        Free shipping on orders over Rp 300.000
    </div>

    <!-- Navbar -->
    <x-navbar />

    <!-- Cart Drawer -->
    <x-cart-drawer />

    <!-- Toast Notification -->
    <x-toast />

    <!-- Content -->
    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <x-footer />

    @stack('scripts')
</body>
</html>
```

---

## Komponen Navbar

```html
<!-- resources/views/components/navbar.blade.php -->
<nav
  x-data="{ mobileOpen: false, scrolled: false }"
  @scroll.window="scrolled = window.scrollY > 50"
  :class="scrolled ? 'bg-white shadow-sm' : 'bg-white'"
  class="sticky top-0 z-30 transition-shadow duration-300 border-b border-gray-100"
>
  <div class="max-w-screen-2xl mx-auto px-4 md:px-8">
    <div class="flex items-center justify-between h-16">

      <!-- Logo -->
      <a href="{{ route('home') }}"
         class="text-xl font-black uppercase tracking-[0.3em]">
        SCM
      </a>

      <!-- Desktop Navigation -->
      <div class="hidden md:flex items-center gap-8">
        <a href="{{ route('products.index') }}"
           class="text-sm uppercase tracking-wider hover:text-gray-500 transition-colors">
          Shop
        </a>
        <a href="{{ route('categories.show', 'new-arrivals') }}"
           class="text-sm uppercase tracking-wider hover:text-gray-500 transition-colors">
          New Arrivals
        </a>
        <a href="{{ route('products.index', ['sort' => 'sale']) }}"
           class="text-sm uppercase tracking-wider hover:text-gray-500 transition-colors">
          Sale
        </a>
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-4">
        <!-- Search -->
        <button class="text-sm hover:text-gray-500 transition-colors" onclick="document.getElementById('search-modal').showModal()">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </button>

        <!-- Account -->
        @auth
        <a href="{{ route('account.index') }}" class="text-sm hover:text-gray-500">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </a>
        @else
        <a href="{{ route('login') }}" class="text-sm uppercase tracking-wider hover:text-gray-500">Login</a>
        @endauth

        <!-- Cart -->
        <button
          @click="$dispatch('open-cart')"
          class="relative text-sm hover:text-gray-500"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
          </svg>
          @if(app(\App\Services\CartService::class)->count() > 0)
          <span class="absolute -top-2 -right-2 bg-black text-white text-xs w-4 h-4 rounded-full flex items-center justify-center">
            {{ app(\App\Services\CartService::class)->count() }}
          </span>
          @endif
        </button>

        <!-- Mobile menu toggle -->
        <button class="md:hidden" @click="mobileOpen = !mobileOpen">
          <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
          <svg x-show="mobileOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="mobileOpen" x-collapse class="md:hidden border-t border-gray-100 py-4 space-y-3">
      <a href="{{ route('products.index') }}" class="block text-sm uppercase tracking-wider py-2">Shop</a>
      <a href="{{ route('categories.show', 'new-arrivals') }}" class="block text-sm uppercase tracking-wider py-2">New Arrivals</a>
      <a href="#" class="block text-sm uppercase tracking-wider py-2">Sale</a>
    </div>
  </div>
</nav>
```

---

## Komponen Footer

```html
<!-- resources/views/components/footer.blade.php -->
<footer class="bg-black text-white mt-24">
  <div class="max-w-screen-2xl mx-auto px-4 md:px-8 py-16">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-12">

      <!-- Brand -->
      <div class="col-span-2 md:col-span-1">
        <h3 class="text-xl font-black uppercase tracking-[0.3em] mb-4">SCM</h3>
        <p class="text-sm text-gray-400 leading-relaxed">
          Street Culture Market — Your destination for premium streetwear.
        </p>
      </div>

      <!-- Shop -->
      <div>
        <h4 class="text-xs uppercase tracking-widest text-gray-400 mb-4">Shop</h4>
        <ul class="space-y-2">
          <li><a href="{{ route('products.index') }}" class="text-sm hover:text-gray-300 transition-colors">All Products</a></li>
          <li><a href="#" class="text-sm hover:text-gray-300 transition-colors">New Arrivals</a></li>
          <li><a href="#" class="text-sm hover:text-gray-300 transition-colors">Best Sellers</a></li>
          <li><a href="#" class="text-sm hover:text-gray-300 transition-colors">Sale</a></li>
        </ul>
      </div>

      <!-- Help -->
      <div>
        <h4 class="text-xs uppercase tracking-widest text-gray-400 mb-4">Help</h4>
        <ul class="space-y-2">
          <li><a href="#" class="text-sm hover:text-gray-300 transition-colors">Shipping Info</a></li>
          <li><a href="#" class="text-sm hover:text-gray-300 transition-colors">Returns</a></li>
          <li><a href="#" class="text-sm hover:text-gray-300 transition-colors">Size Guide</a></li>
          <li><a href="#" class="text-sm hover:text-gray-300 transition-colors">Contact Us</a></li>
        </ul>
      </div>

      <!-- Social -->
      <div>
        <h4 class="text-xs uppercase tracking-widest text-gray-400 mb-4">Follow</h4>
        <ul class="space-y-2">
          <li><a href="#" class="text-sm hover:text-gray-300 transition-colors">Instagram</a></li>
          <li><a href="#" class="text-sm hover:text-gray-300 transition-colors">TikTok</a></li>
          <li><a href="#" class="text-sm hover:text-gray-300 transition-colors">Twitter/X</a></li>
        </ul>
      </div>
    </div>

    <div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
      <p class="text-xs text-gray-500">&copy; {{ date('Y') }} Street Culture Market. All rights reserved.</p>
      <div class="flex items-center gap-4">
        <img src="https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg" alt="Visa" class="h-4 opacity-50">
        <!-- Payment badges -->
      </div>
    </div>
  </div>
</footer>
```
