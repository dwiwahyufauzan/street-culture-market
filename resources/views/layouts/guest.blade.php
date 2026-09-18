<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Street Culture Market') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-scm-black bg-scm-white antialiased">
    <div class="min-h-screen flex flex-col justify-between">

        <!-- Top Mini Navigation -->
        <header class="border-b border-scm-gray-200 bg-white py-4 px-6 sm:px-10 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                <span class="text-xl font-black uppercase tracking-[0.25em] text-scm-black group-hover:opacity-75 transition-opacity">
                    SCM
                </span>
                <span class="hidden sm:inline-block text-[11px] uppercase tracking-widest text-scm-gray-500 border-l border-scm-gray-300 pl-2">
                    Street Culture Market
                </span>
            </a>

            <a href="{{ route('products.index') }}" class="text-xs uppercase tracking-widest text-scm-gray-500 hover:text-scm-black transition-colors font-mono">
                &larr; Return to Store
            </a>
        </header>

        <!-- Centered Main Content -->
        <main class="flex-1 flex items-center justify-center p-4 sm:p-8">
            <div class="w-full max-w-md bg-white border border-scm-gray-200 p-8 sm:p-10 shadow-sm">
                {{ $slot }}
            </div>
        </main>

        <!-- Footer Note -->
        <footer class="border-t border-scm-gray-200 py-6 text-center text-xs uppercase tracking-widest text-scm-gray-400 font-mono">
            &copy; {{ date('Y') }} Street Culture Market. Monochromatic Streetwear Division.
        </footer>

    </div>
</body>
</html>
