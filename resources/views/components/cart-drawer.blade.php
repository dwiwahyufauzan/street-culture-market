<div
  x-data="{
    open: false,
    items: [],
    count: 0,
    total: 0,
    loading: false,

    async fetchCart() {
      try {
        const res = await fetch('{{ route('cart.count') }}');
        const data = await res.json();
        this.items = data.items || [];
        this.count = data.count || 0;
        this.total = data.total || 0;
      } catch (e) {
        console.error('Failed to load cart', e);
      }
    },

    async updateQty(rowId, qty) {
      this.loading = true;
      try {
        const res = await fetch(`/cart/${rowId}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json'
          },
          body: JSON.stringify({ quantity: qty })
        });
        const data = await res.json();
        this.items = data.items;
        this.count = data.count;
        this.total = data.total;
        window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
      } finally {
        this.loading = false;
      }
    },

    async removeItem(rowId) {
      this.loading = true;
      try {
        const res = await fetch(`/cart/${rowId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json'
          }
        });
        const data = await res.json();
        this.items = data.items;
        this.count = data.count;
        this.total = data.total;
        window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
      } finally {
        this.loading = false;
      }
    }
  }"
  @open-cart.window="open = true; fetchCart()"
  @cart-updated.window="fetchCart()"
  x-cloak
>
  <!-- Backdrop -->
  <div
    x-show="open"
    x-transition:enter="transition-opacity ease-linear duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-linear duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @click="open = false"
    class="fixed inset-0 bg-black/60 backdrop-blur-xs z-40"
  ></div>

  <!-- Drawer Slide -->
  <div
    x-show="open"
    x-transition:enter="transition ease-in-out duration-300 transform"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in-out duration-300 transform"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full"
    class="fixed inset-y-0 right-0 max-w-full w-full sm:w-[420px] bg-white shadow-2xl z-50 flex flex-col"
  >
    <!-- Header -->
    <div class="px-6 py-5 border-b border-scm-gray-200 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-scm-black">
          Shopping Bag
        </h3>
        <span class="text-xs text-scm-gray-500 font-medium" x-text="`(${count})`"></span>
      </div>
      <button
        @click="open = false"
        type="button"
        class="text-scm-gray-500 hover:text-scm-black p-1 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Items List -->
    <div class="flex-1 overflow-y-auto p-6 divide-y divide-scm-gray-100">
      <template x-if="items.length === 0">
        <div class="h-full flex flex-col items-center justify-center text-center py-12">
          <svg class="w-12 h-12 text-scm-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
          </svg>
          <p class="text-xs uppercase tracking-widest text-scm-gray-500 mb-6">Your bag is currently empty</p>
          <a
            href="{{ route('products.index') }}"
            @click="open = false"
            class="btn-primary text-xs"
          >
            Explore Catalog
          </a>
        </div>
      </template>

      <template x-for="item in items" :key="item.row_id">
        <div class="py-4 flex gap-4">
          <!-- Thumbnail -->
          <div class="w-20 h-24 bg-scm-gray-100 shrink-0 overflow-hidden relative">
            <template x-if="item.image">
              <img :src="`/storage/${item.image}`" :alt="item.name" class="w-full h-full object-cover">
            </template>
            <template x-if="!item.image">
              <div class="w-full h-full flex items-center justify-center text-[10px] text-scm-gray-400 font-mono uppercase text-center p-1">
                SCM
              </div>
            </template>
          </div>

          <!-- Detail -->
          <div class="flex-1 flex flex-col justify-between">
            <div>
              <h4 class="text-xs font-semibold uppercase tracking-wider text-scm-black line-clamp-1" x-text="item.name"></h4>
              <p class="text-[11px] text-scm-gray-500 uppercase mt-0.5" x-text="`Size: ${item.size}`"></p>
              <p class="text-xs font-bold text-scm-black mt-1" x-text="`Rp ${Number(item.price).toLocaleString('id-ID')}`"></p>
            </div>

            <!-- Stepper & Remove -->
            <div class="flex items-center justify-between mt-3">
              <div class="flex items-center border border-scm-gray-300">
                <button
                  type="button"
                  @click="updateQty(item.row_id, item.quantity - 1)"
                  class="px-2 py-0.5 text-xs text-scm-gray-600 hover:bg-scm-gray-100"
                >-</button>
                <span class="px-2.5 py-0.5 text-xs font-mono font-medium" x-text="item.quantity"></span>
                <button
                  type="button"
                  @click="updateQty(item.row_id, item.quantity + 1)"
                  class="px-2 py-0.5 text-xs text-scm-gray-600 hover:bg-scm-gray-100"
                >+</button>
              </div>

              <button
                type="button"
                @click="removeItem(item.row_id)"
                class="text-[11px] uppercase tracking-wider text-scm-gray-400 hover:text-red-600 transition-colors underline"
              >
                Remove
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Footer / Checkout Button -->
    <template x-if="items.length > 0">
      <div class="p-6 border-t border-scm-gray-200 bg-scm-white space-y-4">
        <div class="flex items-center justify-between text-sm">
          <span class="uppercase tracking-widest text-xs text-scm-gray-600 font-medium">Estimated Subtotal</span>
          <span class="font-bold text-scm-black" x-text="`Rp ${Number(total).toLocaleString('id-ID')}`"></span>
        </div>
        <p class="text-[11px] text-scm-gray-500 leading-tight">
          Shipping and promotion discounts calculated at checkout.
        </p>
        <a
          href="{{ route('cart.index') }}"
          class="btn-primary w-full text-center block text-xs"
        >
          Proceed to Bag & Checkout
        </a>
      </div>
    </template>
  </div>
</div>
