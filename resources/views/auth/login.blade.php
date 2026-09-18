<x-guest-layout>
    <div class="mb-8">
        <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-400 font-semibold block mb-1">
            Access Account
        </span>
        <h1 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-scm-black">
            Sign In
        </h1>
        <p class="text-xs text-scm-gray-500 uppercase tracking-widest mt-1">
            Enter your credentials to manage orders and saved garments
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6 p-3 bg-scm-gray-50 border border-scm-gray-200 text-xs uppercase tracking-wider text-scm-black" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1.5">
                Email Address
            </label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="name@example.com"
                class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs tracking-wider focus:border-scm-black focus:ring-0 placeholder:text-scm-gray-400"
            >
            @error('email')
                <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-xs uppercase tracking-wider font-semibold text-scm-black">
                    Password
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[11px] uppercase tracking-wider text-scm-gray-500 hover:text-scm-black underline font-mono">
                        Forgot Password?
                    </a>
                @endif
            </div>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs tracking-wider focus:border-scm-black focus:ring-0 placeholder:text-scm-gray-400 font-mono"
            >
            @error('password')
                <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="rounded-none border-scm-gray-300 text-scm-black focus:ring-scm-black"
                >
                <span class="text-xs uppercase tracking-wider text-scm-gray-600 font-medium">Remember Session</span>
            </label>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button
                type="submit"
                class="btn-primary w-full py-3.5 text-xs tracking-[0.25em] font-bold text-center block"
            >
                Sign In &rarr;
            </button>
        </div>

        <!-- Registration Link -->
        <div class="pt-4 border-t border-scm-gray-200 text-center">
            <p class="text-xs uppercase tracking-wider text-scm-gray-500">
                New to Street Culture Market?
                <a href="{{ route('register') }}" class="text-scm-black font-bold underline hover:text-scm-gray-600 ml-1">
                    Create Account
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
