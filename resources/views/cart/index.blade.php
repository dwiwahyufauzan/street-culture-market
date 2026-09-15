<x-app-layout>
  <div class="bg-scm-white border-b border-scm-gray-200 py-10 md:py-16">
    <div class="container-scm section-padding !py-0">
      <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-scm-black">
        Shopping Bag
      </h1>
      <p class="text-xs uppercase tracking-widest text-scm-gray-400 mt-2 font-mono">
        Review your selected garments before checkout
      </p>
    </div>
  </div>

  <div class="container-scm section-padding">
    @if(count($items) === 0)
      <div class="py-24 text-center max-w-md mx-auto space-y-4">
        <svg class="w-16 h-16 text-scm-gray-300 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
        </svg>
        <h3 class="text-lg font-bold uppercase tracking-wider text-scm-black">Your Bag is Empty</h3>
        <p class="text-xs text-scm-gray-500 uppercase tracking-widest">Discover our latest drop and limited capsule garments.</p>
        <div class="pt-4">
          <a href="{{ route('products.index') }}" class="btn-primary">
            Explore Catalog &rarr;
          </a>
        </div>
      </div>
    @else
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        <!-- Items Table -->
        <div class="lg:col-span-8 divide-y divide-scm-gray-200 border-t border-b border-scm-gray-200">
          @foreach($items as $item)
            <div class="py-6 flex flex-col sm:flex-row gap-6 items-start sm:items-center justify-between">
              <div class="flex gap-4">
                <div class="w-20 h-28 bg-scm-gray-100 shrink-0 overflow-hidden relative">
                  @if(!empty($item['image']) && file_exists(public_path('storage/' . $item['image'])))
                    <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                  @else
                    <div class="w-full h-full flex items-center justify-center text-[10px] text-scm-gray-400 font-mono uppercase text-center p-1">
                      SCM
                    </div>
                  @endif
                </div>
                <div class="space-y-1">
                  <h4 class="text-sm font-bold uppercase tracking-wider text-scm-black">{{ $item['name'] }}</h4>
                  <p class="text-xs uppercase tracking-wider text-scm-gray-500">Size: {{ $item['size'] }}</p>
                  <p class="text-xs font-semibold text-scm-black">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                </div>
              </div>

              <!-- Quantity update form -->
              <div class="flex items-center gap-6">
                <form action="{{ route('cart.update', $item['row_id']) }}" method="POST" class="flex items-center border border-scm-gray-300">
                  @csrf
                  @method('PATCH')
                  <button type="submit" name="quantity" value="{{ $item['quantity'] - 1 }}" class="px-3 py-1 text-xs text-scm-gray-600 hover:bg-scm-gray-100">-</button>
                  <span class="px-3 py-1 text-xs font-mono font-medium">{{ $item['quantity'] }}</span>
                  <button type="submit" name="quantity" value="{{ $item['quantity'] + 1 }}" class="px-3 py-1 text-xs text-scm-gray-600 hover:bg-scm-gray-100">+</button>
                </form>

                <span class="text-sm font-bold font-mono text-scm-black min-w-[100px] text-right">
                  Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                </span>

                <form action="{{ route('cart.remove', $item['row_id']) }}" method="POST">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="text-scm-gray-400 hover:text-red-600 p-1" title="Remove item">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </form>
              </div>
            </div>
          @endforeach
        </div>

        <!-- Summary Column -->
        <div class="lg:col-span-4 bg-scm-white border border-scm-gray-200 p-8 space-y-6 self-start">
          <h3 class="text-sm font-bold uppercase tracking-[0.2em] text-scm-black border-b border-scm-gray-200 pb-4">
            Order Summary
          </h3>

          <div class="space-y-3 text-xs uppercase tracking-wider">
            <div class="flex justify-between">
              <span class="text-scm-gray-500">Subtotal ({{ $count }} items)</span>
              <span class="font-bold text-scm-black">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-scm-gray-500">Estimated Shipping</span>
              <span class="text-scm-black">Calculated at Checkout</span>
            </div>
          </div>

          <div class="border-t border-scm-gray-200 pt-4 flex justify-between items-baseline">
            <span class="text-xs uppercase tracking-widest font-bold text-scm-black">Total</span>
            <span class="text-lg font-black text-scm-black font-mono">Rp {{ number_format($total, 0, ',', '.') }}</span>
          </div>

          <div class="pt-2">
            <a href="/checkout" class="btn-primary w-full text-center block text-xs tracking-[0.25em]">
              Proceed To Checkout &rarr;
            </a>
          </div>

          <p class="text-[11px] text-scm-gray-500 text-center font-light leading-relaxed">
            Taxes and domestic express couriers selected during payment step.
          </p>
        </div>
      </div>
    @endif
  </div>
</x-app-layout>
