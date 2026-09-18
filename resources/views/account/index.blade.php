<x-app-layout>
  <!-- Account Header -->
  <div class="bg-scm-white border-b border-scm-gray-200 py-10 md:py-14">
    <div class="container-scm section-padding !py-0">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 text-xs uppercase tracking-[0.25em] text-scm-gray-400 font-semibold mb-2">
            <a href="{{ route('home') }}" class="hover:text-scm-black transition-colors">Home</a>
            <span>/</span>
            <span class="text-scm-black font-bold">My Account</span>
          </div>
          <h1 class="text-3xl md:text-4xl font-black uppercase tracking-tight text-scm-black">
            Member Hub
          </h1>
        </div>
        <span class="text-xs uppercase tracking-widest text-scm-gray-500 font-mono">
          Member ID: SCM-USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}
        </span>
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
            class="flex items-center justify-between px-4 py-3 border transition-colors {{ request()->routeIs('account.index') ? 'bg-scm-black text-white border-scm-black' : 'bg-white text-scm-black border-scm-gray-200 hover:border-scm-black' }}"
          >
            <span>Overview</span>
            <span>&rarr;</span>
          </a>

          <a
            href="{{ route('account.orders') }}"
            class="flex items-center justify-between px-4 py-3 border transition-colors {{ request()->routeIs('account.orders*') ? 'bg-scm-black text-white border-scm-black' : 'bg-white text-scm-black border-scm-gray-200 hover:border-scm-black' }}"
          >
            <span>Order Archive</span>
            <span class="font-mono text-[11px]">{{ $ordersCount }}</span>
          </a>

          <a
            href="{{ route('wishlist.index') }}"
            class="flex items-center justify-between px-4 py-3 border transition-colors bg-white text-scm-black border-scm-gray-200 hover:border-scm-black"
          >
            <span>Saved Wishlist</span>
            <span class="font-mono text-[11px]">{{ $wishlistCount }}</span>
          </a>

          <a
            href="{{ route('profile.edit') }}"
            class="flex items-center justify-between px-4 py-3 border transition-colors {{ request()->routeIs('profile.edit') ? 'bg-scm-black text-white border-scm-black' : 'bg-white text-scm-black border-scm-gray-200 hover:border-scm-black' }}"
          >
            <span>Profile &amp; Security</span>
            <span>&rarr;</span>
          </a>

          @if($user->isAdmin() || $user->isOwner())
            <a
              href="/admin"
              class="flex items-center justify-between px-4 py-3 border border-scm-black bg-scm-gray-100 text-scm-black hover:bg-scm-black hover:text-white transition-colors"
            >
              <span>Management Panel</span>
              <span class="text-[10px] font-mono uppercase bg-scm-black text-white px-1.5 py-0.5">Staff</span>
            </a>
          @endif

          <form method="POST" action="{{ route('logout') }}" class="pt-4">
            @csrf
            <button
              type="submit"
              class="w-full text-left px-4 py-3 text-xs uppercase tracking-widest font-bold text-red-600 border border-transparent hover:border-red-200 hover:bg-red-50 transition-colors"
            >
              Sign Out &rarr;
            </button>
          </form>
        </nav>
      </aside>

      <!-- Main Overview Content -->
      <main class="md:col-span-8 lg:col-span-9 space-y-10">

        <!-- Stat Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="p-6 bg-white border border-scm-gray-200">
            <span class="text-[11px] uppercase tracking-[0.2em] text-scm-gray-400 font-semibold block mb-1">
              Orders History
            </span>
            <p class="text-3xl font-black font-mono text-scm-black">{{ $ordersCount }}</p>
            <a href="{{ route('account.orders') }}" class="text-[11px] uppercase tracking-wider text-scm-gray-500 hover:text-scm-black underline mt-2 inline-block font-mono">
              View All Orders &rarr;
            </a>
          </div>

          <div class="p-6 bg-white border border-scm-gray-200">
            <span class="text-[11px] uppercase tracking-[0.2em] text-scm-gray-400 font-semibold block mb-1">
              Saved Garments
            </span>
            <p class="text-3xl font-black font-mono text-scm-black">{{ $wishlistCount }}</p>
            <a href="{{ route('wishlist.index') }}" class="text-[11px] uppercase tracking-wider text-scm-gray-500 hover:text-scm-black underline mt-2 inline-block font-mono">
              Open Wishlist &rarr;
            </a>
          </div>

          <div class="p-6 bg-white border border-scm-gray-200">
            <span class="text-[11px] uppercase tracking-[0.2em] text-scm-gray-400 font-semibold block mb-1">
              Customer Status
            </span>
            <p class="text-xl font-black uppercase tracking-wider text-scm-black mt-2">
              Verified
            </p>
            <span class="text-[11px] uppercase tracking-widest text-emerald-700 font-semibold block mt-1 font-mono">
              &bull; Active Member
            </span>
          </div>
        </div>

        <!-- Recent Orders Section -->
        <div class="space-y-4">
          <div class="flex items-center justify-between border-b border-scm-gray-200 pb-3">
            <h2 class="text-xs uppercase tracking-[0.25em] font-bold text-scm-black">
              Recent Dispatched Orders
            </h2>
            <a href="{{ route('account.orders') }}" class="text-[11px] uppercase tracking-wider text-scm-gray-500 hover:text-scm-black underline font-mono">
              All Orders ({{ $ordersCount }})
            </a>
          </div>

          @if($recentOrders->count() > 0)
            <div class="divide-y divide-scm-gray-200 border border-scm-gray-200 bg-white">
              @foreach($recentOrders as $order)
                <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-scm-gray-50 transition-colors">
                  <div class="space-y-1">
                    <div class="flex items-center gap-3">
                      <span class="text-xs font-mono font-bold text-scm-black">
                        {{ $order->order_number }}
                      </span>
                      <span class="text-[10px] uppercase tracking-widest font-bold px-2 py-0.5 border {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-scm-gray-100 text-scm-black border-scm-gray-300' }}">
                        {{ $order->status }}
                      </span>
                    </div>
                    <p class="text-[11px] text-scm-gray-500 font-mono">
                      {{ $order->created_at->format('d M Y, H:i') }} WIB &bull; {{ $order->items->count() }} Garments
                    </p>
                  </div>

                  <div class="flex items-center justify-between sm:justify-end gap-6">
                    <span class="text-sm font-black font-mono text-scm-black">
                      Rp {{ number_format($order->total, 0, ',', '.') }}
                    </span>
                    <a
                      href="{{ route('account.orders.show', $order) }}"
                      class="text-xs uppercase tracking-widest font-semibold px-4 py-2 border border-scm-gray-300 hover:border-scm-black text-scm-black transition-colors"
                    >
                      Details &rarr;
                    </a>
                  </div>
                </div>
              @endforeach
            </div>
          @else
            <div class="p-12 text-center border border-dashed border-scm-gray-300 bg-white space-y-3">
              <p class="text-xs uppercase tracking-widest text-scm-gray-500">You haven't placed any orders yet.</p>
              <a href="{{ route('products.index') }}" class="btn-primary text-xs inline-block">
                Browse New Releases &rarr;
              </a>
            </div>
          @endif
        </div>

        <!-- Shipping Destination Snapshot -->
        <div class="space-y-4">
          <div class="flex items-center justify-between border-b border-scm-gray-200 pb-3">
            <h2 class="text-xs uppercase tracking-[0.25em] font-bold text-scm-black">
              Default Delivery Address
            </h2>
            <a href="{{ route('profile.edit') }}" class="text-[11px] uppercase tracking-wider text-scm-gray-500 hover:text-scm-black underline font-mono">
              Update Address
            </a>
          </div>

          <div class="p-6 bg-white border border-scm-gray-200 text-xs space-y-1">
            <p class="font-bold uppercase text-scm-black">{{ $user->name }}</p>
            <p class="text-scm-gray-600 leading-relaxed font-light">
              {{ $user->address ?? 'No default street address registered.' }}
            </p>
            @if($user->city || $user->province || $user->postal_code)
              <p class="text-scm-gray-600 font-mono">
                {{ $user->city }}, {{ $user->province }} {{ $user->postal_code }}
              </p>
            @endif
            <p class="text-scm-gray-500 font-mono pt-1">
              Phone: {{ $user->phone ?? 'Not specified' }}
            </p>
          </div>
        </div>

      </main>

    </div>
  </div>
</x-app-layout>
