<x-app-layout>
  <div class="container-scm section-padding">
    <div class="max-w-3xl mx-auto space-y-10">

      <!-- Success Header Banner -->
      <div class="text-center space-y-3 pb-8 border-b border-scm-gray-200">
        <div class="w-16 h-16 bg-scm-black text-white flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
        </div>

        <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-400 font-semibold block">
          Order Dispatched Successfully
        </span>
        <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tight text-scm-black">
          Thank You For Your Order
        </h1>
        <p class="text-xs md:text-sm text-scm-gray-600 font-light max-w-md mx-auto leading-relaxed">
          We have registered your order. A confirmation email with tracking details will be sent to <span class="font-semibold text-scm-black">{{ $order->customer_email }}</span>.
        </p>
      </div>

      <!-- Order Reference Card -->
      <div class="bg-scm-gray-50 border border-scm-gray-200 p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <span class="text-[11px] uppercase tracking-widest text-scm-gray-500 font-semibold block mb-1">
            Order Reference
          </span>
          <div class="flex items-center gap-3">
            <span class="text-lg sm:text-xl font-mono font-black text-scm-black">
              {{ $order->order_number }}
            </span>
          </div>
          <span class="text-[11px] text-scm-gray-400 font-mono mt-1 block">
            Placed on {{ $order->created_at->format('d M Y, H:i') }} WIB
          </span>
        </div>

        <div class="flex flex-col sm:items-end gap-1.5">
          <span class="text-[11px] uppercase tracking-widest text-scm-gray-500 font-semibold">
            Status
          </span>
          <div class="flex items-center gap-2">
            <span class="inline-block bg-scm-black text-white text-[11px] uppercase tracking-widest font-bold px-3 py-1">
              {{ $order->status }} &bull; {{ $order->payment_status }}
            </span>
            @if($order->payment_status === 'unpaid' && $order->status !== 'cancelled')
              <a href="{{ route('checkout.payment', $order) }}" 
                 class="inline-block bg-neutral-800 hover:bg-black text-white text-[11px] uppercase tracking-widest font-bold px-3 py-1 transition">
                Pay Now &rarr;
              </a>
            @endif
          </div>
        </div>
      </div>

      <!-- Customer & Shipping Summary Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 p-6 sm:p-8 bg-white border border-scm-gray-200">
        <div>
          <h3 class="text-xs uppercase tracking-[0.2em] font-bold text-scm-black mb-3 border-b border-scm-gray-100 pb-2">
            Delivery Destination
          </h3>
          <p class="text-xs font-bold uppercase text-scm-black">{{ $order->customer_name }}</p>
          <p class="text-xs text-scm-gray-600 leading-relaxed mt-1 font-light">{{ $order->shipping_address }}</p>
          <p class="text-xs text-scm-gray-600 font-mono mt-0.5">
            {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal }}
          </p>
          <p class="text-xs text-scm-gray-600 font-mono mt-1">Phone: {{ $order->customer_phone }}</p>
        </div>

        <div>
          <h3 class="text-xs uppercase tracking-[0.2em] font-bold text-scm-black mb-3 border-b border-scm-gray-100 pb-2">
            Logistics &amp; Courier
          </h3>
          <div class="space-y-1.5 text-xs text-scm-gray-600">
            <div class="flex justify-between">
              <span class="uppercase tracking-wider">Method:</span>
              <span class="font-bold text-scm-black uppercase font-mono">{{ $order->shipping_method }}</span>
            </div>
            <div class="flex justify-between">
              <span class="uppercase tracking-wider">Estimated Delivery:</span>
              <span class="text-scm-black font-medium">2-3 Business Days</span>
            </div>
            @if($order->notes)
              <div class="pt-2 border-t border-scm-gray-100">
                <span class="uppercase tracking-wider block text-[11px] text-scm-gray-400">Order Notes:</span>
                <p class="text-[11px] italic text-scm-gray-600">{{ $order->notes }}</p>
              </div>
            @endif
          </div>
        </div>
      </div>

      <!-- Itemized Garments Receipt -->
      <div class="border border-scm-gray-200 bg-white">
        <div class="px-6 py-4 border-b border-scm-gray-200 bg-scm-white flex justify-between items-center">
          <h3 class="text-xs uppercase tracking-[0.2em] font-bold text-scm-black">
            Garments Summary ({{ $order->items->sum('quantity') }} Items)
          </h3>
        </div>

        <div class="divide-y divide-scm-gray-100">
          @foreach($order->items as $item)
            @php
              $primaryImg = $item->product?->primaryImage;
              $hasImg = $primaryImg && $primaryImg->image_path && file_exists(public_path('storage/' . $primaryImg->image_path));
            @endphp
            <div class="p-6 flex items-center justify-between gap-4">
              <div class="flex items-center gap-4">
                <div class="w-16 h-20 bg-scm-gray-100 overflow-hidden shrink-0 border border-scm-gray-200">
                  @if($hasImg)
                    <img src="{{ asset('storage/' . $primaryImg->image_path) }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                  @else
                    <div class="w-full h-full flex items-center justify-center text-[10px] text-scm-gray-400 font-mono">
                      SCM
                    </div>
                  @endif
                </div>

                <div>
                  <h4 class="text-xs font-bold uppercase tracking-wider text-scm-black">
                    {{ $item->product_name }}
                  </h4>
                  <p class="text-[11px] text-scm-gray-500 uppercase tracking-wider mt-0.5">
                    Size: <span class="font-mono text-scm-black font-semibold">{{ $item->size ?? '-' }}</span> &bull; Qty: <span class="font-mono text-scm-black font-semibold">{{ $item->quantity }}</span>
                  </p>
                  <p class="text-[11px] text-scm-gray-400 font-mono mt-0.5">
                    Unit: Rp {{ number_format($item->price, 0, ',', '.') }}
                  </p>
                </div>
              </div>

              <span class="text-xs font-bold font-mono text-scm-black">
                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
              </span>
            </div>
          @endforeach
        </div>

        <!-- Financial Totals Table -->
        <div class="bg-scm-gray-50 p-6 border-t border-scm-gray-200 space-y-2 text-xs uppercase tracking-wider">
          <div class="flex justify-between text-scm-gray-600">
            <span>Subtotal</span>
            <span class="font-mono font-semibold text-scm-black">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
          </div>
          <div class="flex justify-between text-scm-gray-600">
            <span>Shipping Cost</span>
            <span class="font-mono font-semibold text-scm-black">
              @if($order->shipping_cost > 0)
                Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
              @else
                Free
              @endif
            </span>
          </div>
          @if($order->discount_amount > 0)
            <div class="flex justify-between text-red-600">
              <span>Promotion Discount</span>
              <span class="font-mono font-semibold">- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
            </div>
          @endif
          <div class="border-t border-scm-gray-200 pt-3 flex justify-between items-baseline font-black text-scm-black text-sm">
            <span class="tracking-[0.2em]">Grand Total</span>
            <span class="text-lg font-mono">Rp {{ number_format($order->total, 0, ',', '.') }}</span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4">
        <a
          href="{{ route('products.index') }}"
          class="btn-primary w-full sm:w-auto text-xs tracking-[0.2em] text-center"
        >
          &larr; Continue Exploring
        </a>

        @auth
          <a
            href="{{ route('dashboard') }}"
            class="text-xs uppercase tracking-widest font-semibold text-scm-black hover:text-scm-gray-600 underline font-mono"
          >
            View In Customer Dashboard &rarr;
          </a>
        @endauth
      </div>

    </div>
  </div>
</x-app-layout>
