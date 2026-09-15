<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
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

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="font-sans antialiased bg-scm-white text-scm-black selection:bg-scm-black selection:text-white flex flex-col min-h-screen">

    <!-- Announcement Bar -->
    <div class="bg-scm-black text-white text-center py-2 px-4 text-[10px] md:text-xs uppercase tracking-[0.25em] font-medium border-b border-scm-gray-900">
        Free Domestic Express Delivery on Orders Over Rp 500.000 &bull; Autumn/Winter Drop Active
    </div>

    <!-- Main Navigation -->
    <x-navbar />

    <!-- Search Modal -->
    <x-search-modal />

    <!-- Slide-over Cart Drawer -->
    <x-cart-drawer />

    <!-- Toast Notification Banner -->
    <x-toast />

    <!-- Main Content Slot -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- Master Footer -->
    <x-footer />

    @stack('scripts')
</body>
</html>
