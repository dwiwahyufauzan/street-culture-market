<x-app-layout>
  <div class="container-scm section-padding">
    <div class="max-w-3xl mx-auto space-y-10">

      <!-- Breadcrumbs Progress Indicator -->
      <nav class="flex items-center justify-center space-x-2 text-[11px] font-mono tracking-widest uppercase text-scm-gray-400">
        <span class="text-scm-gray-400">01. Bag</span>
        <span>&rarr;</span>
        <span class="text-scm-gray-400">02. Shipping</span>
        <span>&rarr;</span>
        <span class="text-scm-black font-bold border-b border-scm-black pb-0.5">03. Payment</span>
        <span>&rarr;</span>
        <span class="text-scm-gray-400">04. Confirmation</span>
      </nav>

      <!-- Payment Header Section -->
      <div class="text-center space-y-3 pb-8 border-b border-scm-gray-200">
        <span class="text-xs uppercase tracking-[0.3em] text-scm-gray-400 font-semibold block">
          Secure Transaction Portal
        </span>
        <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tight text-scm-black">
          Complete Your Payment
        </h1>
        <p class="text-xs md:text-sm text-scm-gray-600 font-light max-w-md mx-auto leading-relaxed">
          Order reference registered. Please finalize your transaction via our integrated Midtrans Payment Gateway.
        </p>
      </div>

      <!-- Payment Notification Alerts (Dynamic via JS) -->
      <div id="payment-alert" class="hidden p-4 text-xs font-mono tracking-wide border"></div>

      <!-- Order Reference & Total Card -->
      <div class="bg-scm-gray-50 border border-scm-gray-200 p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div>
          <span class="text-[11px] uppercase tracking-widest text-scm-gray-500 font-semibold block mb-1">
            Order Reference
          </span>
          <span class="text-xl sm:text-2xl font-mono font-black text-scm-black block">
            {{ $order->order_number }}
          </span>
          <span class="text-[11px] text-scm-gray-400 font-mono mt-1 block">
            Issued on {{ $order->created_at->format('d M Y, H:i') }} WIB
          </span>
        </div>

        <div class="sm:text-right">
          <span class="text-[11px] uppercase tracking-widest text-scm-gray-500 font-semibold block mb-1">
            Amount Due
          </span>
          <span class="text-2xl sm:text-3xl font-mono font-black text-scm-black">
            Rp {{ number_format($order->total, 0, ',', '.') }}
          </span>
          <span class="text-[11px] text-scm-gray-500 block mt-0.5 uppercase tracking-wider font-semibold">
            Status: <span class="text-scm-black font-bold uppercase">{{ $order->payment_status }}</span>
          </span>
        </div>
      </div>

      <!-- Garment Items & Shipping Summary Grid -->
      <div class="border border-scm-gray-200 bg-white divide-y divide-scm-gray-100">
        <!-- Section Title -->
        <div class="p-6 bg-scm-gray-50 flex items-center justify-between">
          <h2 class="text-xs uppercase tracking-[0.2em] font-bold text-scm-black">
            Purchased Garments ({{ $order->items->sum('quantity') }})
          </h2>
          <span class="text-[11px] font-mono text-scm-gray-500 uppercase tracking-widest">
            Subtotal: Rp {{ number_format($order->subtotal, 0, ',', '.') }}
          </span>
        </div>

        <!-- Items List -->
        <div class="p-6 space-y-4">
          @foreach($order->items as $item)
            <div class="flex items-center justify-between gap-4 py-2">
              <div class="flex items-center gap-4">
                <div class="w-14 h-16 bg-scm-gray-100 border border-scm-gray-200 overflow-hidden flex-shrink-0">
                  @if($item->product && $item->product->primaryImage)
                    <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" 
                         alt="{{ $item->product_name }}" 
                         class="w-full h-full object-cover grayscale contrast-125">
                  @else
                    <div class="w-full h-full flex items-center justify-center text-[9px] font-mono text-scm-gray-400">
                      SCM
                    </div>
                  @endif
                </div>

                <div>
                  <h4 class="text-xs font-bold uppercase text-scm-black leading-snug">
                    {{ $item->product_name }}
                  </h4>
                  <div class="flex items-center gap-2 mt-1 text-[11px] font-mono text-scm-gray-500">
                    <span class="bg-scm-gray-100 px-1.5 py-0.5 border border-scm-gray-200 font-bold text-scm-black">
                      SIZE {{ $item->size }}
                    </span>
                    <span>&times; {{ $item->quantity }}</span>
                  </div>
                </div>
              </div>

              <div class="text-right font-mono text-xs font-bold text-scm-black">
                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
              </div>
            </div>
          @endforeach
        </div>

        <!-- Shipping & Fees Breakdown -->
        <div class="p-6 bg-scm-gray-50/50 space-y-2 text-xs font-mono">
          <div class="flex justify-between text-scm-gray-600">
            <span>Shipping Cost ({{ $order->shipping_method ?? 'Standard' }})</span>
            <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
          </div>

          @if($order->discount_amount > 0)
            <div class="flex justify-between text-scm-black font-semibold">
              <span>Voucher / Discount</span>
              <span>-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
            </div>
          @endif

          <div class="flex justify-between pt-3 border-t border-scm-gray-200 text-sm font-bold text-scm-black">
            <span class="uppercase tracking-wider">Final Total</span>
            <span>Rp {{ number_format($order->total, 0, ',', '.') }}</span>
          </div>
        </div>
      </div>

      <!-- Shipping Information Card -->
      <div class="border border-scm-gray-200 p-6 bg-white space-y-2">
        <h3 class="text-xs uppercase tracking-[0.2em] font-bold text-scm-black mb-3 border-b border-scm-gray-100 pb-2">
          Shipping Recipient & Address
        </h3>
        <p class="text-xs font-bold uppercase text-scm-black">{{ $order->customer_name }}</p>
        <p class="text-xs text-scm-gray-600 leading-relaxed font-light">{{ $order->shipping_address }}</p>
        <p class="text-xs text-scm-gray-600 font-mono">
          {{ $order->shipping_city }}, {{ $order->shipping_province }} {{ $order->shipping_postal }}
        </p>
        <p class="text-xs text-scm-gray-500 font-mono pt-1">
          Phone: {{ $order->customer_phone }} &bull; Email: {{ $order->customer_email }}
        </p>
      </div>

      <!-- Payment CTA Action Box -->
      <div class="border border-scm-black p-8 bg-white text-center space-y-6">
        <div class="space-y-2">
          <span class="text-[10px] font-mono uppercase tracking-[0.3em] text-scm-gray-500 block">
            Instant Verification Supported
          </span>
          <h3 class="text-lg font-black uppercase tracking-tight text-scm-black">
            Midtrans Payment Gateway
          </h3>
          <p class="text-xs text-scm-gray-500 max-w-md mx-auto">
            Choose from GoPay, QRIS (All e-Wallets), BCA / Mandiri / BNI / BRI Virtual Account, or Credit Card.
          </p>
        </div>

        <!-- Pay Button -->
        <div class="max-w-md mx-auto space-y-3">
          <button id="pay-button" 
                  type="button" 
                  class="w-full bg-scm-black text-white py-4 px-8 text-xs uppercase tracking-[0.25em] font-bold hover:bg-neutral-800 transition duration-200 flex items-center justify-center gap-3 shadow-lg hover:shadow-xl">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Pay Now (Rp {{ number_format($order->total, 0, ',', '.') }})
          </button>

          <!-- Security note -->
          <div class="flex items-center justify-center gap-2 text-[10px] font-mono text-scm-gray-400 uppercase tracking-wider">
            <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>256-Bit SSL Encrypted &bull; Official Midtrans Partner</span>
          </div>
        </div>

        @if(app()->environment('local', 'testing'))
          <div class="pt-4 border-t border-dashed border-scm-gray-200">
            <span class="text-[10px] font-mono uppercase text-scm-gray-400 block mb-2">Development Sandbox Options:</span>
            <a href="{{ route('payment.simulate', $order) }}" 
               class="inline-block text-[11px] font-mono uppercase tracking-widest text-scm-black bg-scm-gray-100 hover:bg-scm-black hover:text-white px-3 py-1.5 border border-scm-gray-300 transition">
              ⚡ Simulate Payment Success (Dev Test)
            </a>
          </div>
        @endif
      </div>

    </div>
  </div>

  <!-- Midtrans Snap SDK Script -->
  <script src="{{ config('services.midtrans.snap_url') }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
  
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const payButton = document.getElementById('pay-button');
      const alertBox = document.getElementById('payment-alert');
      const snapToken = @json($order->midtrans_snap_token);

      function showAlert(type, message) {
        alertBox.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-800', 'border-emerald-300', 'bg-amber-50', 'text-amber-800', 'border-amber-300', 'bg-rose-50', 'text-rose-800', 'border-rose-300');
        
        if (type === 'success') {
          alertBox.classList.add('bg-emerald-50', 'text-emerald-800', 'border-emerald-300');
        } else if (type === 'pending') {
          alertBox.classList.add('bg-amber-50', 'text-amber-800', 'border-amber-300');
        } else {
          alertBox.classList.add('bg-rose-50', 'text-rose-800', 'border-rose-300');
        }

        alertBox.textContent = message;
        alertBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }

      if (payButton) {
        payButton.addEventListener('click', function(e) {
          e.preventDefault();

          if (!snapToken || snapToken.startsWith('dummy') || snapToken.startsWith('snap-fallback') || snapToken.startsWith('snap-sandbox')) {
            // In local/sandbox testing without live Midtrans credentials, prompt simulated checkout
            if (confirm('Sandbox/Testing environment detected. Proceed with simulated checkout success?')) {
              window.location.href = "{{ route('payment.simulate', $order) }}";
              return;
            }
          }

          if (typeof window.snap === 'undefined') {
            showAlert('error', 'Midtrans Snap library failed to load. Please check your internet connection or try again.');
            return;
          }

          window.snap.pay(snapToken, {
            onSuccess: function(result) {
              showAlert('success', 'Payment authorized successfully. Redirecting to invoice confirmation...');
              setTimeout(function() {
                window.location.href = "{{ route('checkout.success', $order) }}";
              }, 1200);
            },
            onPending: function(result) {
              showAlert('pending', 'Payment pending instructions. Redirecting to order details...');
              setTimeout(function() {
                window.location.href = "{{ route('checkout.success', $order) }}";
              }, 1500);
            },
            onError: function(result) {
              showAlert('error', 'Payment transaction failed. Please select another payment method or try again.');
            },
            onClose: function() {
              showAlert('pending', 'Payment popup closed. You can re-attempt payment at any time by clicking Pay Now.');
            }
          });
        });
      }
    });
  </script>
</x-app-layout>
