<x-guest-layout>
    <div class="mb-6">
        <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-400 font-semibold block mb-1">
            Account Recovery
        </span>
        <h1 class="text-2xl font-black uppercase tracking-tight text-scm-black">
            Reset Password
        </h1>
        <p class="text-xs text-scm-gray-500 uppercase tracking-wider mt-2 leading-relaxed">
            Enter your registered email address and we will dispatch a secure password reset link.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6 p-3 bg-scm-gray-50 border border-scm-gray-200 text-xs uppercase tracking-wider text-scm-black" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1.5">
                Registered Email
            </label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                placeholder="name@example.com"
                class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs tracking-wider focus:border-scm-black focus:ring-0 placeholder:text-scm-gray-400"
            >
            @error('email')
                <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
            @enderror
        </div>

        <div class="pt-2">
            <button
                type="submit"
                class="btn-primary w-full py-3.5 text-xs tracking-[0.2em] font-bold text-center block"
            >
                Email Reset Link &rarr;
            </button>
        </div>

        <div class="pt-4 border-t border-scm-gray-200 text-center">
            <a href="{{ route('login') }}" class="text-xs uppercase tracking-wider text-scm-gray-500 hover:text-scm-black font-medium underline">
                &larr; Back to Sign In
            </a>
        </div>
    </form>
</x-guest-layout>
