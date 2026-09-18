<div
  x-data="searchModal()"
  @open-search.window="openModal()"
  @keydown.escape.window="closeModal()"
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
    @click="closeModal()"
    class="fixed inset-0 bg-black/85 backdrop-blur-sm z-50 flex items-start justify-center pt-16 md:pt-24 px-4 overflow-y-auto"
  >
    <!-- Modal Card -->
    <div
      @click.stop
      class="w-full max-w-2xl bg-white p-6 md:p-8 shadow-2xl relative border border-scm-gray-200 my-4"
    >
      <div class="flex items-center justify-between pb-4 border-b border-scm-gray-200">
        <span class="text-xs uppercase tracking-[0.25em] text-scm-gray-500 font-bold">Search Catalog</span>
        <button @click="closeModal()" class="text-scm-gray-400 hover:text-scm-black">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <form action="{{ route('search.index') }}" method="GET" class="mt-6">
        <div class="relative">
          <input
            x-ref="searchInput"
            type="text"
            name="q"
            x-model="query"
            @input.debounce.300ms="fetchSuggestions()"
            autocomplete="off"
            placeholder="TYPE TO SEARCH HOODIES, TEES, CARGO..."
            class="w-full border-b-2 border-scm-black py-4 pl-2 pr-24 text-sm md:text-base uppercase tracking-wider font-semibold focus:outline-none placeholder:text-scm-gray-300"
          >
          <button
            type="submit"
            class="absolute right-2 top-1/2 -translate-y-1/2 bg-scm-black text-white px-4 py-2 text-xs uppercase tracking-widest hover:bg-scm-gray-800 transition-colors"
          >
            Search
          </button>
        </div>
      </form>

      <!-- Live Autocomplete Suggestions Dropdown -->
      <div x-show="suggestions.length > 0" x-transition class="mt-4 border border-scm-gray-200 divide-y divide-scm-gray-100 bg-white">
        <div class="px-4 py-2 bg-scm-gray-50 text-[10px] font-mono uppercase tracking-widest text-scm-gray-500 flex justify-between">
          <span>Instant Matches</span>
          <span x-text="suggestions.length + ' Suggestions'"></span>
        </div>
        <template x-for="item in suggestions" :key="item.id">
          <a :href="'/products/' + item.slug" 
             class="flex items-center justify-between gap-4 px-4 py-3 hover:bg-scm-gray-50 transition-colors group">
            <div class="flex items-center gap-3">
              <div class="w-10 h-12 bg-scm-gray-100 border border-scm-gray-200 overflow-hidden flex-shrink-0">
                <template x-if="item.image">
                  <img :src="'/storage/' + item.image" :alt="item.name" class="w-full h-full object-cover grayscale contrast-125">
                </template>
                <template x-if="!item.image">
                  <div class="w-full h-full flex items-center justify-center text-[8px] font-mono text-scm-gray-400">SCM</div>
                </template>
              </div>
              <div>
                <p class="text-xs font-bold uppercase text-scm-black group-hover:text-scm-gray-600 transition-colors" x-text="item.name"></p>
                <p class="text-[10px] text-scm-gray-400 uppercase tracking-widest" x-text="item.category || 'Garment'"></p>
              </div>
            </div>
            <div class="text-right font-mono text-xs font-bold text-scm-black" x-text="item.formatted_price"></div>
          </a>
        </template>
        <a :href="'/search?q=' + encodeURIComponent(query)"
           class="block px-4 py-3 text-center text-xs uppercase tracking-widest font-bold text-scm-black bg-scm-gray-50 hover:bg-scm-black hover:text-white transition">
           View All Search Results for "<span x-text="query"></span>" &rarr;
        </a>
      </div>

      <!-- Popular Searches Suggestions -->
      <div x-show="suggestions.length === 0" class="mt-6 pt-4 border-t border-scm-gray-100">
        <p class="text-[11px] uppercase tracking-widest text-scm-gray-400 mb-2">Popular Searches:</p>
        <div class="flex flex-wrap gap-2">
          <a href="{{ route('search.index', ['q' => 'Hoodie']) }}" class="text-xs uppercase tracking-wider bg-scm-gray-100 hover:bg-scm-gray-200 px-2.5 py-1 text-scm-black transition">
            Hoodie
          </a>
          <a href="{{ route('search.index', ['q' => 'Acid Wash']) }}" class="text-xs uppercase tracking-wider bg-scm-gray-100 hover:bg-scm-gray-200 px-2.5 py-1 text-scm-black transition">
            Acid Wash
          </a>
          <a href="{{ route('search.index', ['q' => 'Cargo']) }}" class="text-xs uppercase tracking-wider bg-scm-gray-100 hover:bg-scm-gray-200 px-2.5 py-1 text-scm-black transition">
            Cargo
          </a>
          <a href="{{ route('search.index', ['q' => 'Bomber']) }}" class="text-xs uppercase tracking-wider bg-scm-gray-100 hover:bg-scm-gray-200 px-2.5 py-1 text-scm-black transition">
            Bomber
          </a>
          <a href="{{ route('search.index', ['q' => 'Vintage']) }}" class="text-xs uppercase tracking-wider bg-scm-gray-100 hover:bg-scm-gray-200 px-2.5 py-1 text-scm-black transition">
            Vintage
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function searchModal() {
  return {
    open: false,
    query: '',
    suggestions: [],

    openModal() {
      this.open = true;
      this.$nextTick(() => {
        if (this.$refs.searchInput) {
          this.$refs.searchInput.focus();
        }
      });
    },

    closeModal() {
      this.open = false;
      this.suggestions = [];
    },

    async fetchSuggestions() {
      const q = this.query.trim();
      if (q.length < 2) {
        this.suggestions = [];
        return;
      }

      try {
        const res = await fetch('/search/suggest?q=' + encodeURIComponent(q));
        if (res.ok) {
          this.suggestions = await res.json();
        }
      } catch (err) {
        this.suggestions = [];
      }
    }
  };
}
</script>
