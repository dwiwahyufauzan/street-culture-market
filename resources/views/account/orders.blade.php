<x-app-layout>
  <!-- Order Archive Header -->
  <div class="bg-scm-white border-b border-scm-gray-200 py-10 md:py-14">
    <div class="container-scm section-padding !py-0">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-xs uppercase tracking-[0.25em] text-scm-gray-400 font-semibold mb-2">
            <a href="{{ route('home') }}" class="hover:text-scm-black transition-colors">Home</a>
            <span>/</span>
            <a href="{{ route('account.index') }}" class="hover:text-scm-black transition-colors">Account</a>
            <span>/</span>
            <span class="text-scm-black font-bold">Orders</span>
          </div>
          <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tight text-scm-black">
            Order Archive
          </h1>
        </div>
        <p class="text-xs uppercase tracking-widest text-scm-gray-500 font-mono">
          {{ $orders->total() }} Total Dispatches
        </p>
      </div>
    </div>
  </div>

  <div class="container-scm section-padding">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12">

      <!-- Sidebar Navigation -->
      <aside class="md:col-span-4 lg:col-span-3 space-y-2">
        <div class="p-4 bg-scm-gray-50 border border-scm-gray-200 mb-6">
          <span class="text-[10px] uppercase tracking-[0.25em] text-scm-gray-400 font-bold block mb-1">Signed In As</span>
          <h3 class="text-sm font-bold uppercase tracking-wider text-scm-black truncate">{{ $user->name }}</h3>
          <p class="text-xs text-scm-gray-500 font-mono truncate">{{ $user->email }}</p>
        </div>

        <nav class="space-y-1 text-xs uppercase tracking-wider font-semibold">
          <a
            href="{{ route('account.index') }}"
            class="flex items-center justify-between px-4 py-3 border transition-colors bg-white text-scm-black border-scm-gray-200 hover:border-scm-black"
          >
            <span>Overview</span>
            <span>&rarr;</span>
          </a>

          <a
            href="{{ route('account.orders') }}"
            class="flex items-center justify-between px-4 py-3 border transition-colors bg-scm-black text-white border-scm-black"
          >
            <span>Order Archive</span>
            <span class="font-mono text-[11px]">{{ $orders->total() }}</span>
          </a>

          <a
            href="{{ route('wishlist.index') }}"
            class="flex items-center justify-between px-4 py-3 border transition-colors bg-white text-scm-black border-scm-gray-200 hover:border-scm-black"
          >
            <span>Saved Wishlist</span>
            <span>&rarr;</span>
          </a>

          <a
            href="{{ route('profile.edit') }}"
            class="flex items-center justify-between px-4 py-3 border transition-colors bg-white text-scm-black border-scm-gray-200 hover:border-scm-black"
          >
            <span>Profile &amp; Security</span>
            <span>&rarr;</span>
          </a>
        </nav>
      </aside>

      <!-- Orders Archive List -->
      <main class="md:col-span-8 lg:col-span-9 space-y-6">
        @if($orders->count() > 0)
          <div class="space-y-4">
            @foreach($orders as $order)
              <div class="border border-scm-gray-200 bg-white hover:border-scm-black transition-colors duration-200">
                <!-- Order Card Header -->
                <div class="p-5 sm:p-6 border-b border-scm-gray-100 bg-scm-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                  <div class="space-y-1">
                    <div class="flex items-center gap-3">
                      <span class="text-xs font-mono font-bold text-scm-black">
                        {{ $order->order_number }}
                      </span>
                      <span class="text-[10px] uppercase tracking-widest font-bold px-2 py-0.5 border {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-scm-gray-100 text-scm-black border-scm-gray-300' }}">
                        {{ $order->status }} &bull; {{ $order->payment_status }}
                      </span>
                    </div>
                    <p class="text-[11px] text-scm-gray-500 font-mono">
                      Placed on {{ $order->created_at->format('d M Y, H:i') }} WIB
                    </p>
                  </div>

                  <div class="text-left sm:text-right">
                    <span class="text-xs uppercase tracking-widest text-scm-gray-400 block">Total</span>
                    <span class="text-base font-black font-mono text-scm-black">
                      Rp {{ number_format($order->total, 0, ',', '.') }}
                    </span>
                  </div>
                </div>

                <!-- Order Items Previews -->
                <div class="p-5 sm:p-6 space-y-4">
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($order->items as $item)
                      @php
                        $primaryImg = $item->product?->primaryImage;
                        $hasImg = $primaryImg && $primaryImg->image_path && file_exists(public_path('storage/' . $primaryImg->image_path));
                      @endphp
                      <div class="flex items-center gap-3">
                        <div class="w-12 h-16 bg-scm-gray-100 shrink-0 overflow-hidden border border-scm-gray-200">
                          @if($hasImg)
                            <img src="{{ asset('storage/' . $primaryImg->image_path) }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                          @else
                            <div class="w-full h-full flex items-center justify-center text-[8px] text-scm-gray-400 font-mono uppercase">
                              SCM
                            </div>
                          @endif
                        </div>
                        <div>
                          <h4 class="text-xs font-bold uppercase tracking-wider text-scm-black line-clamp-1">
                            {{ $item->product_name }}
                          </h4>
                          <p class="text-[11px] text-scm-gray-500 uppercase tracking-widest mt-0.5">
                            Size: <span class="font-mono text-scm-black font-semibold">{{ $item->size ?? '-' }}</span> &bull; Qty: <span class="font-mono text-scm-black font-semibold">{{ $item->quantity }}</span>
                          </p>
                        </div>
                      </div>
                    @endforeach
                  </div>

                  <!-- Detail Action Link -->
                  <div class="pt-4 border-t border-scm-gray-100 flex justify-between items-center">
                    <span class="text-[11px] text-scm-gray-500 font-mono uppercase tracking-wider">
                      Courier: {{ $order->shipping_method ?? 'Domestic Courier' }}
                    </span>
                    <a
                      href="{{ route('account.orders.show', $order) }}"
                      class="text-xs uppercase tracking-widest font-bold text-scm-black hover:text-scm-gray-600 underline font-mono"
                    >
                      View Invoice &amp; Tracking &rarr;
                    </a>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

          <!-- Pagination -->
          <div class="mt-8">
            {{ $orders->links() }}
          </div>
        @else
          <div class="p-16 text-center border border-dashed border-scm-gray-300 bg-white space-y-4">
            <div class="w-16 h-16 mx-auto mb-2 border border-scm-gray-300 flex items-center justify-center text-scm-gray-400">
              <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
              </svg>
            </div>
            <h3 class="text-sm font-bold uppercase tracking-wider text-scm-black">No Orders Found</h3>
            <p class="text-xs text-scm-gray-500 uppercase tracking-widest max-w-sm mx-auto leading-relaxed">
              You have not placed any orders yet. Discover our limited heavyweight streetwear collections.
            </p>
            <div class="pt-2">
              <a href="{{ route('products.index') }}" class="btn-primary text-xs inline-block">
                Explore Catalog &rarr;
              </a>
            </div>
          </div>
        @endif
      </main>

    </div>
  </div>
</x-app-layout>
