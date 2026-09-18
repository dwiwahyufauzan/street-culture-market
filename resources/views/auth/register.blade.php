<x-guest-layout>
    <div class="mb-8">
        <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-400 font-semibold block mb-1">
            New Membership
        </span>
        <h1 class="text-2xl sm:text-3xl font-black uppercase tracking-tight text-scm-black">
            Create Account
        </h1>
        <p class="text-xs text-scm-gray-500 uppercase tracking-widest mt-1">
            Register to track exclusive drops, express checkout, and manage your wardrobe
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1.5">
                Full Name <span class="text-red-600">*</span>
            </label>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="e.g. Dwi Wahyu Fauzan"
                class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs tracking-wider focus:border-scm-black focus:ring-0 placeholder:text-scm-gray-400"
            >
            @error('name')
                <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1.5">
                Email Address <span class="text-red-600">*</span>
            </label>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                placeholder="name@example.com"
                class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs tracking-wider focus:border-scm-black focus:ring-0 placeholder:text-scm-gray-400"
            >
            @error('email')
                <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Phone (Optional) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="phone" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1.5">
                    Phone / WA <span class="text-scm-gray-400 font-normal">(Optional)</span>
                </label>
                <input
                    id="phone"
                    type="tel"
                    name="phone"
                    value="{{ old('phone') }}"
                    placeholder="08123456789"
                    class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs font-mono tracking-wider focus:border-scm-black focus:ring-0 placeholder:text-scm-gray-400"
                >
            </div>

            <div>
                <label for="city" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1.5">
                    City <span class="text-scm-gray-400 font-normal">(Optional)</span>
                </label>
                <input
                    id="city"
                    type="text"
                    name="city"
                    value="{{ old('city') }}"
                    placeholder="Jakarta / Bandung"
                    class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs uppercase tracking-wider focus:border-scm-black focus:ring-0 placeholder:normal-case placeholder:text-scm-gray-400"
                >
            </div>
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1.5">
                Password <span class="text-red-600">*</span>
            </label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="At least 8 characters"
                class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs tracking-wider focus:border-scm-black focus:ring-0 placeholder:text-scm-gray-400 font-mono"
            >
            @error('password')
                <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1.5">
                Confirm Password <span class="text-red-600">*</span>
            </label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Confirm password"
                class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs tracking-wider focus:border-scm-black focus:ring-0 placeholder:text-scm-gray-400 font-mono"
            >
            @error('password_confirmation')
                <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
            @enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-3">
            <button
                type="submit"
                class="btn-primary w-full py-3.5 text-xs tracking-[0.25em] font-bold text-center block"
            >
                Create Account &rarr;
            </button>
        </div>

        <!-- Sign In Link -->
        <div class="pt-4 border-t border-scm-gray-200 text-center">
            <p class="text-xs uppercase tracking-wider text-scm-gray-500">
                Already registered?
                <a href="{{ route('login') }}" class="text-scm-black font-bold underline hover:text-scm-gray-600 ml-1">
                    Sign In
                </a>
            </p>
        </div>
    </form>
</x-guest-layout>
