<div
  x-data="{ open: false, query: '' }"
  @open-search.window="open = true; $nextTick(() => $refs.searchInput.focus())"
  @keydown.escape.window="open = false"
  x-cloak
>
  <!-- Backdrop -->
  <div
    x-show="open"
    x-transition:enter="transition-opacity ease-linear duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="open = false"
    class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-start justify-center pt-20 px-4"
  >
    <!-- Modal Card -->
    <div
      @click.stop
      class="w-full max-w-2xl bg-white p-6 shadow-2xl relative border border-scm-gray-200"
    >
      <div class="flex items-center justify-between pb-4 border-b border-scm-gray-200">
        <span class="text-xs uppercase tracking-[0.25em] text-scm-gray-500 font-bold">Search Catalog</span>
        <button @click="open = false" class="text-scm-gray-400 hover:text-scm-black">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <form action="{{ route('products.index') }}" method="GET" class="mt-6">
        <div class="relative">
          <input
            x-ref="searchInput"
            type="text"
            name="q"
            x-model="query"
            placeholder="TYPE TO SEARCH HOODIES, TEES, CARGO..."
            class="w-full border-b-2 border-scm-black py-4 px-2 text-base md:text-lg uppercase tracking-wider font-semibold focus:outline-none placeholder:text-scm-gray-300"
          >
          <button
            type="submit"
            class="absolute right-2 top-1/2 -translate-y-1/2 bg-scm-black text-white px-4 py-2 text-xs uppercase tracking-widest hover:bg-scm-gray-800 transition-colors"
          >
            Search
          </button>
        </div>
      </form>

      <div class="mt-6 pt-4 border-t border-scm-gray-100">
        <p class="text-[11px] uppercase tracking-widest text-scm-gray-400 mb-2">Popular Searches:</p>
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('products.index', ['q' => 'Hoodie']) }}" class="text-xs uppercase tracking-wider bg-scm-gray-100 hover:bg-scm-gray-200 px-2.5 py-1 text-scm-black">
            Hoodie
          </a>
          <a href="{{ route('products.index', ['q' => 'Acid Wash']) }}" class="text-xs uppercase tracking-wider bg-scm-gray-100 hover:bg-scm-gray-200 px-2.5 py-1 text-scm-black">
            Acid Wash
          </a>
          <a href="{{ route('products.index', ['q' => 'Cargo']) }}" class="text-xs uppercase tracking-wider bg-scm-gray-100 hover:bg-scm-gray-200 px-2.5 py-1 text-scm-black">
            Cargo
          </a>
          <a href="{{ route('products.index', ['q' => 'Bomber']) }}" class="text-xs uppercase tracking-wider bg-scm-gray-100 hover:bg-scm-gray-200 px-2.5 py-1 text-scm-black">
            Bomber
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
