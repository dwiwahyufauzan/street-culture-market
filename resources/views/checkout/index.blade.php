<x-app-layout>
  <!-- Checkout Banner -->
  <div class="bg-scm-white border-b border-scm-gray-200 py-10 md:py-14">
    <div class="container-scm section-padding !py-0">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <!-- Steps Indicator -->
          <div class="flex items-center gap-3 text-xs uppercase tracking-[0.25em] text-scm-gray-400 font-semibold mb-2">
            <a href="{{ route('cart.index') }}" class="hover:text-scm-black transition-colors">1. Shopping Bag</a>
            <span>&rarr;</span>
            <span class="text-scm-black font-bold">2. Shipping &amp; Review</span>
            <span>&rarr;</span>
            <span class="text-scm-gray-300">3. Payment</span>
          </div>
          <h1 class="text-3xl md:text-5xl font-black uppercase tracking-tight text-scm-black">
            Order Checkout
          </h1>
        </div>
        <p class="text-xs uppercase tracking-widest text-scm-gray-500 font-mono">
          {{ $cart['count'] }} Garments Selected
        </p>
      </div>
    </div>
  </div>

  <div
    class="container-scm section-padding"
    x-data="{
      subtotal: {{ (float) $cart['total'] }},
      isFreeShipping: {{ $isFreeShipping ? 'true' : 'false' }},
      selectedCourier: 'JNE_REG',
      couriers: {
        'JNE_REG': { name: 'JNE Express (Regular)', cost: 25000 },
        'SICEPAT_BEST': { name: 'SiCepat BEST (Next Day)', cost: 35000 },
        'JNT_STD': { name: 'J&T Express (Standard)', cost: 22000 }
      },
      get shippingCost() {
        if (this.isFreeShipping) return 0;
        return this.couriers[this.selectedCourier]?.cost || 25000;
      },
      get grandTotal() {
        return this.subtotal + this.shippingCost;
      },
      formatCurrency(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
      }
    }"
  >
    <form action="{{ route('checkout.store') }}" method="POST">
      @csrf

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14">

        <!-- Left Form Column: Customer & Shipping Details -->
        <div class="lg:col-span-7 space-y-10">

          <!-- Section 1: Customer Contact -->
          <div class="space-y-4">
            <div class="flex items-center justify-between border-b border-scm-gray-200 pb-3">
              <h2 class="text-xs uppercase tracking-[0.25em] font-bold text-scm-black">
                1. Customer Contact
              </h2>
              @guest
                <span class="text-[11px] text-scm-gray-500 uppercase tracking-wider">
                  Already registered? <a href="{{ route('login') }}" class="underline text-scm-black font-semibold">Login</a>
                </span>
              @endguest
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label for="customer_name" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1">
                  Full Name <span class="text-red-600">*</span>
                </label>
                <input
                  type="text"
                  id="customer_name"
                  name="customer_name"
                  value="{{ old('customer_name', $user?->name) }}"
                  required
                  placeholder="e.g. Dwi Wahyu Fauzan"
                  class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs uppercase tracking-wider focus:border-scm-black focus:ring-0 placeholder:normal-case placeholder:text-scm-gray-400"
                >
                @error('customer_name')
                  <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label for="customer_email" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1">
                  Email Address <span class="text-red-600">*</span>
                </label>
                <input
                  type="email"
                  id="customer_email"
                  name="customer_email"
                  value="{{ old('customer_email', $user?->email) }}"
                  required
                  placeholder="name@example.com"
                  class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs tracking-wider focus:border-scm-black focus:ring-0 placeholder:text-scm-gray-400"
                >
                @error('customer_email')
                  <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label for="customer_phone" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1">
                  WhatsApp / Phone <span class="text-red-600">*</span>
                </label>
                <input
                  type="tel"
                  id="customer_phone"
                  name="customer_phone"
                  value="{{ old('customer_phone', $user?->phone) }}"
                  required
                  placeholder="081234567890"
                  class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs tracking-wider focus:border-scm-black focus:ring-0 placeholder:text-scm-gray-400 font-mono"
                >
                @error('customer_phone')
                  <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>

          <!-- Section 2: Delivery Destination -->
          <div class="space-y-4">
            <h2 class="text-xs uppercase tracking-[0.25em] font-bold text-scm-black border-b border-scm-gray-200 pb-3">
              2. Shipping Address
            </h2>

            <div class="space-y-4">
              <div>
                <label for="shipping_address" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1">
                  Street Address / House / Unit <span class="text-red-600">*</span>
                </label>
                <textarea
                  id="shipping_address"
                  name="shipping_address"
                  rows="3"
                  required
                  placeholder="Street name, building number, district/sub-district"
                  class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs leading-relaxed focus:border-scm-black focus:ring-0 placeholder:text-scm-gray-400"
                >{{ old('shipping_address', $user?->address) }}</textarea>
                @error('shipping_address')
                  <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
                @enderror
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                  <label for="shipping_city" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1">
                    City / Regency <span class="text-red-600">*</span>
                  </label>
                  <input
                    type="text"
                    id="shipping_city"
                    name="shipping_city"
                    value="{{ old('shipping_city', $user?->city ?? 'Jakarta Selatan') }}"
                    required
                    class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs uppercase tracking-wider focus:border-scm-black focus:ring-0"
                  >
                  @error('shipping_city')
                    <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
                  @enderror
                </div>

                <div>
                  <label for="shipping_province" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1">
                    Province <span class="text-red-600">*</span>
                  </label>
                  <input
                    type="text"
                    id="shipping_province"
                    name="shipping_province"
                    value="{{ old('shipping_province', $user?->province ?? 'DKI Jakarta') }}"
                    required
                    class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs uppercase tracking-wider focus:border-scm-black focus:ring-0"
                  >
                  @error('shipping_province')
                    <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
                  @enderror
                </div>

                <div>
                  <label for="shipping_postal" class="block text-xs uppercase tracking-wider font-semibold text-scm-black mb-1">
                    Postal Code <span class="text-red-600">*</span>
                  </label>
                  <input
                    type="text"
                    id="shipping_postal"
                    name="shipping_postal"
                    value="{{ old('shipping_postal', $user?->postal_code ?? '12430') }}"
                    required
                    maxlength="10"
                    class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs font-mono tracking-wider focus:border-scm-black focus:ring-0"
                  >
                  @error('shipping_postal')
                    <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
                  @enderror
                </div>
              </div>
            </div>
          </div>

          <!-- Section 3: Shipping Courier Method -->
          <div class="space-y-4">
            <div class="flex items-center justify-between border-b border-scm-gray-200 pb-3">
              <h2 class="text-xs uppercase tracking-[0.25em] font-bold text-scm-black">
                3. Courier &amp; Delivery Option
              </h2>
              <template x-if="isFreeShipping">
                <span class="text-xs uppercase tracking-wider font-bold text-emerald-700 bg-emerald-50 border border-emerald-300 px-2 py-0.5">
                  &bull; Free Delivery Qualified
                </span>
              </template>
            </div>

            <div class="space-y-3">
              @foreach($couriers as $courier)
                <label
                  class="flex items-center justify-between p-4 border cursor-pointer transition-all duration-200"
                  :class="selectedCourier === '{{ $courier['code'] }}' ? 'border-scm-black bg-scm-gray-50' : 'border-scm-gray-200 hover:border-scm-gray-400 bg-white'"
                >
                  <div class="flex items-center gap-3">
                    <input
                      type="radio"
                      name="shipping_method"
                      value="{{ $courier['code'] }}"
                      x-model="selectedCourier"
                      class="text-scm-black focus:ring-scm-black border-scm-gray-300"
                    >
                    <div>
                      <span class="text-xs uppercase tracking-wider font-bold text-scm-black block">
                        {{ $courier['name'] }}
                      </span>
                      <span class="text-[11px] text-scm-gray-500 uppercase tracking-widest block font-mono mt-0.5">
                        Est: {{ $courier['estimate'] }}
                      </span>
                    </div>
                  </div>

                  <div>
                    <template x-if="isFreeShipping">
                      <div class="text-right">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">Free</span>
                        <span class="text-[11px] text-scm-gray-400 line-through block font-mono">
                          Rp {{ number_format($courier['cost'], 0, ',', '.') }}
                        </span>
                      </div>
                    </template>
                    <template x-if="!isFreeShipping">
                      <span class="text-xs font-bold font-mono text-scm-black">
                        Rp {{ number_format($courier['cost'], 0, ',', '.') }}
                      </span>
                    </template>
                  </div>
                </label>
              @endforeach
            </div>
            @error('shipping_method')
              <p class="text-[11px] text-red-600 mt-1 uppercase tracking-wider font-medium">{{ $message }}</p>
            @enderror
          </div>

          <!-- Section 4: Special Instructions -->
          <div class="space-y-4">
            <h2 class="text-xs uppercase tracking-[0.25em] font-bold text-scm-black border-b border-scm-gray-200 pb-3">
              4. Order Notes (Optional)
            </h2>
            <div>
              <textarea
                name="notes"
                rows="2"
                placeholder="Notes about your order (e.g. Leave package at security post / buzzer)"
                class="w-full bg-white border border-scm-gray-300 px-4 py-3 text-xs leading-relaxed focus:border-scm-black focus:ring-0 placeholder:text-scm-gray-400"
              >{{ old('notes') }}</textarea>
            </div>
          </div>

        </div>

        <!-- Right Column: Order Summary & Placement -->
        <div class="lg:col-span-5">
          <div class="bg-scm-white border border-scm-gray-200 p-6 md:p-8 space-y-6 sticky top-28">

            <div class="flex items-center justify-between border-b border-scm-gray-200 pb-4">
              <h2 class="text-xs uppercase tracking-[0.25em] font-black text-scm-black">
                Garments Overview
              </h2>
              <a href="{{ route('cart.index') }}" class="text-[11px] uppercase tracking-wider text-scm-gray-500 hover:text-scm-black underline font-mono">
                Edit Bag
              </a>
            </div>

            <!-- Items List -->
            <div class="divide-y divide-scm-gray-100 max-h-72 overflow-y-auto pr-1">
              @foreach($cart['items'] as $item)
                <div class="py-3 flex gap-3.5 items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-14 h-16 bg-scm-gray-100 shrink-0 overflow-hidden relative border border-scm-gray-200">
                      @if(!empty($item['image']) && file_exists(public_path('storage/' . $item['image'])))
                        <img src="{{ asset('storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                      @else
                        <div class="w-full h-full flex items-center justify-center text-[9px] text-scm-gray-400 font-mono uppercase">
                          SCM
                        </div>
                      @endif
                    </div>
                    <div>
                      <h3 class="text-xs font-bold uppercase tracking-wider text-scm-black line-clamp-1">
                        {{ $item['name'] }}
                      </h3>
                      <p class="text-[11px] text-scm-gray-500 uppercase tracking-widest mt-0.5">
                        Size: <span class="font-mono text-scm-black font-semibold">{{ $item['size'] }}</span> &bull; Qty: <span class="font-mono text-scm-black font-semibold">{{ $item['quantity'] }}</span>
                      </p>
                    </div>
                  </div>

                  <span class="text-xs font-mono font-bold text-scm-black text-right shrink-0">
                    Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                  </span>
                </div>
              @endforeach
            </div>

            <!-- Cost Breakdown -->
            <div class="border-t border-scm-gray-200 pt-5 space-y-3 text-xs uppercase tracking-wider">
              <div class="flex justify-between">
                <span class="text-scm-gray-500">Subtotal ({{ $cart['count'] }} Items)</span>
                <span class="font-mono font-semibold text-scm-black">
                  Rp {{ number_format($cart['total'], 0, ',', '.') }}
                </span>
              </div>

              <div class="flex justify-between items-center">
                <span class="text-scm-gray-500">Express Delivery</span>
                <span class="font-mono font-semibold text-scm-black" x-text="formatCurrency(shippingCost)"></span>
              </div>

              <template x-if="isFreeShipping">
                <div class="p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] leading-relaxed uppercase tracking-wider font-medium">
                  Free Shipping Applied (Subtotal &gt; Rp 1.000.000)
                </div>
              </template>
            </div>

            <!-- Grand Total -->
            <div class="border-t-2 border-scm-black pt-4 flex justify-between items-baseline">
              <span class="text-xs uppercase tracking-[0.25em] font-black text-scm-black">Estimated Total</span>
              <span class="text-xl font-black text-scm-black font-mono" x-text="formatCurrency(grandTotal)"></span>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
              <button
                type="submit"
                class="btn-primary w-full py-4 text-xs tracking-[0.25em] font-bold text-center block"
              >
                Place Order &amp; Proceed &rarr;
              </button>
            </div>

            <!-- Security & Guarantee Footnotes -->
            <div class="pt-4 border-t border-scm-gray-200 space-y-2 text-[11px] text-scm-gray-500 uppercase tracking-widest font-light">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-scm-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span>Encrypted 256-Bit SSL Checkout</span>
              </div>
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-scm-black shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>100% Street Culture Market Authenticity Guarantee</span>
              </div>
            </div>

          </div>
        </div>

      </div>
    </form>
  </div>
</x-app-layout>
